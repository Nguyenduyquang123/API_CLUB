<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
$router->get('/', function () {
    return 'Hello from Lumen!';
});

$router->get('/generate-token/{id}', function ($id) {
    $user = App\Models\User::find($id);
    if (!$user) return response()->json(['message' => 'User không tồn tại'], 404);

    $user->api_token = Illuminate\Support\Str::random(60);
    $user->save();

    return response()->json([
        'message' => 'Token tạo thành công',
        'api_token' => $user->api_token
    ]);
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('login', 'AuthController@login');
        $router->get('profile', ['middleware' => 'auth', 'uses' => 'AuthController@profile']);
        $router->post('logout', ['middleware' => 'auth', 'uses' => 'AuthController@logout']);
    });

    $router->get('users', 'UserController@index');
    $router->get('/users/find', 'UserController@find');
    $router->get('users/{id}', 'UserController@show');
    $router->post('users', 'UserController@store');
    $router->put('users/{id}', 'UserController@update');
    $router->delete('users/{id}', 'UserController@destroy');
    $router->get('/myprofile', 'UserController@myProfile');
    $router->post('/user/avatar', [
        'middleware' => 'auth:api',
        'uses' => 'UserController@uploadAvatar'
    ]);
    $router->get('/users/{userId}/stats', 'UserController@getUserStats');
    $router->get('/users/{userId}/posts', 'UserController@getUserPosts');
    $router->get('/users/{userId}/notifications', 'UserController@getUserNotifications');
    $router->get('/users/{userId}/joined-events', 'UserController@getJoinedEvents');
    
    
    $router->post('/notifications/read/{id}', ['uses' => 'UserController@markAsRead']);
    $router->delete('/notifications/{id}', ['uses' => 'UserController@destroyNotification']);

    $router->get('/notifications/read-count/{userId}', 'UserController@readCount');

    $router->get('clubs', 'ClubController@index');
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/clubs/{id}', 'ClubController@show');
        $router->get('/clubs/{id}/settings', 'ClubController@showSettings');
    });
    $router->post('clubs', 'ClubController@store');
    $router->put('clubs/{id}', 'ClubController@update');
    $router->post('/clubs/{clubId}/update', 'ClubController@update');
   // $router->delete('clubs/{id}', 'ClubController@destroy');
    $router->get('/my-clubs', ['middleware' => 'auth', 'uses' => 'ClubController@myClubs']);
    $router->post('/clubs/join', 'ClubController@joinByCode');
    $router->post('/club/{club}/invite', 'ClubController@sendInvite');
    $router->post('/club/accept-invite', 'ClubController@acceptInvite');
    $router->delete('/clubs/{clubId}', 'ClubController@deleteClub');
 
    

    // Category routes
    $router->get('categories', 'CategoryController@index');
    $router->get('categories/{id}', 'CategoryController@show');
    $router->post('categories', 'CategoryController@store');
    $router->put('categories/{id}', 'CategoryController@update');
    $router->delete('categories/{id}', 'CategoryController@destroy');

    $router->group(['prefix' => 'events'], function () use ($router) {
        $router->get('/', 'ClubEventController@index');
        $router->get('/{id}', 'ClubEventController@show');
        $router->post('/', 'ClubEventController@store');
        $router->put('/{id}', 'ClubEventController@update');
        $router->delete('/{id}', 'ClubEventController@destroy');
        $router->get('/{eventId}/participants', 'ClubEventController@getParticipants');
        $router->post('/{eventId}/toggle-join', 'ClubEventController@toggleJoin');
    });
    $router->get('/clubs/{clubId}/events', 'ClubEventController@getByClub');

    $router->group(['prefix' => 'posts'], function () use ($router) {
        $router->get('/', 'PostController@index');
        $router->get('/{id}', 'PostController@show');
        $router->post('/', 'PostController@store');
        $router->put('/{id}', 'PostController@update');
        $router->delete('/{id}', 'PostController@destroy');
         // Lấy bài đăng theo CLB
    });
    $router->get('clubs/{club_id}/posts', 'PostController@getByClub');

    $router->get('posts/{postId}/comments', 'CommentController@index');
    $router->post('posts/{postId}/comments', 'CommentController@store');
   
    // member club
    $router->get('/clubs/{clubId}/member', 'ClubMemberController@index');
    // Thêm thành viên mới
    $router->post('/clubs/{clubId}/members', 'ClubMemberController@store');
    
    // Xóa thành viên
    $router->delete('/members/{id}', 'ClubMemberController@destroy');
    // ✏️ Sửa vai trò thành viên (member -> admin, v.v)
    $router->put('/members/{id}/role', 'ClubMemberController@updateRole');
    $router->get('/members/{id}','ClubMemberController@show');
   
   $router->get('/clubs/{clubId}/my-role', 'ClubMemberController@getMyRole'); // $router->get('/test-mail', function () {
   $router->get('/clubs/{clubId}/members/count','ClubMemberController@countMembers') ;
   
    $router->post('/clubs/{clubId}/toggle-join', 'ClubMemberController@toggleJoin');
    $router->get('/clubs/{clubId}/members', 'ClubMemberController@listMembers');
    $router->delete('/clubs/{clubId}/members/{memberId}', 'ClubMemberController@removeMember');
    $router->post('/clubs/{clubId}/leave', 'ClubMemberController@leaveClub');
   


    $router->group(['prefix' => '/clubs', 'middleware' => 'auth'], function () use ($router) {
        $router->post('{clubId}/invite', 'ClubInviteController@sendInvite');
       
        $router->get('invites/pending', 'ClubInviteController@getPendingInvites');
        $router->post('invites/{inviteId}/accept', 'ClubInviteController@acceptInvite');
        $router->post('invites/{inviteId}/reject', 'ClubInviteController@rejectInvite');
          // Lấy danh sách lời mời của CLB
        $router->get('/{clubId}/invites', 'ClubInviteController@getInvitesByClub');

        // Hủy lời mời
        $router->delete('/invites/{inviteId}/cancel', 'ClubInviteController@cancelInvite');
        
    });
    $router->get('/clubs/invites/{inviteId}', 'ClubInviteController@show');
    
    $router->get('/posts/{postId}/likes', 'PostLikeController@index');
    $router->post('/posts/toggle-like', 'PostLikeController@toggleLike');
    $router->post('/posts/check-like', 'PostLikeController@checkLike');
    $router->patch('/posts/{postId}/pin', 'PostController@pin');
    $router->patch('/posts/{id}/unpin', 'PostController@unpin');
 
    $router->get('/comments/{commentId}/likes', 'CommentLikeController@index');
    $router->post('/comments/toggle-like', 'CommentLikeController@toggleLike');
    $router->post('/comments/check-like', 'CommentLikeController@checkLike');
    $router->delete('/comments/{id}', 'CommentController@delete');


    $router->get('/events/{eventId}/export-participants', 'ClubEventController@exportParticipants');


    // User Calendar Event routes
    $router->post('/calendar/add', 'UserCalendarEventController@store');
    $router->get('/calendar/{userId}', 'UserCalendarEventController@index');
    $router->get('/notifications/event-reminder', 'NotificationController@eventReminder');
    $router->get('/calendar/today/{userId}', 'UserCalendarEventController@getTodayEvents');
    $router->delete('/calendar/{userId}/{eventId}', 'UserCalendarEventController@remove');


    
