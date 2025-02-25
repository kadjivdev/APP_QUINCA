<div class="modal fade" id="showFactureProformaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Détails Proforma du <strong class="reference badge bg-dark"></strong> </h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    {{-- Section articles --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="editableTable" class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Article</th>
                                                <th>Quantité</th>
                                                <th>Prix Unit</th>
                                                <th>Unité de mesure</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody id="articlesRows">
                                            <!-- Les lignes seront ajoutées ici -->

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push("scripts")
<!-- SHOW MODAL -->
<script>
    var apiUrl = "{{ config('app.url_ajax') }}";

    $(document).ready(function() {
        // Fonction principale pour afficher la facture

        $("#showDetail").click(function(e) {
            e.preventDefault()
            let element = document.getElementById('showDetail')
            let elementId = element.getAttribute("data-proformaid")

            // 
            showFacture(elementId)
        })

        function showFacture(id) {
            // Vérification de l'ID
            if (!id) {
                showError('ID de facture invalide');
                return;
            }

            // Afficher l'animation de chargement
            Swal.fire({
                title: 'Chargement...',
                html: 'Récupération des détails de la facture',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Faire la requête AJAX
            $.ajax({
                url: `${apiUrl}/vente/factures/proforma/${id}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Fermer le loader
                    Swal.close();

                    // Mettre à jour le contenu du modal
                    updateModalContent(response);
                },
                error: function(xhr) {
                    Swal.close();
                    showError('Erreur de communication avec le serveur');
                    console.error('Erreur AJAX:', xhr);
                }
            });
        }

        // Fonction pour mettre à jour le contenu du modal
        function updateModalContent(data) {
            const facture = data;

            // En-tête modal
            $('.reference').text(facture.reference);

            let articles = facture.articles
            let detail = facture.detail

            // Contenu HTML
            let rows = ""
            articles.forEach(element => {
                rows += `
                    <tr>
                        <td>${element.code_article}</td>
                        <td>${detail.qte_cmde}</td>
                        <td>${detail.prix_unit}</td>
                        <td>${detail.mesureunit.libelle_unite}</td>
                        <td>${detail.qte_cmde*detail.prix_unit}</td>
                    </tr>
            `;
            });

            $('#articlesRows').append(rows);
            initTooltips();
        }

        // Fonction pour générer le badge de statut
        function getStatusBadge(statut) {
            const statusClasses = {
                'brouillon': 'bg-warning text-warning',
                'validee': 'bg-success text-success',
                'partiellement_payee': 'bg-info text-info',
                'payee': 'bg-success text-success',
                'annulee': 'bg-danger text-danger'
            };

            const statusLabels = {
                'brouillon': 'Brouillon',
                'validee': 'Validée',
                'partiellement_payee': 'Partiellement payée',
                'payee': 'Payée',
                'annulee': 'Annulée'
            };

            const className = statusClasses[statut] || 'bg-secondary';
            const label = statusLabels[statut] || 'Indéfini';

            return `<span class="badge ${className} bg-opacity-10 ">${label}</span>`;
        }

        // Fonction pour générer les lignes de facture
        function generateLignesFacture(lignes) {
            return lignes.map(ligne => `
            <tr>
                <td>${ligne.article.designation}</td>
                <td class="text-end">${formatMontant(ligne.prix_unitaire_ht)} FCFA</td>
                <td class="text-center">${formatQuantite(ligne.quantite)} ${ligne.unite_vente.libelle_unite}</td>
                <td class="text-end">${formatTaux(ligne.taux_remise)}%</td>
                <td class="text-end">${formatMontant(ligne.montant_ht)} FCFA</td>
            </tr>
        `).join('');
        }

        // Fonction pour afficher les erreurs
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Initialisation des tooltips
        function initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endpush