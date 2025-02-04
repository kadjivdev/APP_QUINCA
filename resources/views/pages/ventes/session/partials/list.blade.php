<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="sessionsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N° Session</th>
                            <th class="border-bottom-0">Caissier</th>
                            <th class="border-bottom-0">Ouverture</th>
                            <th class="border-bottom-0">Fermeture</th>
                            <th class="border-bottom-0 text-end">Montant Initial</th>
                            <th class="border-bottom-0 text-end">Montant Final</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <span class="numero-session">{{ $session->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-user me-2">
                                            {{ substr($session->utilisateur->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $session->utilisateur->name }}</div>
                                            <div class="text-muted small">Caissier</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-success me-2"></i>
                                        {{ \Carbon\Carbon::parse($session->date_ouverture)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    @if($session->date_fermeture)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-danger me-2"></i>
                                            {{ \Carbon\Carbon::parse($session->date_fermeture)->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($session->montant_ouverture, 0, ',', ' ') }} F
                                </td>
                                <td class="text-end fw-medium">
                                    {{ $session->montant_fermeture ? number_format($session->montant_fermeture, 0, ',', ' ') . ' F' : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($session->estOuverte())
                                        <span class="badge bg-success bg-opacity-10 text-success px-3">Ouverte</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3">Fermée</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        @if($session->estOuverte())
                                            <button class="btn btn-sm btn-light-danger btn-icon"
                                                    onclick="fermerSession({{ $session->id }})"
                                                    data-bs-toggle="tooltip"
                                                    title="Fermer la session">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-light-info btn-icon ms-1"
                                                onclick="printSessionReport({{ $session->id }})"
                                                data-bs-toggle="tooltip"
                                                title="Imprimer le rapport">
                                            <i class="fas fa-print"></i>
                                        </button>

                                        <a class="btn btn-sm btn-light-primary btn-icon ms-1"
                                                href="{{route('ventes.sessions.list-ventes', $session->id)}}"
                                                data-bs-toggle="tooltip"
                                                title="Détails">
                                            <i class="fas fa-eye"></i>
                                    </a>

                                        @if(!$session->estOuverte())
                                            <button class="btn btn-sm btn-light-secondary btn-icon ms-1"
                                                    onclick="downloadSessionReport({{ $session->id }})"
                                                    data-bs-toggle="tooltip"
                                                    title="Télécharger le rapport">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted mb-1">Aucune session trouvée</h6>
                                        <p class="text-muted small mb-3">Les sessions de caisse apparaîtront ici</p>
                                        <button type="button"
                                                class="btn btn-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addSessionCaisseModal"
                                                {{ $hasSessionOuverte ? 'disabled' : '' }}>
                                            <i class="fas fa-plus me-2"></i>Ouvrir une session
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
                <div class="card-footer border-0 py-3">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
:root {
    --kadjiv-orange: #FFA500;
    --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
}

/* Numéro de session */
.numero-session {
    font-family: 'Monaco', 'Consolas', monospace;
    color: var(--kadjiv-orange);
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    background-color: var(--kadjiv-orange-light);
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

/* Avatar utilisateur */
.avatar-user {
    width: 40px;
    height: 40px;
    background-color: var(--kadjiv-orange-light);
    color: var(--kadjiv-orange);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
}

/* Table */
.table thead {
    background-color: #f8f9fa;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #555;
}

/* Badges */
.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    border-radius: 30px;
}

.badge.bg-opacity-10 {
    border: 1px solid currentColor;
}

/* Boutons d'action */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

.btn-light-primary {
    color: var(--kadjiv-orange);
    background-color: var(--kadjiv-orange-light);
}

.btn-light-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.btn-light-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.btn-light-secondary {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

/* Hover effects */
.btn-icon i {
    transition: transform 0.2s ease;
}

.btn-icon:hover i {
    transform: scale(1.1);
}

/* État vide */
.empty-state {
    text-align: center;
    padding: 3rem;
}

.empty-state i {
    color: #dee2e6;
    margin-bottom: 1rem;
}

/* Card */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
}

/* Bouton désactivé */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
