<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateNewsAction
{
    public function __construct(
        private readonly GenerateNewsSlugAction $generateNewsSlugAction,
        private readonly UploadNewsImageAction $uploadNewsImageAction,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(News $news, array $data): News
    {
        return DB::transaction(function () use ($news, $data) {
            $titleValue = $data['title'] ?? '';
            $title = is_string($titleValue) ? $titleValue : $news->title;
            $status = ($data['status'] ?? null) === 'published' ? 'published' : 'draft';
            $image = $data['image'] ?? null;

            $data['status'] = $status;

            if ($title !== $news->title) {
                $data['slug'] = $this->generateNewsSlugAction->execute($title, $news->id);
            }

            if ($image instanceof UploadedFile) {
                $data['image'] = $this->uploadNewsImageAction->execute($image, $news->image);
            } elseif (! empty($data['image_remove']) && $news->image) {
                Storage::disk('public')->delete($news->image);
                $data['image'] = null;
            }

            if ($status === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if ($status === 'draft') {
                $data['published_at'] = null;
            }

            /** @var array<string, mixed> $attributes */
            $attributes = Arr::except($data, ['image_remove']);

            $news->update($attributes);

            return $news->refresh();
        });
    }
}
