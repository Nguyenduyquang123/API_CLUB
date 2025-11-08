<?php

namespace App\Http\Controllers;

use App\Models\ClubEvent;
use App\Models\ClubEventMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            'description' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'in:public,private',
            'is_paid' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
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
            'type' => $request->type ?? 'public',
            'is_paid' => $request->is_paid ?? false,
            'max_participants' => $request->max_participants,
            'status' => 'upcoming',
        ]);

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
    public function destroy($id)
    {
        $event = ClubEvent::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

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
    public function getByClub($clubId)
    {
        $events = ClubEvent::with(['creator', 'club'])
            ->where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($events);
    }

}
