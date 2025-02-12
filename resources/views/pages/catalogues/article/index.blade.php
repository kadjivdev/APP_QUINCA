@extends('layouts.catalogue.article')

@push('styles')
    @include('pages.catalogues.article.partials.styles')
@endpush

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.catalogues.article.partials.header')

        {{-- Liste des articles --}}
        <div class="row g-3 list mt-3" id="articlesList">
            @include('pages.catalogues.article.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.catalogues.article.partials.add-modal')
    @include('pages.catalogues.article.partials.edit-modal')
    @include('pages.catalogues.article.partials.import-modal')
@endsection

@push('scripts')
    @include('pages.catalogues.article.partials.scripts')
    @include('pages.catalogues.article.partials.scripts_filter')
    @include('pages.catalogues.article.partials.js-import-modal')
@endpush
