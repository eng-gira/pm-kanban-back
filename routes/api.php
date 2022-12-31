<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
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
$prefix = env('APP_ENV', 'production') == 'production' ? $prefix . '/project-manager/api' : '/api';
// Auth
Route::post($prefix . '/register', [AuthController::class, 'register']);
Route::post($prefix . '/login', [AuthController::class, 'login']);
Route::post($prefix . '/refresh', [AuthController::class, 'refresh']);
Route::post($prefix . '/logout', [AuthController::class, 'logout']);

// Project
Route::get($prefix . '/projects', [ProjectController::class, 'index']);
Route::get($prefix . '/projects/{id}', [ProjectController::class, 'show']);
Route::post($prefix . '/projects', [ProjectController::class, 'store']);
Route::post($prefix . '/projects/{id}', [ProjectController::class, 'update']);
Route::get($prefix . '/archive/projects/', [ProjectController::class, 'archive']);
Route::post($prefix . '/projects/addToArchive/{id}', [ProjectController::class, 'addToArchive']);
Route::post($prefix . '/projects/removeFromArchive/{id}', [ProjectController::class, 'removeFromArchive']);
Route::delete($prefix . '/projects/{id}', [ProjectController::class, 'delete']);

// Task
Route::get($prefix . 'columns/{columnId}/tasks', [TaskController::class, 'index']);
Route::get($prefix . 'tasks/{id}', [TaskController::class, 'show']);
Route::post($prefix . '/columns/{columnId}/tasks', [TaskController::class, 'store']);
Route::post($prefix . '/tasks/{id}', [TaskController::class, 'update']);
Route::post($prefix . '/tasks/{id}/relocate', [TaskController::class, 'relocate']);
Route::delete($prefix . '/tasks/{id}', [TaskController::class, 'delete']);

// Column
Route::get($prefix . '/projects/{projectId}/columns', [ColumnController::class, 'index']);
Route::get($prefix . '/columns/{id}', [ColumnController::class, 'show']);
Route::post($prefix . '/projects/{projectId}/columns', [ColumnController::class, 'store']);
Route::post($prefix . '/columns/{id}', [ColumnController::class, 'update']);
Route::post($prefix . '/projects/{projectId}/columns/changeOrder', [ColumnController::class, 'changeOrder']);
Route::delete($prefix . '/columns/{id}', [ColumnController::class, 'delete']);

// Project Member
Route::get($prefix . '/projects/{projectId}/getMembers', [ProjectMemberController::class, 'index']);
Route::post($prefix . '/projects/{projectId}/addMember', [ProjectMemberController::class, 'store']);
Route::post($prefix . '/projects/{projectId}/removeMember', [ProjectMemberController::class, 'delete']);

// Task Comment
Route::get($prefix . '/tasks/{taskId}/comments', [TaskCommentController::class, 'index']);
Route::get($prefix . '/comments/{id}', [TaskCommentController::class, 'show']);
Route::post($prefix . '/tasks/{taskId}/comments', [TaskCommentController::class, 'store']);
Route::post($prefix . '/comments/{id}', [TaskCommentController::class, 'update']);
Route::delete($prefix . '/comments/{id}', [TaskCommentController::class, 'delete']);
