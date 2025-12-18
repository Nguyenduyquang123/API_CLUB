<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\ClubAchievement;

class AchievementController extends BaseController
{
    public function index($clubId)
    {
        $items = ClubAchievement::where('club_id', $clubId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['achievements' => $items]);
    }

    public function store($clubId, Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $item = ClubAchievement::create([
            'club_id' => $clubId,
            'title' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = ClubAchievement::find($id);
        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
