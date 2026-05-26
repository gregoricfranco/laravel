<?php

namespace App\Actions\News;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadNewsImageAction
{
    public function execute(?UploadedFile $image, ?string $oldImage = null): ?string
    {
        if (! $image) {
            return $oldImage;
        }

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $image->store('news', 'public');
    }
}
