{{-- pages/fournisseurs/partials/list.blade.php --}}
@forelse($fournisseurs as $fournisseur)
<div class="col-12 col-xl-6 mb-4">
    <div class="card h-100 border-0 shadow-sm hover-shadow">
        <div class="card-body p-4">
            {{-- En-tête de la carte --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="fournisseur-icon me-3">
                        <i class="fas fa-user-tie fa-lg text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">{{ $fournisseur->raison_sociale }}</h5>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-soft-primary text-primary rounded-pill">
                                <i class="fas fa-hashtag fs-xs me-1"></i>
                                {{ $fournisseur->code_fournisseur }}
                            </span>
                            <span class="badge {{ $fournisseur->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                <i class="fas fa-circle fs-xs me-1"></i>
                                {{ $fournisseur->statut ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-muted ms-2 small">
                                <i class="far fa-clock me-1"></i>
                                {{ $fournisseur->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                            </span>
                            <strong class="badge bg-success border-white text-white ms-2">
                                <i class="far fa-clock me-1"></i>
                               Solde :  {{ number_format($fournisseur->approvisionnements()->sum("montant"),2)  }} FCFA
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="editFournisseur({{ $fournisseur->id }})">
                                <i class="far fa-edit me-2 text-warning"></i>
                                Modifier
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteFournisseur({{ $fournisseur->id }})">
                                <i class="far fa-trash-alt me-2"></i>
                                Supprimer
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Contenu de la carte --}}
            <div class="card-content mb-4">
                @if($fournisseur->adresse)
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                    <p class="mb-0 text-muted">{{ $fournisseur->adresse }}</p>
                </div>
                @endif

                <div class="d-flex flex-wrap gap-4 mt-3">
                    @if($fournisseur->telephone)
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <span>{{ $fournisseur->telephone }}</span>
                    </div>
                    @endif

                    @if($fournisseur->email)
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <span>{{ $fournisseur->email }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Statistiques futures --}}
            <div class="row g-3">
                <div class="col-auto">
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Commandes</span>
                        <h6 class="mb-0 mt-1">0</h6>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Articles</span>
                        <h6 class="mb-0 mt-1">0</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <div class="empty-state">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-users fa-2x text-muted"></i>
                </div>
                <h5 class="empty-state-title">Aucun fournisseur</h5>
                <p class="empty-state-description text-muted">
                    Commencez par ajouter votre premier fournisseur en cliquant sur le bouton "Nouveau Fournisseur".
                </p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">
                    <i class="fas fa-plus me-2"></i>Ajouter un fournisseur
                </button>
            </div>
        </div>
    </div>
</div>
@endforelse