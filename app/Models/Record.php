<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transcription_id',
        'content',
        'language',
        'service_used',
        'service_response',
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
        'is_edited' => 'boolean',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the transcription that owns the record.
     */
    public function transcription(): BelongsTo
    {
        return $this->belongsTo(Transcription::class);
    }
}
