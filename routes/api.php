<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
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

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Project
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::post('/projects/{id}', [ProjectController::class, 'update']);
Route::get('/archive/projects/', [ProjectController::class, 'archive']);
Route::post('/projects/addToArchive/{id}', [ProjectController::class, 'addToArchive']);
Route::delete('/projects/{id}', [ProjectController::class, 'delete']);

// Task
Route::get('columns/{columnId}/tasks', [TaskController::class, 'index']);
Route::get('tasks/{id}', [TaskController::class, 'show']);
Route::post('columns/{columnId}/tasks', [TaskController::class, 'store']);
Route::post('tasks/{id}', [TaskController::class, 'update']);
Route::post('tasks/{id}/relocate', [TaskController::class, 'relocate']);
Route::delete('tasks/{id}', [TaskController::class, 'delete']);

// Column
Route::get('projects/{projectId}/columns', [ColumnController::class, 'index']);
Route::get('columns/{id}', [ColumnController::class, 'show']);
Route::post('projects/{projectId}/columns', [ColumnController::class, 'store']);
Route::post('columns/{id}', [ColumnController::class, 'update']);
Route::post('projects/{projectId}/columns/changeOrder', [ColumnController::class, 'changeOrder']);
Route::delete('columns/{id}', [ColumnController::class, 'delete']);

// Project Member
Route::get('projects/{projectId}/getMembers', [ProjectMemberController::class, 'index']);
Route::post('projects/{projectId}/addMember', [ProjectMemberController::class, 'store']);
Route::post('projects/{projectId}/removeMember', [ProjectMemberController::class, 'delete']);