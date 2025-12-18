<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\MemberHonor;

class HonorController extends BaseController
{
    public function index($clubId)
    {
        return response()->json([
            'honors' => MemberHonor::where('club_id', $clubId)
                ->orderBy('id', 'desc')
                ->get()
        ]);
    }

    public function store($clubId, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'role' => 'required',
            'avatar' => 'required',
        ]);

        $item = MemberHonor::create([
            'club_id' => $clubId,
            'name' => $request->name,
            'role' => $request->role,
            'avatar' => $request->avatar,
            'description' => $request->description,
        ]);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = MemberHonor::find($id);
        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
