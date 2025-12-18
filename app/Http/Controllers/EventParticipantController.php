<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ClubEvent;
use App\Models\EventParticipant;

class EventParticipantController extends Controller
{
    /**
     * Lấy danh sách thành viên tham gia theo sự kiện
     */
public function index(Request $request, $eventId)
{
    $status = $request->query('status');

    $event = ClubEvent::with(['participants' => function ($q) use ($status) {
        if ($status) {
            $q->wherePivot('status', $status);
        }
    }])->find($eventId);

    if (!$event) {
        return response()->json(['message' => 'Event not found'], 404);
    }

    $participants = $event->participants->map(function ($u) {
        return [
            'user_id' => $u->id,
            'name' => $u->displayName,
            'avatar' => $u->avatarUrl,
            'status' => $u->pivot->status,
            'role' => $u->pivot->role,
        ];
    });

    return response()->json([
        'event_id' => $eventId,
        'participants' => $participants
    ]);
}



    /**
     * Xác nhận người tham gia (update status → confirmed)
     */
   public function confirm($eventId, $userId)
{
    $updated = EventParticipant::where('event_id', $eventId)
        ->where('user_id', $userId)
        ->update(['status' => 'confirmed']);

    if (!$updated) {
        return response()->json(['message' => 'Participant not found'], 404);
    }

    return response()->json(['message' => 'Member confirmed']);
}


    /**
     * Hủy / từ chối người tham gia (update status → cancelled)
     */
   public function cancel($eventId, $userId)
{
    $updated = EventParticipant::where('event_id', $eventId)
        ->where('user_id', $userId)
        ->update(['status' => 'pending']);

    if (!$updated) {
        return response()->json(['message' => 'Participant not found'], 404);
    }

    return response()->json(['message' => 'Member cancelled']);
}

}
