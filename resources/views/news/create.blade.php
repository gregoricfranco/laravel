@extends('layouts.app', ['title' => 'Criar noticia'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Criar noticia</h1>
        <p class="mt-1 text-sm text-slate-600">Cadastre um novo artigo como rascunho ou publicado.</p>
    </div>

    <form method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('news._form', ['buttonLabel' => 'Criar noticia'])
    </form>
@endsection
