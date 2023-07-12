<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;


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

Route::get('/', [
    UserController::class,
    'showCorrectHomePage'
])->name('login');


Route::post('/register', [
    UserController::class,
    'register'
])->middleware('guest');

Route::post('/login', [
    UserController::class,
    'login'
])->middleware('guest');

Route::post('/logout', [
    UserController::class,
    'logout'
])->middleware('mustBeLoggedIn');


// Posts

Route::get('/create-post', [
    PostController::class,
    'showCreateForm'
])->middleware('mustBeLoggedIn');

Route::post('/create-post', [
    PostController::class,
    'storeNewPost'
])->middleware('mustBeLoggedIn');

Route::get('/post/{post}', [
    PostController::class,
    'viewSinglePost'
]);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'update'])->middleware('can:update,post');
// Users

Route::get('/profile/{user:username}', [
    UserController::class,
    'profile'
]);


