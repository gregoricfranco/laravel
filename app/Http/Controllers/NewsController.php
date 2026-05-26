<?php

namespace App\Http\Controllers;

use App\Actions\News\CreateNewsAction;
use App\Actions\News\DeleteNewsAction;
use App\Actions\News\SearchNewsAction;
use App\Actions\News\UpdateNewsAction;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request, SearchNewsAction $searchNewsAction): View
    {
        /** @var array<string, mixed> $filters */
        $filters = $request->only(['query', 'status']);

        $news = $searchNewsAction->execute($filters);

        return view('news.index', compact('news'));
    }

    public function create(): View
    {
        return view('news.create', [
            'news' => new News(['status' => 'draft']),
        ]);
    }

    public function store(StoreNewsRequest $request, CreateNewsAction $createNewsAction): RedirectResponse
    {
        $createNewsAction->execute($request->validated());

        return redirect()
            ->route('news.index')
            ->with('success', 'Noticia criada com sucesso.');
    }

    public function edit(News $news): View
    {
        return view('news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news, UpdateNewsAction $updateNewsAction): RedirectResponse
    {
        $updateNewsAction->execute($news, $request->validated());

        return redirect()
            ->route('news.index')
            ->with('success', 'Noticia atualizada com sucesso.');
    }

    public function destroy(News $news, DeleteNewsAction $deleteNewsAction): RedirectResponse
    {
        $deleteNewsAction->execute($news);

        return redirect()
            ->route('news.index')
            ->with('success', 'Noticia excluida com sucesso.');
    }
}
