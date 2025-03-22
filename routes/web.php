<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioRecordingController;

// Audio recording routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::post('/audio/upload', [AudioRecordingController::class, 'upload'])->name('audio.upload');
});
