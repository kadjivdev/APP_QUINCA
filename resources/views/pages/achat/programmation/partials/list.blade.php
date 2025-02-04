<div class="row g-3">
    {{-- Table des programmations --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="programmationsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0 text-nowrap py-3">Date Insertion</th>
                            <th class="border-bottom-0">Date Programmation</th>
                            <th class="border-bottom-0">Point de Vente</th>
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programmations as $programmation)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <div class="d-flex align-items-center">
                                        <span class="code-programmation me-2">{{ $programmation->code }}</span>
                                    </div>
                                </td>
                                <td>{{ Carbon\Carbon::parse($programmation->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $programmation->date_programmation->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-point-vente me-2">
                                            {{ substr($programmation->pointVente->nom_pv, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $programmation->pointVente->nom_pv }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-fournisseur me-2">
                                            {{ substr($programmation->fournisseur->raison_sociale, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $programmation->fournisseur->raison_sociale }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($programmation->rejected_by)
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3"><i
                                                class="fas fa-minus-circle"></i> Rejetée</span>
                                    @elseif ($programmation->validated_at)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3"><i
                                                class="fas fa-check-circle"></i> Validée</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3"><i
                                                class="fas fa-hourglass-half"></i> En attente</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light-primary btn-icon"
                                            data-id="{{ $programmation->id }}"
                                            onclick="showProgrammation({{ $programmation->id }})"
                                            data-bs-toggle="tooltip" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>


                                        @if (!$programmation->validated_at && !$programmation->rejected_at)
                                            <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                                onclick="editProgrammation({{ $programmation->id }})"
                                                data-bs-toggle="tooltip" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                                onclick="validateProgrammation({{ $programmation->id }})"
                                                data-bs-toggle="tooltip" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                                onclick="initRejetProgramation({{ $programmation->id }})"
                                                data-bs-toggle="tooltip" title="Rejeter">
                                                <i class="fas fa-ban"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                                onclick="deleteProgrammation({{ $programmation->id }})"
                                                data-bs-toggle="tooltip" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        <div class="btn-group ms-1">
                                            <button class="btn btn-sm btn-light-secondary btn-icon"
                                                data-bs-toggle="dropdown">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" target="blank" href="#">
                                                        <i class="fas fa-file-pdf me-2"></i>Imprimer
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="exportExcel({{ $programmation->id }})">
                                                        <i class="fas fa-file-excel me-2"></i>Exporter Excel
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted mb-1">Aucune programmation trouvée</h6>
                                        <p class="text-muted small mb-3">Les programmations que vous créez apparaîtront
                                            ici</p>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#addProgrammationModal">
                                            <i class="fas fa-plus me-2"></i>Créer une programmation
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($programmations->hasPages())
                <div class="card-footer bg-white border-0 pt-0">
                    {{ $programmations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Code de la programmation */
    .code-programmation {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Avatar point de vente et fournisseur */
    .avatar-point-vente,
    .avatar-fournisseur {
        width: 35px;
        height: 35px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
    }

    .avatar-fournisseur {
        background-color: rgba(var(--bs-info-rgb), 0.1);
        color: var(--bs-info);
    }

    /* Styles pour les badges */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 0.5em 1em;
    }

    /* Styles pour les boutons */
    .btn-light-primary,
    .btn-light-warning,
    .btn-light-success,
    .btn-light-danger,
    .btn-light-secondary {
        border: none;
        transition: all 0.2s ease;
    }

    .btn-light-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .btn-light-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
    }

    .btn-light-success {
        background-color: rgba(var(--bs-success-rgb), 0.1);
        color: var(--bs-success);
    }

    .btn-light-danger {
        background-color: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
    }

    .btn-light-secondary {
        background-color: rgba(var(--bs-secondary-rgb), 0.1);
        color: var(--bs-secondary);
    }

    /* Hover effects */
    .btn-light-primary:hover {
        background-color: var(--bs-primary);
        color: white;
    }

    .btn-light-warning:hover {
        background-color: var(--bs-warning);
        color: white;
    }

    .btn-light-success:hover {
        background-color: var(--bs-success);
        color: white;
    }

    .btn-light-danger:hover {
        background-color: var(--bs-danger);
        color: white;
    }

    .btn-light-secondary:hover {
        background-color: var(--bs-secondary);
        color: white;
    }

    /* Button icon styles */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    /* Table styles */
    .table> :not(caption)>*>* {
        padding: 1rem 1rem;
        border-bottom-color: rgba(0, 0, 0, 0.05);
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            flex-wrap: wrap;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
        }

        .code-programmation {
            font-size: 0.75rem;
        }

        .avatar-point-vente,
        .avatar-fournisseur {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
    }
</style>


@push('scripts')
    <script>
        function rejeteProgrammation(id) {
            console.log('Validation appelée pour ID:', id); // Log de débogage

            Swal.fire({
                title: 'Confirmer le rejet',
                text: 'Êtes-vous sûr de vouloir valider cette programmation ? Cette action est irréversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, valider',
                cancelButtonText: 'Annuler',
                reverseButtons: true
            }).then((result) => {
                console.log('Réponse SweetAlert:', result); // Log de débogage

                if (result.isConfirmed) {
                    // Afficher un loader pendant la validation
                    const loadingAlert = Swal.fire({
                        title: 'Validation en cours...',
                        text: 'Veuillez patienter...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Appel AJAX avec Axios
                    axios.post(`/achat/programmations/${id}/validate`)
                        .then(response => {
                            console.log('Réponse serveur:', response); // Log de débogage

                            if (response.data.success) {
                                loadingAlert.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Validation réussie !',
                                    text: 'La programmation a été validée avec succès.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(response.data.message || 'Erreur lors de la validation');
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error); // Log de débogage

                            loadingAlert.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: error.response?.data?.message ||
                                    'Une erreur est survenue lors de la validation.',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }
    </script>

    <script>
        // Fonction principale pour afficher une programmation
        function showProgrammation(id) {
            // Réinitialiser le modal
            resetModal();

            // Afficher l'indicateur de chargement
            Swal.fire({
                title: 'Chargement...',
                text: 'Veuillez patienter...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Charger les données
            $.ajax({
                url: `/achat/programmations/${id}/edit`,
                method: 'GET',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        const programmation = response.data;
                        fillProgrammationDetails(programmation);
                        $('#showProgrammationModal').modal('show');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des données'
                    });
                }
            });
        }

        // Fonction pour remplir les détails de la programmation
        function fillProgrammationDetails(programmation) {
            // Informations générales
            $('#programmationCode').text(`Code : ${programmation.code}`);
            $('#dateProgrammation').text(moment(programmation.date_programmation).format('DD/MM/YYYY'));
            $('#pointVente').text(programmation.point_vente.nom_pv);
            $('#fournisseur').text(programmation.fournisseur.raison_sociale);

            // Statut
            const statutBadge = programmation.validated_at ?
                '<span class="badge bg-success">Validée</span>' :
                '<span class="badge bg-warning">En attente</span>';
            $('#statut').html(statutBadge);

            // Commentaire
            $('#commentaire').text(programmation.commentaire || 'Aucun commentaire');

            // Vider et remplir le tableau des lignes
            $('#lignesDetails').empty();
            programmation.lignes.forEach(ligne => {
                const row = `
                    <tr>
                        <td>${ligne.article.code_article}</td>
                        <td>${ligne.article.designation}</td>
                        <td class="text-end">${parseFloat(ligne.quantite).toLocaleString('fr-FR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}</td>
                        <td>${ligne.unite_mesure.libelle_unite}</td>
                        <td>${ligne.observation || ''}</td>
                    </tr>
                `;
                $('#lignesDetails').append(row);
            });
        }

        // Fonction pour réinitialiser le modal
        function resetModal() {
            $('#lignesDetails').empty();
            $('#commentaire').text('');
            $('#programmationCode').text('Code : ');
            $('#dateProgrammation').text('');
            $('#pointVente').text('');
            $('#fournisseur').text('');
            $('#statut').html('');
        }

        // Fonctions d'export
        function printProgrammation() {
            window.print();
        }

        function exportPDF() {
            Toast.fire({
                icon: 'info',
                title: 'Export PDF en cours de développement'
            });
        }

        function exportExcel() {
            Toast.fire({
                icon: 'info',
                title: 'Export Excel en cours de développement'
            });
        }

        // Initialisation des tooltips au chargement de la page
        $(document).ready(function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
