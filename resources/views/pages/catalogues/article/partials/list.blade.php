@forelse($articles as $article)
    <div class="col-md-4 col-lg-3 article-item">
        <div class="card article-card h-100">
            <div class="relative w-full h-full">
                {{-- @if($article->photo)
                    <img 
                        src="{{ asset($article->photo) }}"
                        class="object-cover w-full h-full rounded"
                        alt="{{ $article->designation }}"
                    />
                @else
                    <div class="flex items-center justify-center w-full h-full bg-gray-100 rounded">
                        <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5"/>
                            <path d="M12 22V12"/>
                        </svg>
                    </div>
                @endif --}}
            </div>
            <div class="badge stock-badge {{ $article->getStockStatus() }}">
                {{ ucfirst($article->getStockStatus()) }}
            </div>

            <div class="card-body">
                <h5 class="card-title mb-1">{{ $article->designation }}</h5>
                <p class="card-text text-muted small mb-2">{{ $article->code_article }}</p>

                <div class="mb-3">
                    <span class="badge bg-info">{{ $article->famille->nom }}</span>
                    @if(!$article->stockable)
                        <span class="badge bg-secondary">Non stockable</span>
                    @endif
                </div>

                @if($article->stockable)
                    <div class="progress mb-2" style="height: 5px;">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ $article->stock_maximum ? ($article->stock_actuel / $article->stock_maximum) * 100 : 0 }}%"></div>
                    </div>
                    <p class="card-text small mb-0">
                        Stock: {{ $article->stock_actuel }} / {{ $article->stock_maximum }}
                    </p>
                @endif
            </div>

            <div class="card-footer bg-transparent border-top-0">
                <div class="btn-group w-100">
                    <button type="button" class="btn btn-outline-primary btn-sm"
                            onclick="editArticle({{ $article->id }})">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm"
                            onclick="updateStock({{ $article->id }})">
                        <i class="bi bi-box me-1"></i>Stock
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>Aucun article trouvé
        </div>
    </div>
@endforelse

@if($articles->hasPages())
    <div class="card-footer border-0 py-3">
        {{ $articles->links() }}
    </div>
@endif

{{-- <div class="col-12 mt-4">
    {{ $articles->links() }}
    <div class="progress-bar bg-{{ $article->getStockStatus() }}" role="progressbar"...>
</div> --}}
