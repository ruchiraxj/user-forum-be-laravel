<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/role', [AuthController::class, 'role']);

    //Posts
    Route::get('/posts/search', [PostController::class, 'search']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/created', [PostController::class, 'byUser']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts/store', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    //Comments
    Route::get('/comments/by-post/{id}', [CommentController::class, 'show']);
    Route::post('/comments/store', [CommentController::class, 'store']);
  
});


Route::group(['middleware' => ['auth:sanctum', 'can:accessAdmin,App\Policy\Admin']], function () {
    //Admin
    Route::get('/admin/posts/pending', [AdminController::class, 'viewUnapprovedPosts']);
    Route::put('/admin/posts/approve/{id}', [AdminController::class, 'updatePostStatus']);
});
