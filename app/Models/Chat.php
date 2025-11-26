<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'session_id',
        'customer_name',
        'customer_email',
        'customer_ip',
        'user_agent',
        'message_count',
        'is_resolved',
        'satisfaction_score',
        'started_at',
        'last_message_at',
        'ended_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'satisfaction_score' => 'decimal:2',
        'started_at' => 'datetime',
        'last_message_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function addMessage(string $role, string $content, array $metadata = []): Message
    {
        $message = $this->messages()->create([
            'company_id' => $this->company_id,
            'role' => $role,
            'content' => $content,
            'context_chunks' => $metadata['context_chunks'] ?? null,
            'model_used' => $metadata['model_used'] ?? null,
            'tokens_used' => $metadata['tokens_used'] ?? null,
            'response_time' => $metadata['response_time'] ?? null,
        ]);

        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);

        return $message;
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }
}
