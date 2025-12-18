<?php

namespace App\Http\Controllers;

use App\Models\ClubEvent;
use App\Models\ClubMember;
use App\Models\ClubStatistic;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Models\EventParticipant; // ⚠ THÊM VÀO
use Illuminate\Support\Carbon;

class StatisticController extends Controller
{
    public function getStats($clubId)
    {
        $stats = ClubStatistic::where('club_id', $clubId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($stats);
    }
public function generateStats($clubId)
{
    $now = Carbon::now();
    $year = $now->year;
    $month = $now->month;

    // 1. Thành viên mới trong tháng hiện tại
    $newMembers = ClubMember::where('club_id', $clubId)
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->count();

    // 2. Tính điểm tương tác từng thành viên
    $members = User::whereHas('clubMembers', fn($q) => $q->where('club_id', $clubId))->get();
    $memberScores = [];

    foreach ($members as $member) {
        $userId = $member->id;

        $eventPoints = EventParticipant::where('user_id', $userId)
            ->whereHas('event', fn($q) => $q->where('club_id', $clubId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', 'confirmed')
            ->count() * 50;

        $commentPoints = PostComment::where('user_id', $userId)
            ->whereHas('post', fn($q) => $q->where('club_id', $clubId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() * 0.25;

        $commentlikePoints = CommentLike::where('user_id', $userId)
            ->whereHas('comment.post', fn($q) => $q->where('club_id', $clubId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() * 0.25;

        $likePoints = PostLike::where('user_id', $userId)
            ->whereHas('post', fn($q) => $q->where('club_id', $clubId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() * 1;

        $postPoints = Post::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() * 2;

        $totalScore = $eventPoints + $commentPoints + $commentlikePoints + $likePoints + $postPoints;

        $memberScores[] = [
            'user_id' => $userId,
            'avatar' => $member->avatarUrl,
            'name' => $member->displayName,
            'score' => $totalScore
        ];
    }

    // Top 5 thành viên tương tác nhiều nhất
    $topMembers = collect($memberScores)
        ->sortByDesc('score')
        ->take(5)
        ->values();

    // Top 5 sự kiện có người tham gia cao nhất
    $topEvents = EventParticipant::select('event_id', \DB::raw('COUNT(*) as participants'))
        ->whereHas('event', fn($q) => $q->where('club_id', $clubId))
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->groupBy('event_id')
        ->orderByDesc('participants')
        ->limit(5)
        ->get()
        ->map(fn($item) => [
            'event_id' => $item->event_id,
            'name' => $item->event->title ?? $item->event->event_name ?? 'N/A',
            'participants' => $item->participants
        ]);

    // 3. Dữ liệu biểu đồ cột: số thành viên mới theo tháng
    $monthlyMembers = ClubMember::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->where('club_id', $clubId)
        ->whereYear('created_at', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    // 4. Dữ liệu biểu đồ đường: tổng điểm tương tác theo tháng
    $monthlyScores = [];
    for ($m = 1; $m <= 12; $m++) {
        $score = 0;
        foreach ($members as $member) {
            $userId = $member->id;

            $eventPoints = EventParticipant::where('user_id', $userId)
                ->whereHas('event', fn($q) => $q->where('club_id', $clubId))
                ->where('status', 'confirmed')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $year)
                ->count() * 50;

            $commentPoints = PostComment::where('user_id', $userId)
                ->whereHas('post', fn($q) => $q->where('club_id', $clubId))
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $year)
                ->count() * 0.25;

            $commentlikePoints = CommentLike::where('user_id', $userId)
            ->whereHas('comment.post', fn($q) => $q->where('club_id', $clubId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() * 0.25;


            $likePoints = PostLike::where('user_id', $userId)
                ->whereHas('post', fn($q) => $q->where('club_id', $clubId))
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $year)
                ->count() * 1;

            $postPoints = Post::where('user_id', $userId)
                ->where('club_id', $clubId)
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $year)
                ->count() * 2;

            $score += $eventPoints + $commentPoints + $commentlikePoints + $likePoints + $postPoints;
        }

        $monthlyScores[] = [
            'month' => $m,
            'score' => $score
        ];
    }

    // 5. Lưu vào bảng thống kê
    $stat = ClubStatistic::updateOrCreate(
        ['club_id' => $clubId, 'year' => $year, 'month' => $month],
        [
            'new_members' => $newMembers,
            'total_posts' => Post::where('club_id', $clubId)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count(),
            'total_comments' => PostComment::whereHas('post', fn($q) => $q->where('club_id', $clubId))
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count(),
            'total_likes' => PostLike::whereHas('post', fn($q) => $q->where('club_id', $clubId))
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count(),
            'total_commentlikes' => CommentLike::whereHas('comment.post', fn($q) => $q->where('club_id', $clubId))
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count(),
            'top_members' => $topMembers,
            'top_events' => $topEvents
        ]
    );

    return response()->json([
        'message' => 'Thống kê tháng đã được cập nhật!',
        'data' => $stat
    ]);
}


    
}
