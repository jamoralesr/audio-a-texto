<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Recording extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'duration',
        'status',
        'metadata',
        'is_public',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'duration' => 'integer',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the recording.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transcription associated with the recording.
     */
    public function transcription(): HasOne
    {
        return $this->hasOne(Transcription::class);
    }

    /**
     * Get the record associated with the recording through transcription.
     */
    public function record(): HasOneThrough
    {
        return $this->hasOneThrough(Record::class, Transcription::class);
    }
}
