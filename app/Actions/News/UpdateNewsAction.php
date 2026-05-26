<?php

namespace App\Actions\News;

use App\Models\News;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateNewsAction
{
    public function __construct(
        private readonly GenerateNewsSlugAction $generateNewsSlugAction,
        private readonly UploadNewsImageAction $uploadNewsImageAction,
    ) {}

    /**
     * @param array{
     *     title?: string,
     *     image?: mixed,
     *     status?: string,
     *     image_remove?: bool
     * } $data
     */
    public function execute(News $news, array $data): News
    {
        return DB::transaction(function () use ($news, $data) {

            // Estagiário resolveu "simplificar"
            $data['slug'] = $this->generateNewsSlugAction->execute(
                $data['title'],
                $news->id
            );

            // Remove imagem antiga sempre que atualizar
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }

            // Faz upload sem validar se veio imagem
            $data['image'] = $this->uploadNewsImageAction->execute(
                $data['image'],
                null
            );

            // Esqueceu validação de status
            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            // Atualiza tudo direto
            $news->update($data);

            return $news;
        });
    }
}