$router->group(['prefix' => 'clubs'], function () use ($router) {

    // 1. User gửi đơn xin gia nhập
    $router->post('{clubId}/join-request', 'ClubJoinRequestController@requestJoin');

    // 2. Admin xem danh sách đơn của 1 CLB
    $router->get('{clubId}/join-requests', 'ClubJoinRequestController@listRequests');
    $router->post('/{clubId}/join-requests/{id}/approve', 'ClubJoinRequestController@approve');
    $router->post('/{clubId}/join-requests/{id}/reject', 'ClubJoinRequestController@reject');

});   

$router->group(['prefix' => 'events/{eventId}'], function () use ($router) {

    // Lấy danh sách người tham gia
    $router->get('/participantss', 'EventParticipantController@index');

    // Xác nhận
    $router->post('/participants/{userId}/confirm', 'EventParticipantController@confirm');

    // Hủy / từ chối
    $router->post('/participants/{userId}/cancel', 'EventParticipantController@cancel');
});

$router->get('/clubs/{clubId}/statistics', 'StatisticController@getStats');
$router->post('/clubs/{clubId}/statistics/generate', 'StatisticController@generateStats');




$router->post('clubs/{id}/update-privacy', 'ClubController@updatePrivacy');



});

 $router->get('/clubs/public','ClubController@publicClubs');
    


