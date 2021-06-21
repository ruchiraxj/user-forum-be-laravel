<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
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

    //view all approved posts
    
    Route::get('/posts/search', [PostController::class, 'search']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/created', [PostController::class, 'byUser']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts/store', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);


    Route::get('/admin/posts/pending', [AdminController::class, 'viewUnapprovedPosts']);
    Route::put('/admin/posts/approve/{id}', [AdminController::class, 'updatePostStatus']);
});

//Route::resource('products', ProductController::class);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
