{{-- list-reglements.blade.php --}}
<div class="row g-3">
    {{-- Filtres --}}

    {{-- Table des règlements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="reglementsTable">
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
                                <span class="badge bg-dark">{{ $reglement->reference_preuve }}</span>
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
</div>

@push("scripts")
<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'asc']
        ],
        "pageLength": 15,
        language: {
            "emptyTable": "Aucune donnée disponible dans le tableau",
            "lengthMenu": "Afficher _MENU_ éléments",
            "loadingRecords": "Chargement...",
            "processing": "Traitement...",
            "zeroRecords": "Aucun élément correspondant trouvé",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "previous": "Précédent",
                "next": "Suiv"
            },
            "aria": {
                "sortAscending": ": activer pour trier la colonne par ordre croissant",
                "sortDescending": ": activer pour trier la colonne par ordre décroissant"
            },
            "select": {
                "rows": {
                    "_": "%d lignes sélectionnées",
                    "1": "1 ligne sélectionnée"
                },
                "cells": {
                    "1": "1 cellule sélectionnée",
                    "_": "%d cellules sélectionnées"
                },
                "columns": {
                    "1": "1 colonne sélectionnée",
                    "_": "%d colonnes sélectionnées"
                }
            },
            "autoFill": {
                "cancel": "Annuler",
                "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                "fillHorizontal": "Remplir les cellules horizontalement",
                "fillVertical": "Remplir les cellules verticalement"
            },
            "searchBuilder": {
                "conditions": {
                    "date": {
                        "after": "Après le",
                        "before": "Avant le",
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "number": {
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "gt": "Supérieur à",
                        "gte": "Supérieur ou égal à",
                        "lt": "Inférieur à",
                        "lte": "Inférieur ou égal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "string": {
                        "contains": "Contient",
                        "empty": "Vide",
                        "endsWith": "Se termine par",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "startsWith": "Commence par"
                    },
                    "array": {
                        "equals": "Egal à",
                        "empty": "Vide",
                        "contains": "Contient",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "without": "Sans"
                    }
                },
                "add": "Ajouter une condition",
                "button": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "clearAll": "Effacer tout",
                "condition": "Condition",
                "data": "Donnée",
                "deleteTitle": "Supprimer la règle de filtrage",
                "logicAnd": "Et",
                "logicOr": "Ou",
                "title": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "value": "Valeur"
            },
            "searchPanes": {
                "clearMessage": "Effacer tout",
                "count": "{total}",
                "title": "Filtres actifs - %d",
                "collapse": {
                    "0": "Volet de recherche",
                    "_": "Volet de recherche (%d)"
                },
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Pas de volet de recherche",
                "loadMessage": "Chargement du volet de recherche..."
            },
            "buttons": {
                "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                "collection": "Collection",
                "colvis": "Visibilité colonnes",
                "colvisRestore": "Rétablir visibilité",
                "copy": "Copier",
                "copySuccess": {
                    "1": "1 ligne copiée dans le presse-papier",
                    "_": "%ds lignes copiées dans le presse-papier"
                },
                "copyTitle": "Copier dans le presse-papier",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Afficher toutes les lignes",
                    "_": "Afficher %d lignes"
                },
                "pdf": "PDF",
                "print": "Imprimer"
            },
            "decimal": ",",
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
            "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
            "infoThousands": ".",
            "search": "Rechercher:",
            "thousands": ".",
            "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
            "datetime": {
                "previous": "Précédent",
                "next": "Suivant",
                "hours": "Heures",
                "minutes": "Minutes",
                "seconds": "Secondes",
                "unknown": "-",
                "amPm": [
                    "am",
                    "pm"
                ],
                "months": [
                    "Janvier",
                    "Fevrier",
                    "Mars",
                    "Avril",
                    "Mai",
                    "Juin",
                    "Juillet",
                    "Aout",
                    "Septembre",
                    "Octobre",
                    "Novembre",
                    "Decembre"
                ],
                "weekdays": [
                    "Dim",
                    "Lun",
                    "Mar",
                    "Mer",
                    "Jeu",
                    "Ven",
                    "Sam"
                ]
            },
            "editor": {
                "close": "Fermer",
                "create": {
                    "button": "Nouveaux",
                    "title": "Créer une nouvelle entrée",
                    "submit": "Envoyer"
                },
                "edit": {
                    "button": "Editer",
                    "title": "Editer Entrée",
                    "submit": "Modifier"
                },
                "remove": {
                    "button": "Supprimer",
                    "title": "Supprimer",
                    "submit": "Supprimer",
                    "confirm": {
                        "1": "etes-vous sure de vouloir supprimer 1 ligne?",
                        "_": "etes-vous sure de vouloir supprimer %d lignes?"
                    }
                },
                "error": {
                    "system": "Une erreur système s'est produite"
                },
                "multi": {
                    "title": "Valeurs Multiples",
                    "restore": "Rétablir Modification",
                    "noMulti": "Ce champ peut être édité individuellement, mais ne fait pas partie d'un groupe. ",
                    "info": "Les éléments sélectionnés contiennent différentes valeurs pour ce champ. Pour  modifier et "
                }
            }
        },
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
</script>
@endpush