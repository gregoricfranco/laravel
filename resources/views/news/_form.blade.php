<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <div class="space-y-5 rounded border bg-white p-5 shadow-sm">
        <div>
            <label for="title" class="mb-1 block text-sm font-medium">Titulo</label>
            <input id="title" name="title" type="text" value="{{ old('title', $news->title) }}" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('title') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="summary" class="mb-1 block text-sm font-medium">Resumo</label>
            <textarea id="summary" name="summary" rows="3" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('summary', $news->summary) }}</textarea>
            @error('summary') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="content" class="mb-1 block text-sm font-medium">Conteudo</label>
            <textarea id="content" name="content" rows="10" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('content', $news->content) }}</textarea>
            @error('content') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <aside class="space-y-5">
        <div class="rounded border bg-white p-5 shadow-sm">
            <label for="category" class="mb-1 block text-sm font-medium">Categoria</label>
            <input id="category" name="category" type="text" value="{{ old('category', $news->category) }}" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('category') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="rounded border bg-white p-5 shadow-sm">
            <label for="status" class="mb-1 block text-sm font-medium">Status</label>
            <select id="status" name="status" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="draft" @selected(old('status', $news->status ?? 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $news->status) === 'published')>Published</option>
            </select>
            @error('status') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror

            <label for="published_at" class="mb-1 mt-4 block text-sm font-medium">Publicado em</label>
            <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $news->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded border-slate-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('published_at') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="rounded border bg-white p-5 shadow-sm">
            <label for="image" class="mb-2 block text-sm font-medium">Imagem</label>

            <div class="mb-3 overflow-hidden rounded border bg-slate-100">
                <img
                    id="image-preview"
                    src="{{ $news->image ? \Illuminate\Support\Facades\Storage::url($news->image) : '' }}"
                    alt=""
                    class="{{ $news->image ? 'block' : 'hidden' }} h-44 w-full object-cover"
                >
                <div id="image-empty" class="{{ $news->image ? 'hidden' : 'flex' }} h-44 items-center justify-center text-sm text-slate-500">
                    Sem imagem
                </div>
            </div>

            <input id="image" name="image" type="file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white">
            @error('image') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror

            @if ($news->image)
                <label class="mt-3 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="image_remove" value="1" class="rounded border-slate-300">
                    Remover imagem atual
                </label>
            @endif
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ $buttonLabel }}
            </button>
            <a href="{{ route('news.index') }}" class="rounded border px-4 py-2 text-sm font-medium hover:bg-white">
                Cancelar
            </a>
        </div>
    </aside>
</div>

<script>
    document.getElementById('image')?.addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('image-preview');
        const empty = document.getElementById('image-empty');

        if (!file) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        preview.classList.add('block');
        empty.classList.add('hidden');
        empty.classList.remove('flex');
    });
</script>
