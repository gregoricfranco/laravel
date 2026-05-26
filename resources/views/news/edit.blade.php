@extends('layouts.app', ['title' => 'Editar noticia'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Editar noticia</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $news->slug }}</p>
    </div>

    <form method="POST" action="{{ route('news.update', $news) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('news._form', ['buttonLabel' => 'Salvar alteracoes'])
    </form>
@endsection
