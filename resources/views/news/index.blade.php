@extends('layouts.app', ['title' => 'Noticias'])

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Noticias</h1>
            <p class="mt-1 text-sm text-slate-600">Gerencie artigos, rascunhos e publicacoes.</p>
        </div>

        <form method="GET" action="{{ route('news.index') }}" class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_160px_auto]">
            <input
                type="search"
                name="query"
                value="{{ request('query') }}"
                placeholder="Buscar por titulo"
                class="rounded border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
            >

            <select name="status" class="rounded border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todos</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
            </select>

            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Filtrar
            </button>
        </form>
    </div>

    @if ($news->isEmpty())
        <div class="rounded border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <h2 class="text-lg font-medium">Nenhuma noticia encontrada</h2>
            <p class="mt-2 text-sm text-slate-600">Crie a primeira noticia ou ajuste os filtros da busca.</p>
            <a href="{{ route('news.create') }}" class="mt-5 inline-flex rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Criar noticia
            </a>
        </div>
    @else
        <div class="overflow-hidden rounded border bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Titulo</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Publicacao</th>
                        <th class="px-4 py-3 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($news as $item)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-medium text-slate-900">{{ $item->title }}</div>
                                <div class="mt-1 max-w-xl truncate text-slate-500">{{ $item->summary }}</div>
                            </td>
                            <td class="px-4 py-4 text-slate-700">{{ $item->category }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded px-2 py-1 text-xs font-medium {{ $item->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ $item->published_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('news.edit', $item) }}" class="rounded border px-3 py-2 text-xs font-medium hover:bg-slate-50">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('news.destroy', $item) }}" onsubmit="return confirm('Deseja excluir esta noticia?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-red-200 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $news->links() }}
        </div>
    @endif
@endsection
