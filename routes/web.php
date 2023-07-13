<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/admin', function () {
        return 'admin';
})->middleware('can:visitAdmin');

// User
Route::get('/', [
    UserController::class, 'showCorrectHomePage'
])->name('login');


Route::post('/register', [
    UserController::class,'register'
])->middleware('guest');

Route::post('/login', [
    UserController::class,'login'
])->middleware('guest');

Route::post('/logout', [
    UserController::class,'logout'
])->middleware('mustBeLoggedIn');

Route::get('/manage-avatar', [
    UserController::class, 'viewAvatarForm'
])->name('mustBeLoggedIn');

Route::post('/manage-avatar', [
    UserController::class, 'storeAvatar'
])->name('mustBeLoggedIn');

// Posts

Route::get('/create-post', [
    PostController::class,'showCreateForm'
])->middleware('mustBeLoggedIn');

Route::post('/create-post', [
    PostController::class,'storeNewPost'
])->middleware('mustBeLoggedIn');

Route::get('/post/{post}', [
    PostController::class,'viewSinglePost'
]);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'update'])->middleware('can:update,post');
// Users

Route::get('/profile/{user:username}', [
    UserController::class,
    'profile'
]);

Route::get('/profile/{user:username}/followers', [
    UserController::class,
    'followers'
]);

Route::get('/profile/{user:username}/following', [
    UserController::class,
    'following'
]);


// Follows 

Route::post('/follow/{user:username}', [
    FollowController::class,
    'follow'
])->name('mustBeLoggedIn');

Route::post('/unfollow/{user:username}', [
    FollowController::class,
    'unfollow'
])->name('mustBeLoggedIn');



