@forelse($depots as $depot)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="depot-icon me-3">
                            <i class="fas fa-warehouse fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $depot->libelle_depot }}</h5>
                            <div class="d-flex align-items-center flex-wrap">
                                <span
                                    class="badge {{ $depot->actif ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $depot->actif ? 'Actif' : 'Inactif' }}
                                </span>
                                @if ($depot->typeDepot)
                                    <span class="badge bg-soft-primary text-primary rounded-pill ms-2">
                                        <i class="fas fa-tag fs-xs me-1"></i>
                                        {{ $depot->typeDepot->libelle_type_depot }}
                                    </span>
                                @endif
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $depot->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)"
                                    onclick="editDepot({{ $depot->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="toggleDepotStatus({{ $depot->id }})">
                                    <i
                                        class="fas {{ $depot->actif ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                    {{ $depot->actif ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                            @if (!$depot->depot_principal)
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                        onclick="deleteDepot({{ $depot->id }})">
                                        <i class="far fa-trash-alt me-2"></i>
                                        Supprimer
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Contenu de la carte --}}
                <div class="card-content mb-4">
                    @if ($depot->adresse_depot)
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                            <p class="mb-0 text-muted">{{ $depot->adresse_depot }}</p>
                        </div>
                    @endif
                    @if ($depot->tel_depot)
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <span class="text-muted">{{ $depot->tel_depot }}</span>
                        </div>
                    @endif
                </div>

                {{-- Statistiques --}}
                <div class="row g-3">
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Code</span>
                            <h6 class="mb-0 mt-1">{{ $depot->code_depot }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Points de Vente</span>
                            <h6 class="mb-0 mt-1">{{ $depot->pointsVente?->nom_pv }}</h6>
                        </div>
                    </div>
                    @if ($depot->depot_principal)
                        <div class="col-auto">
                            <div class="stat-item bg-soft-primary">
                                <span class="stat-label text-primary small">Magasin Principal</span>
                            </div>
                        </div>
                    @endif
                    <div class="col-auto">
                        <div class="stat-item {{ $depot->typeDepot ? 'bg-soft-info' : 'bg-soft-warning' }}">
                            <span class="stat-label {{ $depot->typeDepot ? 'text-info' : 'text-warning' }} small">
                                <i class="fas fa-tag me-1"></i>
                                {{ $depot->typeDepot ? $depot->typeDepot->code_type_depot : 'Non catégorisé' }}
                            </span>
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
                        <i class="fas fa-warehouse fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucun magasin</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par ajouter votre premier magasin en cliquant sur le bouton "Nouveau Magasin".
                    </p>
                    {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepotModal">
                        <i class="fas fa-plus me-2"></i>Ajouter un magasin
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
@endforelse
