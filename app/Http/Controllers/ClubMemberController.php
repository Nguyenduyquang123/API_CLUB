<?php

use App\Http\Controllers\Controller;
use App\Models\ClubMember;
use Illuminate\Http\Client\Request;

class ClubMemberController extends Controller
{
    public function index($clubId)
    {
        $members = ClubMember::where('club_id', $clubId)
            ->with('user')
            ->get();

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $member = ClubMember::create([
            'club_id' => $request->club_id,
            'user_id' => $request->user_id,
            'role' => $request->role ?? 'member',
            'joined_at' => now(),
        ]);

        return response()->json($member, 201);
    }

    public function destroy($id)
    {
        $member = ClubMember::findOrFail($id);
        $member->delete();
        return response()->json(['message' => 'Member deleted']);
    }
}
