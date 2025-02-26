<div class="modal fade" id="updateFactureProformaModal" aria-labelledby="addFactureProformaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier la facture <strong class="badge bg-dark reference"></strong> </h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour modifier une facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="updateFactureProformaForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section informations générales --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <select id="client_idUpdate" class="form-select _select2" name="client_id" required>
                                                    <!-- LES CLIENTS -->
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le client est requis</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input id="date_pfUpdate" type="date" class="form-control" name="date_pf" required>
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <div class="col-4">
                                            <label class="form-label">Choisir l'article</label>
                                            <select class="form-select form-control test _select2" name="article_id"
                                                id="articleSelectUpdate">
                                                <option value="">Choisir l'article </option>
                                                @foreach ($articles as $article)
                                                <option data-prixVente="{{ $article->prix_special }}"
                                                    value="{{ $article->id }}"> {{ $article->designation }} </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-2">
                                            <label class="form-label">Quantité</label>
                                            <input type="text" name="qte" id="qteUpdate" class="form-control">
                                        </div>

                                        <div class="col-2">
                                            <label class="form-label">Prix unitaire</label>
                                            <input type="text" name="prix" id="prixUpdate" class="form-control">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">Unité</label>
                                            <select class="form-select _select2" name="unite_id" id="uniteSelectUpdate">
                                                @foreach($unites_mesures as $unite)
                                                <option value="{{$unite->id}}">{{$unite->libelle_unite}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="ajouterUpdateArticle">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
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
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="updateArticlesRows">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template pour une nouvelle ligne --}}
<template id="ligneFactureTemplate">
    <tr class="ligne-facture">
        <td>
            <select class="form-select select2-articles" name="lignes[__INDEX__][article_id]" required>
                <option value="">Sélectionner un article</option>
            </select>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control text-end quantite-input" name="lignes[__INDEX__][quantite]"
                    placeholder="0.00" required min="0.01" step="0.01">
                <select class="form-select unite-select" name="lignes[__INDEX__][unite_vente_id]" hidden required>
                    {{-- <option value="">Unité</option> --}}
                </select>
            </div>
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td>
            <input type="number"
                class="form-control text-end select2-tarifs"
                name="lignes[__INDEX__][tarification_id]"
                placeholder="0.00"
                required
                min="0.01"
                step="0.01">
            <div class="invalid-feedback">Le prix est requis</div>
        </td>
        <td>
            <input type="number" class="form-control text-end remise-input" name="lignes[__INDEX__][taux_remise]"
                placeholder="0.00" min="0" max="100" step="0.01">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">FCFA</span>
                <input type="text" class="form-control text-end total-ligne" readonly value="0">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push("scripts")
<script>
    $("._select2").select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: updateFactureProformaModal,
    })
</script>

<!-- SHOW MODAL -->
<script>
    var apiUrl = "{{ config('app.url_ajax') }}";

    $(document).ready(function() {
        // Fonction principale pour afficher la facture

        $(".showUpdateFacture").click(function(e) {
            $("#factureId").val()
            e.preventDefault()
            console.log($("#factureId").val())
            // console.log($(".showUpdateFacture").data("facture"))
            // console.log($(this).proforma)
            // let element = document.getElementById('showUpdateFacture')
            // let proforma = element.getAttribute("data-proforma")

            // console.log(proforma)
            // 
            // showUpdateFacture(proforma)
        })

        function showUpdateFacture(facture) {
            alert("Christ ....")
            let id = facture.id
            let factureClient = facture
            console.log(factureClient)
            // Afficher l'animation de chargement
            Swal.fire({
                title: 'Chargement...',
                html: 'Récupération des clients',
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
                    updateUpdateModalContent(response);
                },
                error: function(xhr) {
                    Swal.close();
                    showError('Erreur de communication avec le serveur');
                    console.error('Erreur AJAX:', xhr);
                }
            });

            $("#client_idUpdate").empty()

            // GET ALL CLIENTS
            $.ajax({
                url: `${apiUrl}/vente/clients`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Fermer le loader
                    Swal.close();

                    let clients = response

                    let options = `<option value="">Sélectionner un client</option>`
                    clients.forEach(client => {
                        let opt = ''
                        if (client.id == factureClient.id) {
                            opt = `
                            <option value='${client.id}'
                                data-taux-aib='${client.taux_aib}'
                                selected
                                >
                                ${client.raison_sociale}
                            </option>
                        `
                        } else {
                            opt = `
                            <option value='${client.id}'
                                data-taux-aib='${client.taux_aib}'
                                >
                                ${client.raison_sociale}
                            </option>
                        `
                        }
                        // 
                        options += opt
                    });
                    console.log(options)
                    $("#client_idUpdate").append(options)
                },
                error: function(xhr) {
                    Swal.close();
                    showError('Erreur de communication avec le serveur');
                    console.error('Erreur AJAX:', xhr);
                }
            });

            // initialisation de l'url du formulaire
            // document.getElementById("#updateFactureProformaForm").setAttribute("action", apiUrl + `/vente/factures/proforma/${id}/edit`)

            // $("#updateFactureProformaForm").attr("action", apiUrl + `/vente/factures/proforma/${id}/edit`)

            // console.log(apiUrl + `/vente/factures/proforma/${id}/edit`)
        }

        // Fonction pour mettre à jour le contenu du modal
        function updateUpdateModalContent(data) {
            const facture = data;
            const date = new Date(facture.date_devis).toISOString().split('T')[0];

            // En-tête modal
            $('.reference').text(facture.reference);
            $("#date_pfUpdate").val(date)

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
                        <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash3"></i> Supprimer</button></td>
                    </tr>
            `;
            });

            $('#updateArticlesRows').append(rows);
            initTooltips();
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

<script>
    var apiUrl = "{{ config('app.url_ajax') }}";

    $(document).ready(function() {
        // Écouteur d'événement pour le bouton Ajouter
        $('#ajouterUpdateArticle').click(function() {
            // Récupérer les valeurs des champs
            var articleId = $('#articleSelectUpdate').val();
            var articleNom = $('#articleSelectUpdate option:selected').text();
            var uniteId = $('#uniteSelectUpdate option:selected').val();
            var uniteNom = $('#uniteSelectUpdate option:selected').text();
            var prix = $('#prixUpdate').val();
            var quantite = $('#qteUpdate').val();
            var total = prix * quantite;
            var prixMin = $('#articleSelectUpdate option:selected').attr('data-prixVente');
            $('#prixUpdate').attr('min', prixMin);
            // Ajouter une nouvelle ligne au tableau
            var newRow = `
            <tr>
                <td>${articleNom}<input type="hidden" required name="articles[]" value="${articleId}"></td>
                <td>${quantite} <input type="hidden" required name="qte_cdes[]" value="${quantite}"</td>
                <td>${prix} <input type="hidden" required name="prixUnits[]" value="${prix}"</td>
                <td>${uniteNom} <input type="hidden" required name="unites[]" value="${uniteId}"</td>
                <td>${total} <input type="hidden" required name="montants[]" value="${total}"</td>
                <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash3"></i> Supprimer</button></td>
            </tr>`;

            $('#updateArticlesRows').append(newRow);
            // calculateTotal();

            // Effacer les champs après l'ajout
            $('#articleSelectUpdate').val(null).trigger('change');
            $('#uniteSelectUpdate').val('');
            $('#prixUpdate').val('');
            $('#qteUpdate').val('');
        });

        // Écouteur d'événement pour le clic sur le bouton Supprimer
        $('#updateArticlesRows').on('click', '.delete-row', function() {
            $(this).closest('tr').remove();
            calculateTotal();
        });
    });
</script>
@endpush