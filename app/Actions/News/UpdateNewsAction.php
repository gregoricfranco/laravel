<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateNewsAction
{
    public function __construct(
        private readonly GenerateNewsSlugAction $generateNewsSlugAction,
        private readonly UploadNewsImageAction $uploadNewsImageAction,
    ) {}

    public function execute(News $news, array $data): News
    {
        return DB::transaction(function () use ($news, $data) {
            $data['status'] = $data['status'] ?? 'draft';

            if (($data['title'] ?? $news->title) !== $news->title) {
                $data['slug'] = $this->generateNewsSlugAction->execute($data['title'], $news->id);
            }

            if (! empty($data['image'])) {
                $data['image'] = $this->uploadNewsImageAction->execute($data['image'], $news->image);
            } elseif (! empty($data['image_remove']) && $news->image) {
                Storage::disk('public')->delete($news->image);
                $data['image'] = null;
            }

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }

            $news->update(Arr::except($data, ['image_remove']));

            return $news->refresh();
        });
    }
}
