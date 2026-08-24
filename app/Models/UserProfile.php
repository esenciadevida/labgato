<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Model
{
    public const STATUSES = [
        'student',
        'intern',
        'scientist',
        'technician',
        'professional',
        'researcher',
        'lecturer',
        'other',
    ];

    public const STUDENT_STATUSES = ['student', 'intern'];

    public const PROFESSIONAL_STATUSES = ['scientist', 'technician', 'professional', 'researcher', 'lecturer'];

    public const STATUS_LABELS = [
        'student' => 'Student',
        'intern' => 'Intern',
        'scientist' => 'Scientist',
        'technician' => 'Laboratory Technician',
        'professional' => 'Professional',
        'researcher' => 'Researcher',
        'lecturer' => 'Lecturer / Educator',
        'other' => 'Other',
    ];

    protected $fillable = [
        'user_id',
        'display_name',
        'country',
        'photo_path',
        'professional_status',
        'institution',
        'programme',
        'level',
        'graduation_year',
        'employer',
        'job_title',
        'years_of_experience',
        'specialization',
        'show_country',
        'show_institution',
        'show_on_leaderboard',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'years_of_experience' => 'integer',
            'show_country' => 'boolean',
            'show_institution' => 'boolean',
            'show_on_leaderboard' => 'boolean',
            'profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isStudent(): bool
    {
        return in_array($this->professional_status, self::STUDENT_STATUSES, true);
    }

    public function isProfessional(): bool
    {
        return in_array($this->professional_status, self::PROFESSIONAL_STATUSES, true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->professional_status] ?? '';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    /**
     * Percentage of profile fields completed (0-100), based on professional
     * status plus the fields relevant to that status.
     */
    public function completionPercent(): int
    {
        if (! in_array($this->professional_status, self::STATUSES, true)) {
            return 0;
        }

        $fields = [
            $this->professional_status,
            $this->display_name,
            $this->country,
            $this->photo_path,
        ];

        if ($this->isStudent()) {
            $fields[] = $this->institution;
            $fields[] = $this->programme;
        } elseif ($this->isProfessional()) {
            $fields[] = $this->employer;
            $fields[] = $this->job_title;
        }

        $filled = count(array_filter($fields, fn ($value) => filled($value)));

        return (int) round(($filled / count($fields)) * 100);
    }
}
