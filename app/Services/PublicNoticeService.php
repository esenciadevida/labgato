<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Popup;
use Illuminate\Database\Eloquent\Collection;

class PublicNoticeService
{
    public function activePopup(): ?Popup
    {
        return Popup::query()->active()->orderByDesc('created_at')->first();
    }

    public function activePopups(): Collection
    {
        return Popup::query()->active()->orderByDesc('created_at')->get();
    }

    public function activeAnnouncements(): Collection
    {
        return Announcement::query()->active()->orderByDesc('created_at')->get();
    }
}
