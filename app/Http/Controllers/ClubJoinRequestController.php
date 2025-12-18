<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMember;
use App\Models\Notification;
use Illuminate\Http\Request;

class ClubJoinRequestController extends Controller
{
    // User gửi đơn xin gia nhập
    public function requestJoin(Request $request, $clubId)
    {
        $userId = $request->user_id;

        // Kiểm tra đã là member chưa
        if (ClubMember::where('club_id', $clubId)->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Bạn đã là thành viên câu lạc bộ'], 400);
        }

        // Kiểm tra đã gửi đơn chưa
        if (ClubJoinRequest::where('club_id', $clubId)->where('user_id', $userId)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Bạn đã gửi đơn trước đó, vui lòng chờ duyệt'], 400);
        }

        // Tạo request
        $req = ClubJoinRequest::create([
            'club_id' => $clubId,
            'user_id' => $userId
        ]);

        return response()->json([
            'message' => 'Đã gửi đơn xin gia nhập!',
            'data' => $req
        ], 201);
    }

    // Admin xem danh sách đơn
    public function listRequests($clubId)
    {
        $requests = ClubJoinRequest::where('club_id', $clubId)
            ->where('status', 'pending')
            ->with('user')
            ->get();

        return response()->json($requests);
    }

    // Admin duyệt đơn
public function approve($clubId, $id)
{
    $req = ClubJoinRequest::findOrFail($id);

    $req->status = 'approved';
    $req->save();

    // thêm vào bảng member
    ClubMember::create([
        'club_id' => $req->club_id,
        'user_id' => $req->user_id,
        'role' => 'member'
    ]);
    $club = Club::find($clubId);
    $adminId = auth()->id();

  
    $noti = Notification::create([
        'user_id'          => $req->user_id,      // người nhận thông báo
        'from_user_id'     => $adminId,           // người tạo thông báo
        'type'             => 'join_approved',    // loại thông báo
        'club_id'          => $clubId,
          'title'          => "Yêu cầu tham gia CLB đã được chấp nhận",
    'message'        => "Bạn đã được duyệt vào câu lạc bộ ". "$club->name",
        'related_post_id'  => null,
        'related_comment_id' => null,
        'is_read'          => 0,
    ]);


    return response()->json(['message' => 'Duyệt đơn thành công!']);
}

public function reject($clubId, $id)
{
    $req = ClubJoinRequest::findOrFail($id);
    $req->status = 'rejected';
    $req->save();

    $adminId = auth()->id();
    $club = Club::find($clubId);

    $noti = Notification::create([
        'user_id'          => $req->user_id,
        'from_user_id'     => $adminId,
        'type'             => 'join_rejected',
        'club_id'          => $clubId,
          'title'          => "Yêu cầu tham gia CLB bị từ chối",
    'message'        => "Yêu cầu tham gia câu lạc bộ ". "$club->name" . " đã bị từ chối",
        'related_post_id'  => null,
        'related_comment_id' => null,
        'is_read'          => 0,
    ]);

    return response()->json(['message' => 'Đã từ chối đơn']);
}
}
