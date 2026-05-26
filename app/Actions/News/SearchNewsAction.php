<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchNewsAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, News>
     */
    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filters['query'] ?? null;
        $status = $filters['status'] ?? null;

        /** @var Builder<News> $query */
        $query = News::query();

        if (is_string($search) && $search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        return $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
