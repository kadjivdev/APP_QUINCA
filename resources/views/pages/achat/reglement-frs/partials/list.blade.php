<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="reglementsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date Règlement</th>
                            <th class="border-bottom-0">Facture</th>
                            <th class="border-bottom-0">Mode</th>
                            <th class="border-bottom-0">Référence</th>
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reglements as $reglement)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <div class="d-flex align-items-center">
                                        <span class="code-reglement me-2">{{ $reglement->code }}</span>
                                        @if ($reglement->validated_at)
                                            <i class="fas fa-check-circle text-success" data-bs-toggle="tooltip"
                                                title="Règlement validé"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ Carbon\Carbon::parse($reglement->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                                <td>
                                    <a href="#" class="code-facture"
                                        onclick="showFacture({{ $reglement->facture_id }})">
                                        {{ $reglement->facture->code }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-soft-info text-info">
                                        {{ $reglement->mode_reglement }}
                                    </span>
                                </td>
                                <td>{{ $reglement->reference_reglement ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-fournisseur me-2">
                                            {{ substr($reglement->facture->fournisseur->raison_sociale, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">
                                                {{ $reglement->facture->fournisseur->raison_sociale }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold">{{ number_format($reglement->montant_reglement, 2) }} FCFA
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($reglement->validated_at)
                                        <span class="badge bg-success">Validé</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light-primary btn-icon"
                                            onclick="showReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                            title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if (!$reglement->validated_at)
                                            <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                                onclick="editReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                                onclick="validateReglement({{ $reglement->id }})"
                                                data-bs-toggle="tooltip" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                                onclick="deleteReglement({{ $reglement->id }})"
                                                data-bs-toggle="tooltip" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-light-secondary btn-icon ms-1"
                                            onclick="printReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                            title="Imprimer">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted mb-1">Aucun règlement trouvé</h6>
                                        <p class="text-muted small mb-3">Les règlements créés apparaîtront ici</p>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#addReglementModal">
                                            <i class="fas fa-plus me-2"></i>Créer un règlement
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($reglements->hasPages())
                <div class="card-footer bg-white border-0 pt-0">
                    {{ $reglements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
