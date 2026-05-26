<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Support\Facades\Storage;

class DeleteNewsAction
{
    public function execute(News $news): void
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();
    }
}
