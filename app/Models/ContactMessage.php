<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'subject',
        'message',
        'status',
        'read_at',
        'member_read_at',
        'reference_code',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContactMessage $message) {
            if (blank($message->reference_code)) {
                $nextId = (int) static::query()->max('id') + 1;
                $message->reference_code = 'MSG-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactReply::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /** Whether an admin has not yet opened this message. */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /** Whether the sender has seen the latest interaction. */
    public function isMemberUnread(): bool
    {
        return $this->member_read_at === null;
    }

    public function markRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public function markMemberRead(): void
    {
        if ($this->isMemberUnread()) {
            $this->forceFill(['member_read_at' => now()])->save();
        }
    }
}
