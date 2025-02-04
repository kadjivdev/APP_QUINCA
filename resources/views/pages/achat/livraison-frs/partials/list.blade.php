<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 rounded-3 shadow-sm">


                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0" id="livraisonsFournisseurTable">
                        <thead>
                            <tr class="bg-light">
                                <th class="px-4 py-3 text-secondary small text-uppercase">Code BL</th>
                                <th class="py-3 text-secondary small text-uppercase">Date Insertion</th>
                                <th class="py-3 text-secondary small text-uppercase">Date</th>
                                <th class="py-3 text-secondary small text-uppercase">Fournisseur</th>
                                <th class="py-3 text-secondary small text-uppercase">Magasin</th>
                                <th class="py-3 text-secondary small text-uppercase">Transport</th>
                                <th class="py-3 text-secondary small text-uppercase">Articles</th>
                                <th class="py-3 text-secondary small text-uppercase text-center">Statut</th>
                                <th class="pe-4 py-3 text-end" style="min-width: 150px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($livraisons as $livraison)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-warning">{{ $livraison->code }}</span>
                                    </td>
                                    <td>{{ Carbon\Carbon::parse($livraison->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td class="py-3 text-muted">{{ $livraison->date_livraison->format('d/m/Y') }}</td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 bg-warning bg-opacity-10 me-3">
                                                <i class="fas fa-building text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $livraison->fournisseur->raison_sociale }}
                                                </div>
                                                @if ($livraison->fournisseur->telephone)
                                                    <div class="text-muted small">
                                                        {{ $livraison->fournisseur->telephone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 bg-success bg-opacity-10 me-3">
                                                <i class="fas fa-warehouse text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $livraison->depot->libelle_depot }}</div>
                                                <div class="text-muted small">{{ $livraison->depot->code_depot }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        @if ($livraison->vehicule && $livraison->chauffeur)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle p-2 bg-info bg-opacity-10 me-3">
                                                    <i class="fas fa-truck text-info"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $livraison->vehicule->matricule }}</div>
                                                    <div class="text-muted small">{{ $livraison->chauffeur->nom_chauf }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Non spécifié</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3">
                                            {{ $livraison->lignes->count() }} article(s)
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        @if ($livraison->validated_at)
                                            <span
                                                class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">Validé</span>
                                        @elseif($livraison->rejected_at)
                                            <span
                                                class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3">Rejeté</span>
                                        @else
                                            <span
                                                class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3">En
                                                attente</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-link btn-sm text-dark p-2"
                                                onclick="showLivraisonFournisseur({{ $livraison->id }})"
                                                data-bs-toggle="tooltip" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if (!$livraison->validated_at && !$livraison->rejected_at)
                                                <button class="btn btn-link btn-sm text-warning p-2"
                                                    onclick="editLivraisonFournisseur({{ $livraison->id }})"
                                                    data-bs-toggle="tooltip" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button class="btn btn-link btn-sm text-success p-2"
                                                    onclick="validateLivraisonFournisseur({{ $livraison->id }})"
                                                    data-bs-toggle="tooltip" title="Valider">
                                                    <i class="fas fa-check"></i>
                                                </button>

                                                <button class="btn btn-link btn-sm text-danger p-2"
                                                    onclick="initRejetLivraison({{ $livraison->id }})"
                                                    data-bs-toggle="tooltip" title="Rejeter">
                                                    <i class="fas fa-times"></i>
                                                </button>

                                                <button class="btn btn-link btn-sm text-danger p-2 btn-delete-livraison"
                                                    data-id="{{ $livraison->id }}" data-bs-toggle="tooltip"
                                                    title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif

                                            <button class="btn btn-link btn-sm text-secondary p-2"
                                                onclick="printLivraisonFournisseur({{ $livraison->id }})"
                                                data-bs-toggle="tooltip" title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <div
                                                class="rounded-circle bg-warning bg-opacity-10 p-4 mx-auto mb-4 d-inline-flex">
                                                <i class="fas fa-truck-loading fa-2x text-warning"></i>
                                            </div>
                                            <h6 class="text-dark mb-2">Aucun bon de livraison</h6>
                                            <p class="text-muted small mb-4">Les bons de livraison fournisseurs que vous
                                                créez apparaîtront ici</p>
                                            <button class="btn btn-warning rounded-pill px-4" data-bs-toggle="modal"
                                                data-bs-target="#addLivraisonFournisseurModal">
                                                <i class="fas fa-plus me-2"></i>Créer un bon de livraison
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($livraisons->hasPages())
                    <div class="card-footer border-0 bg-white py-3">
                        {{ $livraisons->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-light {
        background: linear-gradient(to right, #fff, #fff8e1);
    }

    .btn-warning {
        background-color: #ffa000;
        border-color: #ffa000;
        color: white;
    }

    .btn-warning:hover {
        background-color: #ff8f00;
        border-color: #ff8f00;
        color: white;
    }

    .text-warning {
        color: #ffa000 !important;
    }

    .btn-group .btn-link:hover {
        background-color: #f8f9fa;
        border-radius: 50%;
    }

    .table> :not(caption)>*>* {
        padding: 1rem 0.75rem;
    }

    .badge {
        font-weight: 500;
    }

    .empty-state {
        padding: 2rem;
    }
</style>
