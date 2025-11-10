<?php

namespace App\Http\Controllers;

use App\Mail\ClubInviteMail;
use App\Models\Club;
use App\Models\ClubMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ClubController extends Controller
{
    // Lấy danh sách tất cả CLB
    public function index()
    {
        return response()->json(Club::all());
    }

  public function show(Request $request, $id)
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        // 🔐 Lấy user từ middleware (hoặc request)
        $user = $request->user;
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 🔒 Kiểm tra thành viên
        $isMember = ClubMember::where('club_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        // Nếu không phải thành viên & CLB không công khai → từ chối
        if (!$isMember && $club->is_public == 0) {
            return response()->json(['message' => 'Bạn không có quyền truy cập CLB này'], 403);
        }

        return response()->json($club);
    }

    // Thêm mới CLB
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'avatar_url' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
           'owner_id' => 'required|integer|exists:users,id',
            'category_id' => 'nullable|integer',
            'is_public' => 'nullable|string'
        ]);

        // ✅ Tạo mã mời ngẫu nhiên
        $inviteCode = strtoupper(substr(md5(uniqid()), 0, 6));

        // ✅ Xử lý upload ảnh
        $avatarPath = null;
        if ($request->hasFile('avatar_url')) {
            $file = $request->file('avatar_url');
            $avatarPath = $file->store('avatars', 'public'); // lưu vào storage/app/public/avatars
        }

        // ✅ Tạo club mới
        $club = Club::create([
            'name' => $request->name,
            'description' => $request->description,
            'avatar_url' => $avatarPath ? ('storage/' . $avatarPath) : 'https://example.com/default-avatar.png',
            'invite_code' => $inviteCode,
            'is_public' => $request->is_public ?? '1', // mặc định là công khai
            'owner_id' => $request->owner_id,
            'category_id' => $request->category_id,
        ]);
        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $request->owner_id,
            'role' => 'owner',
        ]);

        
        return response()->json([
            'message' => '✅ Club created successfully',
            'data' => $club
        ], 201);
    }

    // Cập nhật CLB
    public function update(Request $request, $id)
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        $club->update($request->all());
        return response()->json($club);
    }

    // Xóa CLB
    public function destroy($id)
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        $club->delete();
        return response()->json(['message' => 'Club deleted successfully']);
    }
    public function myClubs(Request $request)
    {
        $userId = $request->user->id;

        $clubs = Club::withCount('members') // 👈 Thêm dòng này để đếm số thành viên
            ->where('owner_id', $userId)
            ->orWhereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->get();

        return response()->json($clubs);
    }
    public function joinByCode(Request $request)
    {
        $this->validate($request, [
            'invite_code' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $club = Club::where('invite_code', $request->invite_code)->first();

        if (!$club) {
            return response()->json(['message' => 'Mã mời không hợp lệ'], 404);
        }

        // Kiểm tra xem đã là thành viên chưa
        $exists = ClubMember::where('club_id', $club->id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Bạn đã là thành viên câu lạc bộ này'], 400);
        }

        // Thêm vào bảng member
        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Tham gia câu lạc bộ thành công!',
            'club' => $club
        ]);
    }
     public function acceptInvite(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $club = Club::where('invite_code', $request->invite_code)->firstOrFail();

        // Kiểm tra user đã là thành viên chưa
        $exists = ClubMember::where('club_id', $club->id)
                            ->where('user_id', $request->user_id)
                            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã là thành viên.'
            ]);
        }

        // Thêm vào bảng club_members
        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $request->user_id,
         
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bạn đã gia nhập câu lạc bộ!'
        ]);
    }
    public function sendInvite(Request $request)
{
    $clubId = $request->route('club'); // Lấy param từ URL

    // Validate thủ công
    $validator = Validator::make($request->all(), [
        'email' => 'required|email'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $club = Club::find($clubId);
    if (!$club) {
        return response()->json([
            'success' => false,
            'message' => 'Club không tồn tại'
        ], 404);
    }

    try {
        Mail::to($request->email)->send(new ClubInviteMail($club));

        return response()->json([
            'success' => true,
            'message' => 'Email mời đã được gửi!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
