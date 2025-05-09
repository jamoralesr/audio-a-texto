<?php

namespace App\Models;

use App\Events\TranscriptionCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transcription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recording_id',
        'content',
        'language',
        'service_used',
        'service_response',
        'confidence_score',
        'is_edited',
        'email_sent',
        'email_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'service_response' => 'array',
        'confidence_score' => 'float',
        'is_edited' => 'boolean',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the recording that owns the transcription.
     */
    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }

    /**
     * Get the record associated with the transcription.
     */
    public function record(): HasOne
    {
        return $this->hasOne(Record::class);
    }
    
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TranscriptionCreated::class,
    ];
}
