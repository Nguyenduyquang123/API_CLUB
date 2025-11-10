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

    $router->get('clubs', 'ClubController@index');
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/clubs/{id}', 'ClubController@show');
    });
    $router->post('clubs', 'ClubController@store');
    $router->put('clubs/{id}', 'ClubController@update');
    $router->delete('clubs/{id}', 'ClubController@destroy');
    $router->get('/my-clubs', ['middleware' => 'auth', 'uses' => 'ClubController@myClubs']);
    $router->post('/clubs/join', 'ClubController@joinByCode');
    $router->post('/club/{club}/invite', 'ClubController@sendInvite');
    $router->post('/club/accept-invite', 'ClubController@acceptInvite');


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
         
    
        // tham gia / rời sự kiện
        $router->post('/{id}/join', 'ClubEventController@join');
        $router->post('/{id}/leave', 'ClubEventController@leave');
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
     $router->get('/clubs/{clubId}/members', 'ClubMemberController@index');
    // Thêm thành viên mới
    $router->post('/clubs/{clubId}/members', 'ClubMemberController@store');
    // Xóa thành viên
    $router->delete('/members/{id}', 'ClubMemberController@destroy');
    // ✏️ Sửa vai trò thành viên (member -> admin, v.v)
    $router->put('/members/{id}/role', 'ClubMemberController@updateRole');
    $router->get('/members/{id}','ClubMemberController@show');
    // $router->get('/test-mail', function () {
    //     Mail::raw('Đây là email test', function ($message) {
    //         $message->to('example@gmail.com')->subject('Test mail');
    //     });
    
    //     return '<h2>✅ Email đã được gửi thành công!</h2>';
    // });



    $router->group(['prefix' => '/clubs', 'middleware' => 'auth'], function () use ($router) {
        $router->post('{clubId}/invite', 'ClubInviteController@sendInvite');
        $router->get('invites/pending', 'ClubInviteController@getPendingInvites');
        $router->post('invites/{inviteId}/accept', 'ClubInviteController@acceptInvite');
        $router->post('invites/{inviteId}/reject', 'ClubInviteController@rejectInvite');
    });

    
});




