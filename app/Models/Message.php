<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'company_id',
        'role',
        'content',
        'context_chunks',
        'model_used',
        'tokens_used',
        'response_time',
        'was_helpful',
        'feedback',
    ];

    protected $casts = [
        'context_chunks' => 'array',
        'response_time' => 'decimal:2',
        'was_helpful' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function markAsHelpful(bool $helpful, ?string $feedback = null): void
    {
        $this->update([
            'was_helpful' => $helpful,
            'feedback' => $feedback,
        ]);
    }
}
