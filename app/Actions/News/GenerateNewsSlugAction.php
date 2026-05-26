<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Support\Str;

class GenerateNewsSlugAction
{
    public function execute(string $title, ?int $ignoreNewsId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreNewsId)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreNewsId): bool
    {
        return News::query()
            ->where('slug', $slug)
            ->when($ignoreNewsId, fn ($query) => $query->whereKeyNot($ignoreNewsId))
            ->exists();
    }
}
