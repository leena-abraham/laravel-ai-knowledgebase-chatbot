<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'domain',
        'logo',
        'description',
        'subscription_plan',
        'max_documents',
        'max_chats_per_month',
        'current_month_chats',
        'is_active',
        'email_verified_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('is_active', true)->latest();
    }

    public function canUploadDocument(): bool
    {
        return $this->documents()->count() < $this->max_documents;
    }

    public function canCreateChat(): bool
    {
        return $this->current_month_chats < $this->max_chats_per_month;
    }

    public function incrementChatCount(): void
    {
        $this->increment('current_month_chats');
    }

    public function resetMonthlyChatCount(): void
    {
        $this->update(['current_month_chats' => 0]);
    }
}
