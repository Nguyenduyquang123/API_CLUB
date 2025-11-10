<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClubInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ClubInviteController extends Controller
{
    // 1. Gửi lời mời
    public function sendInvite(Request $request, $clubId)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'invitee_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra lời mời đã tồn tại chưa
        $existing = ClubInvite::where('club_id', $clubId)
            ->where('invitee_id', $request->invitee_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Người này đã được mời rồi!'], 400);
        }

        // Tạo lời mời mới
        ClubInvite::create([
            'club_id' => $clubId,
            'inviter_id' => auth()->id(),
            'invitee_id' => $request->invitee_id,
        ]);

        return response()->json(['message' => 'Đã gửi lời mời!']);
    }

    // 2. Lấy danh sách lời mời đang chờ của người dùng
    // Lấy lời mời pending
    public function getPendingInvites()
    {
        $invites = ClubInvite::where('invitee_id', auth()->id())
            ->where('status', 'pending')
            ->with('club')
            ->get();

        return response()->json($invites);
    }

    // 3. Xác nhận tham gia
  // Chấp nhận lời mời
  public function acceptInvite($inviteId)
{
    $invite = ClubInvite::findOrFail($inviteId);

    if ($invite->invitee_id !== auth()->id()) {
        return response()->json(['message' => 'Không có quyền xác nhận!'], 403);
    }


    try {
        // Chèn vào bảng club_members đúng các cột
        DB::table('club_members')->insert([
            'club_id' => $invite->club_id,
            'user_id' => auth()->id()
        ]);

        $invite->status = 'accepted';
        $invite->save();

        return response()->json(['message' => 'Bạn đã tham gia câu lạc bộ!']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Lỗi server',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Từ chối lời mời
    public function rejectInvite($inviteId)
    {
        $invite = ClubInvite::findOrFail($inviteId);

        if ($invite->invitee_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền từ chối!'], 403);
        }

        $invite->status = 'rejected';
        $invite->save();

        return response()->json(['message' => 'Đã từ chối lời mời.']);
    }
}
