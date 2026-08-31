<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LabTestsGuideService
{
    protected ?Collection $inventory = null;

    public function inventory(): Collection
    {
        if ($this->inventory !== null) {
            return $this->inventory;
        }

        $path = base_path('dump/labtestsguide_inventory.json');

        if (! File::exists($path)) {
            return $this->inventory = collect();
        }

        $data = json_decode(File::get($path), true);

        return $this->inventory = collect($data['entries'] ?? []);
    }

    public function findForSlug(string $type, string $slug): ?array
    {
        $match = $this->inventory()
            ->first(fn (array $entry) => $entry['labgoto_type'] === $type && $entry['labgoto_slug'] === $slug);

        return $match ?: null;
    }
}
