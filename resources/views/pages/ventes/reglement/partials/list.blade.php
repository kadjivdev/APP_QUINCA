{{-- list-reglements.blade.php --}}
<div class="row g-3">
    {{-- Filtres --}}


    {{-- Table des règlements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="reglementsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N° Reçu</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date règlement</th>
                            <th class="border-bottom-0">N° Facture</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0">Mode</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            {{-- <th class="border-bottom-0 text-end">Mode Règlement</th> --}}
                            <th class="border-bottom-0">Référence</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reglements as $reglement)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <span class="numero-recu me-2">{{ $reglement->numero }}</span>
                                </td>
                                <td>{{ Carbon\Carbon::parse($reglement->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                                <td>
                                    <a href="#" class="text-decoration-none">
                                        {{ $reglement->facture->numero }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-client me-2">
                                            {{ substr($reglement->facture->client->raison_sociale, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $reglement->facture->client->raison_sociale }}
                                            </div>
                                            <div class="text-muted small">{{ $reglement->facture->client->telephone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($reglement->type_reglement) }}
                                    </span>
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($reglement->montant, 0, ',', ' ') }} F
                                </td>
                                {{-- <td>
                                    <span class="text-muted small">{{ $reglement->type_reglement }}</span>
                                </td> --}}
                                <td>
                                    <span class="text-muted small">{{ $reglement->reference_paiement }}</span>
                                </td>
                                <td class="text-center">
                                    @switch($reglement->statut)
                                        @case('brouillon')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3">Brouillon</span>
                                        @break

                                        @case('validee')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3">Validé</span>
                                        @break

                                        @default
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3">Annulé</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        {{-- Voir détails --}}
                                        <button class="btn btn-sm btn-light-primary btn-icon"
                                            onclick="showReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                            title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if ($reglement->statut === 'brouillon')
                                            {{-- Modifier --}}
                                            <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                                onclick="editReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Valider --}}
                                            <button
                                                class="btn btn-sm btn-light-success btn-icon ms-1 btn-validate-reglement"
                                                {{-- onclick="validateReglement({{ $reglement->id }})" --}} data-reglement-id="{{ $reglement->id }}"
                                                data-bs-toggle="tooltip" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            @if ($reglement->statut !== 'annule')
                                                <button
                                                    class="btn btn-sm btn-light-danger btn-icon ms-1 btn-cancel-reglement"
                                                    data-reglement-id="{{ $reglement->id }}" data-bs-toggle="tooltip"
                                                    title="Annuler le règlement">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif

                                            {{-- Supprimer --}}
                                            <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                                onclick="deleteReglement({{ $reglement->id }})"
                                                data-bs-toggle="tooltip" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        {{-- Imprimer --}}
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
                                            <p class="text-muted small mb-3">Les règlements que vous créez apparaîtront ici
                                            </p>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
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
                    <div class="card-footer border-0 py-3">
                        {{ $reglements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <link href="{{ asset('css/theme/table.css') }}" rel="stylesheet">

    <style>
        .numero-recu {
            font-family: 'Monaco', 'Consolas', monospace;
            color: var(--bs-primary);
            font-weight: 500;
            padding: 0.3rem 0.6rem;
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        .avatar-client {
            width: 40px;
            height: 40px;
            background-color: var(--bs-light);
            color: var(--bs-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
        }
    </style>
