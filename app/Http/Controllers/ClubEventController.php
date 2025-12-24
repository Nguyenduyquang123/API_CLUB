<?php

namespace App\Http\Controllers;

use App\Models\ClubEvent;
use App\Models\ClubEventMember;
use App\Models\ClubMember;
use App\Models\EventParticipant;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClubEventController extends Controller
{
    /**
     * Lấy danh sách sự kiện (có thể lọc theo club_id)
     */
    public function index(Request $request)
    {
        $query = ClubEvent::with('club', 'creator');

        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $events = $query->withCount('members')->orderBy('start_time', 'asc')->get();

        return response()->json($events);
    }

    /**
     * Lấy chi tiết 1 sự kiện
     */
    public function show($id)
    {
        $event = ClubEvent::with(['club', 'creator', 'members'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->member_count = $event->members->count();

        
        return response()->json($event);
    }

    /**
     * Tạo mới sự kiện
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'club_id' => 'required|exists:clubs,id',
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'banner' => 'nullable|file|image',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'notify' => 'boolean',
            'require_registration' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bannerPath = null;
      // ✅ Xử lý upload ảnh banner
    if ($request->hasFile('banner')) {
        $file = $request->file('banner');

        // tạo thư mục nếu chưa có
        $destination = base_path('public/banner');
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        // tạo tên file duy nhất
        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

        // di chuyển file vào public/banner
        $file->move($destination, $filename);

        // tạo URL truy cập từ frontend
        $bannerPath = $request->getSchemeAndHttpHost() . '/banner/' . $filename;
    }

        $event = ClubEvent::create([
            'club_id' => $request->club_id,
            'created_by' => $request->created_by,
            'title' => $request->title,
            'banner' => $bannerPath,
            'description' => $request->description,
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
            'location' => $request->location,
            'max_participants' => $request->max_participants,
            'notify' => $request->notify ?? false,
            'require_registration' => $request->require_registration ?? false,
            'status' => 'upcoming',
        ]);
        if ($event->notify) {
        // Lấy tất cả thành viên CLB trừ người tạo event
        $members = ClubMember::where('club_id', $event->club_id)
                            ->where('user_id', '!=', $event->created_by)
                            ->get();

        foreach ($members as $member) {
            $noti = Notification::create([
                'user_id' => $member->user_id,
                'from_user_id' => $event->id,
                'type' => 'club_event',
                'club_id' => $event->club_id,       // thêm dòng này
                'title' => $event->title,
                'message' => $event->description,
                'related_post_id' => null,
                'related_comment_id' => null,
                'is_read' => false,
            ]);

            // 🔥 Gửi realtime
            (new \App\Events\NewNotification($noti))->broadcast();
    }
}


        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }


    /**
     * Cập nhật thông tin sự kiện
     */
    public function update(Request $request, $id)
    {
        $event = ClubEvent::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'in:public,private',
            'is_paid' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'in:upcoming,ongoing,ended,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    /**
     * Xoá sự kiện
     */
    public function destroy(Request $request, $id)
    {
        $authUserId = $request->input('auth_user_id');

        if (!$authUserId) {
            return response()->json(['error' => 'Missing auth_user_id'], 400);
        }

        // Lấy event
        $event = ClubEvent::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Lấy role của user trong CLB
        $role = ClubMember::where('club_id', $event->club_id)
            ->where('user_id', $authUserId)
            ->value('role');

        if (!$role) {
            return response()->json(['error' => 'Bạn không thuộc CLB này'], 403);
        }

        // Chỉ owner + admin mới được xóa
        if (!in_array($role, ['owner', 'admin'])) {
            return response()->json(['error' => 'Bạn không có quyền xóa sự kiện'], 403);
        }

        // Xóa sự kiện
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * Tham gia sự kiện
     */
    public function join(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = ClubEvent::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Kiểm tra giới hạn người tham gia
        if ($event->max_participants && $event->members()->count() >= $event->max_participants) {
            return response()->json(['message' => 'Event is full'], 400);
        }

        // Kiểm tra đã tham gia chưa
        if ($event->members()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'User already joined'], 400);
        }

        $event->members()->attach($request->user_id, [
            'role' => 'participant',
            'status' => 'joined',
            'joined_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Joined event successfully']);
    }

    /**
     * Rời khỏi sự kiện
     */
    public function leave(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = ClubEvent::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->members()->detach($request->user_id);

        return response()->json(['message' => 'Left event successfully']);
    }
    public function getByClub(Request $request, $clubId)
    {
        // Lấy danh sách sự kiện
        $events = ClubEvent::with(['creator', 'club'])
            ->where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy user_id FE gửi lên
        $userId = $request->query('user_id');

        // Lấy role trong CLB
        $userRoleInClub = null;

        if ($userId) {
            $member = ClubMember::where('club_id', $clubId)
                ->where('user_id', $userId)
                ->first();

            $userRoleInClub = $member ? $member->role : null;
        }

        return response()->json([
            'events' => $events,
            'user_role_in_club' => $userRoleInClub
        ]);
    }
    public function addParticipant(Request $request, $eventId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
        ]);

        $event = ClubEvent::findOrFail($eventId);

        // Thêm người tham gia (sẽ không thêm trùng do có unique key)
        $event->participants()->syncWithoutDetaching([
            $request->user_id => ['role' => $request->role],
        ]);

        return response()->json(['message' => 'Thêm người tham gia thành công']);
    }
   // Lấy danh sách người tham gia

    public function getParticipants($eventId)
    {
        $participants = EventParticipant::with('user')
            ->where('event_id', $eventId)
            ->get();

        return response()->json($participants);
    }

    // Toggle tham gia/hủy
      // Toggle tham gia/hủy sự kiện
    public function toggleJoin($eventId, Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['error' => 'user_id is required'], 400);
        }

        $participant = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($participant) {
            // Hủy tham gia
            $participant->delete();
            return response()->json(['joined' => false]);
        } else {
            // Lấy event -> club_id trước
            $event = ClubEvent::find($eventId);
            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            // Lấy role từ club_members
            $clubMember = ClubMember::where('club_id', $event->club_id)
                ->where('user_id', $userId)
                ->first();

            $role = $clubMember ? $clubMember->role : 'Thành viên';

            // Tham gia
            EventParticipant::create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'role' => $role,
            ]);

            return response()->json(['joined' => true]);
        }
    }


public function exportParticipants($eventId)
{
    $participants = DB::table('event_participants')
        ->join('users', 'event_participants.user_id', '=', 'users.id')
        ->where('event_participants.event_id', $eventId)
        ->select(
            'users.displayName',
            'users.email',
            'event_participants.role'
        )
        ->get();

    $filename = "participants_event_{$eventId}.csv";

    $response = new StreamedResponse(function () use ($participants) {
        $file = fopen('php://output', 'w');

        // Giúp Excel đọc UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header cột
        fputcsv($file, ["Họ Tên", "Email", "Vai Trò"]);

        // Ghi từng dòng
        foreach ($participants as $p) {
            fputcsv($file, [
                $p->displayName,
                $p->email,
                $p->role
            ]);
        }

        fclose($file);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set(
        'Content-Disposition',
        'attachment; filename="'.$filename.'"'
    );

    return $response;
}

}
