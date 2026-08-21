<?php

namespace App\Livewire\PublicSite;

use App\Services\FacetService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ResourceBrowser extends Component
{
    use WithPagination;

    public string $type = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public array $categories = [];

    /**
     * Retained for FacetService compatibility; tag filtering is no longer
     * exposed in the public UI.
     */
    public array $tags = [];

    #[Url(as: 'sort')]
    public string $sort = 'newest';

    public bool $drawerOpen = false;

    public int $perPage = 24;

    public function mount(string $type): void
    {
        $this->type = $type;
    }

    public function modelClass(): string
    {
        return app(FacetService::class)->modelClass($this->type);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategories(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function removeCategory(int $id): void
    {
        $this->categories = array_values(array_diff($this->categories, [$id]));
    }

    public function clearAll(): void
    {
        $this->search = '';
        $this->categories = [];
        $this->tags = [];
        $this->sort = 'newest';
    }

    public function openDrawer(): void
    {
        $this->drawerOpen = true;
    }

    public function closeDrawer(): void
    {
        $this->drawerOpen = false;
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' || $this->categories !== [] || $this->sort !== 'newest';
    }

    public function getBaseProperty()
    {
        return app(FacetService::class)->baseQuery($this->modelClass(), $this->search);
    }

    public function getAvailableCategoriesProperty()
    {
        return app(FacetService::class)->categoriesWithCounts($this->base, $this->modelClass(), $this->tags);
    }

    public function getResultsProperty()
    {
        return app(FacetService::class)->results(
            $this->base,
            $this->categories,
            [],
            $this->sort,
            $this->perPage,
        );
    }

    public function getTotalProperty(): int
    {
        return app(FacetService::class)->totalCount($this->base);
    }

    public function render()
    {
        return view('livewire.public.resource-browser');
    }
}
