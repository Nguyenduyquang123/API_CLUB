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
}
