<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateNewsAction
{
    public function __construct(
        private readonly GenerateNewsSlugAction $generateNewsSlugAction,
        private readonly UploadNewsImageAction $uploadNewsImageAction,
    ) {}

    public function execute(array $data): News
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'draft';
            $data['slug'] = $this->generateNewsSlugAction->execute($data['title']);
            $data['image'] = $this->uploadNewsImageAction->execute($data['image'] ?? null);

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            return News::create(Arr::except($data, ['image_remove']));
        });
    }
}
