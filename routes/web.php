<?php

use App\Http\Controllers\HakaruAiTokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
});

// hakaru-aiテスト
Route::get('/hakaru-ai/access-token', [HakaruAiTokenController::class, 'accessToken'])->name('hakaru-ai.accessToken');
Route::post('/hakaru-ai/refresh-token', [HakaruAiTokenController::class, 'refreshToken'])->name('hakaru-ai.refreshToken');
Route::get('/hakaru-ai/create', function () {
  return view('hakaruAi.create');
})->name('hakaru-ai.create');
Route::post('/hakaru-ai/upload-image', [HakaruAiTokenController::class, 'uploadImage'])->name('hakaru-ai.uploadImage');
