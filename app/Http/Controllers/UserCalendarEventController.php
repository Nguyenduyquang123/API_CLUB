<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\UserCalendarEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserCalendarEventController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'event_id' => 'required|integer',
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'description' => 'nullable|string'
        ]);

        $event = UserCalendarEvent::create($request->all());

        return response()->json([
            'message' => 'Event added to calendar',
            'data' => $event
        ], 201);
    }

    public function index($userId)
    {
        $events = UserCalendarEvent::where('user_id', $userId)->get();
        return response()->json($events);
    }
    public function getTodayEvents($userId)
    {
        $today = Carbon::today();

        $events = UserCalendarEvent::where('user_id', $userId)
            ->whereDate('start', $today)
            ->get();

        return response()->json($events);
    }
public function remove($userId, $eventId)
{
    $deleted = UserCalendarEvent::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->delete();

    return response()->json(['status' => 'success', 'deleted' => $deleted]);
}
public function eventReminder()
{
    $now = Carbon::now();
    
    // Lấy các event 30 phút tới
    $upcomingEvents = UserCalendarEvent::where('start', '<=', $now->copy()->addMinutes(30))
                                        ->where('start', '>', $now)
                                        ->get();

    foreach ($upcomingEvents as $event) {
        // Tạo notification cho user
        Notification::create([
            'user_id' => $event->user_id,
            'from_user_id' => null, // hệ thống
            'type' => 'event_reminder',
            'club_id' => $event->club_id,
            'title' => "Sắp diễn ra sự kiện: {$event->title}",
            'message' => "Sự kiện '{$event->title}' sẽ bắt đầu lúc " . $event->start->format('H:i d/m/Y'),
            'related_post_id' => null,
            'related_comment_id' => null,
            'is_read' => 0
        ]);
    }

    return response()->json(['status' => 'success', 'count' => $upcomingEvents->count()]);
}
}
