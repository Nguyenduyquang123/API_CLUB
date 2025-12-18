<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use App\Models\ClubMember;
use Illuminate\Http\Request;

class ClubMemberController extends Controller
{
    public function index($clubId)
    {
        $members = ClubMember::where('club_id', $clubId)
            ->with('user')
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'member')")
            ->get();

        return response()->json($members);
    }


    public function store(Request $request, $clubId)
    {
        $member = ClubMember::create([
            'club_id' => $clubId,
            'user_id' => $request->user_id,
            'role' => $request->role ?? 'member',
        
        ]);

        return response()->json($member, 201);
    }

    public function destroy($id)
    {
        $member = ClubMember::findOrFail($id);
        $member->delete();
        return response()->json(['message' => 'Member deleted']);
    }
    public function updateRole(Request $request, $id)
    {
        $member = ClubMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Thành viên không tồn tại'
            ], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:owner,admin,member' // thêm các role khác nếu cần
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cập nhật role
        $member->role = $request->input('role');
        $member->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chức vụ thành công',
            'data' => $member
        ]);
    }
        public function show($id)
        {
            $member = ClubMember::with('user')->find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thành viên không tồn tại'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $member
            ]);
        }
    public function getMyRole(Request $request, $clubId)
    {
        $userId = $request->input('user_id');

        $member = ClubMember::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json(['role' => null]);
        }

        return response()->json(
            $member);
    }


    public function removeMember(Request $request, $clubId, $memberId)
    {
        $authUserId = $request->input('auth_user_id'); // FE gửi auth userId vào
        if (!$authUserId) {
            return response()->json(['error' => 'Missing auth_user_id'], 400);
        }

        // Lấy role của user thực hiện hành động
        $myRole = ClubMember::where('club_id', $clubId)
            ->where('user_id', $authUserId)
            ->value('role');

        if (!$myRole) {
            return response()->json(['error' => 'Bạn không thuộc CLB này'], 403);
        }

        if (!in_array($myRole, ['owner', 'admin'])) {
            return response()->json(['error' => 'Bạn không có quyền xóa thành viên'], 403);
        }

        // Không cho admin xóa owner
        $target = ClubMember::where('club_id', $clubId)
            ->where('user_id', $memberId)
            ->first();

        if (!$target) {
            return response()->json(['error' => 'Thành viên không tồn tại'], 404);
        }

        if ($target->role === 'owner') {
            return response()->json(['error' => 'Không thể xóa chủ CLB'], 400);
        }

        // Admin không được xóa chính họ
        if ($authUserId == $memberId && $myRole === 'admin') {
            return response()->json(['error' => 'Admin không thể tự xóa chính mình'], 400);
        }

        // Xóa member
        $target->delete();

        return response()->json(['message' => 'Xóa thành viên thành công']);
    }
    public function leaveClub(Request $request, $clubId)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['error' => 'Missing user_id'], 400);
        }

        $member = ClubMember::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'Bạn không thuộc CLB này'], 404);
        }

        // Không cho owner tự rời CLB
        if ($member->role === 'owner') {
            return response()->json(['error' => 'Owner không thể rời CLB, hãy chuyển quyền trước'], 400);
        }

        $member->delete();

        return response()->json(['message' => 'Bạn đã rời CLB thành công']);
    }
    public function countMembers($clubId)
{
    $count = ClubMember::where('club_id', $clubId)->count();

    return response()->json([
        "club_id" => $clubId,
        "members_count" => $count
    ]);
}



}
