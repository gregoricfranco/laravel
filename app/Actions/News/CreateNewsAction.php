<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateNewsAction
{
    public function __construct(
        private readonly GenerateNewsSlugAction $generateNewsSlugAction,
        private readonly UploadNewsImageAction $uploadNewsImageAction,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): News
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image'] ?? null;
            $title = $data['title'] ?? '';

            $data['status'] = $data['status'] ?? 'draft';
            $data['slug'] = $this->generateNewsSlugAction->execute(is_string($title) ? $title : '');
            $data['image'] = $this->uploadNewsImageAction->execute($image instanceof UploadedFile ? $image : null);

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            /** @var array<string, mixed> $attributes */
            $attributes = Arr::except($data, ['image_remove']);

            return News::create($attributes);
        });
    }
}
