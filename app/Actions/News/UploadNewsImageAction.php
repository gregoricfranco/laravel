<?php

namespace App\Actions\News;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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

        $path = $image->store('news', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Nao foi possivel fazer upload da imagem da noticia.');
        }

        return $path;
    }
}
