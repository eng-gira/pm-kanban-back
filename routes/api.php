<?php

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
Route::delete('tasks/{id}', [TaskController::class, 'delete']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
