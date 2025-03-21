<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioRecordingController extends Controller
{
    /**
     * Upload a recorded audio file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:wav,mp3,ogg,webm|max:102400', // 100MB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'nullable|boolean',
            'duration' => 'nullable|integer',
        ]);

        try {
            // Get the uploaded file
            $file = $request->file('audio_file');
            
            // Generate a unique filename
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file in the user's directory
            $filePath = $file->storeAs(
                'recordings/' . Auth::id(),
                $fileName,
                'public'
            );
            
            // Create a new recording record
            $recording = Recording::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'duration' => $request->duration ?? 0,
                'status' => 'pending',
                'metadata' => [
                    'browser' => $request->header('User-Agent'),
                    'ip' => $request->ip(),
                ],
                'is_public' => $request->is_public ?? false,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Audio recording uploaded successfully',
                'recording' => $recording,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload audio recording',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
