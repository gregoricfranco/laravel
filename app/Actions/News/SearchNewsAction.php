<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchNewsAction
{
    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return News::query()
            ->when($filters['query'] ?? null, function ($query, string $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, function ($query, string $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
