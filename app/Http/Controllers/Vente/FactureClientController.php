<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Tarification, Article};
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Vente\{FactureClient, LigneFacture, SessionCaisse, ReglementClient};
use App\Models\Parametre\Societe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Http\JsonResponse;

class FactureClientController extends Controller
{
    /**
     * Affiche la liste des factures
     */

    public function index()
    {
        try {
            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $configuration = Societe::first();
            $tauxTva = $configuration ? $configuration->taux_tva : 18;

            // Chargement des factures avec les relations nécessaires
            $factures = FactureClient::with(['client'])
                ->select([
                    'id',
                    'numero',
                    'date_facture',
                    'date_echeance',
                    'client_id',
                    'statut',
                    'montant_ht',
                    'montant_ttc',
                    'montant_regle',
                    'session_caisse_id',
                    'created_by',
                    'validated_by',
                    'encaissed_at'
                ])
                ->orderBy('date_facture', 'desc')
                ->paginate(10);

            // Ajouter des attributs calculés pour chaque facture
            $factures->getCollection()->transform(function ($facture) {
                // Calcul du reste à payer
                $facture->reste_a_payer = $facture->montant_ttc - $facture->montant_regle;

                // Détermination du vrai statut basé sur le paiement
                if ($facture->statut === 'brouillon') {
                    $facture->statut_reel = 'brouillon';
                } elseif ($facture->statut === 'validee') {
                    if ($facture->montant_regle == 0) {
                        $facture->statut_reel = 'validee';
                    } elseif ($facture->montant_regle < $facture->montant_ttc) {
                        $facture->statut_reel = 'partiellement_payee';
                    } elseif ($facture->montant_regle >= $facture->montant_ttc) {
                        $facture->statut_reel = 'payee';
                    }
                }

                // Vérifier si la facture est en retard
                $facture->is_overdue = $facture->statut !== 'payee'
                    && Carbon::now()->startOfDay()->gt($facture->date_echeance);

                return $facture;
            });

            $facturesResteAPayer = $factures->getCollection()->filter(function ($facture) {
                return $facture->reste_a_payer > 0;
            });
            $montantResteAPyer = $facturesResteAPayer->sum('montant_ttc');

            // Calculer le montant total des factures du mois en cours
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $facturesDuMois = $factures->getCollection()->filter(function ($facture) use ($currentMonth, $currentYear) {
                return Carbon::parse($facture->date_facture)->month == $currentMonth &&
                       Carbon::parse($facture->date_facture)->year == $currentYear;
            });

            $montantFactureMois = $facturesDuMois->sum('montant_ttc');

            // Calculer le total encaissé et le nombre de factures encaissées
            $facturesEncaissees = $facturesDuMois->filter(function ($facture) {
                return !is_null($facture->encaissed_at);
            });

            // dd($facturesDuMois);
            
            $totalEncaisse = $facturesEncaissees->sum('montant_ttc');
            $nombreEncaisse = $facturesEncaissees->count();

            $statsFactures = [
                'total_mois' => $montantFactureMois,
                'total_encaisse' => $totalEncaisse,
                'nombre_encaisse' => $nombreEncaisse,
                'montant_en_attente' => $montantResteAPyer,
                'factures_en_attente' => $facturesResteAPayer,
            ];

            // dd($statsFactures);

            Log::info('Liste des factures chargée avec succès', [
                'nombre_factures' => $factures->total()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'factures' => $factures
                    ]
                ]);
            }

            // Charger la liste des clients pour le filtre
            $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);

            return view('pages.ventes.facture.index', compact('factures', 'clients', 'date', 'tauxTva', 'statsFactures'));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des factures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors du chargement des factures'
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue lors du chargement des factures');
        }
    }



    // public function store(Request $request)
    // {
    //     {
    //         try {
    //             Log::info('Début de création d\'une nouvelle facture avec règlement initial', [
    //                 'request' => $request->all()
    //             ]);

    //             // Vérifier la session de caisse
    //             $sessionCaisse = SessionCaisse::ouverte()
    //                 ->where('utilisateur_id', auth()->id())
    //                 ->first();

    //             if (!$sessionCaisse) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => 'Vous devez avoir une session de caisse ouverte pour créer une facture.'
    //                 ], 422);
    //             }

    //             // Configuration et client
    //             $client = Client::findOrFail($request->client_id);
    //             $configuration = Societe::firstOrFail();

    //             // Validation initiale
    //             $validator = Validator::make($request->all(), [
    //                 'date_facture' => 'required|date',
    //                 'client_id' => 'required|exists:clients,id',
    //                 'date_echeance' => 'date',
    //                 'montant_regle' => 'required|numeric|min:0',
    //                 'moyen_reglement' => 'required', 'string',
    //                 'lignes' => 'required|array|min:1',
    //                 'observations' => 'nullable|string'
    //             ]);

    //             if ($validator->fails()) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => 'Données invalides',
    //                     'errors' => $validator->errors()
    //                 ], 422);
    //             }

    //             // Calcul préalable du montant de la nouvelle facture
    //             $totalHT = 0;
    //             $totalRemise = 0;
    //             $totalTVA = 0;
    //             $totalAIB = 0;

    //             foreach ($request->lignes as $ligne) {
    //                 $montantHT = $ligne['quantite'] * $ligne['tarification_id'];
    //                 $montantRemise = isset($ligne['taux_remise']) ? ($montantHT * $ligne['taux_remise'] / 100) : 0;
    //                 $montantHTApresRemise = $montantHT - $montantRemise;

    //                 $totalHT += $montantHT;
    //                 $totalRemise += $montantRemise;
    //                 $totalTVA += $montantHTApresRemise * ($configuration->taux_tva / 100);
    //                 $totalAIB += $montantHTApresRemise * ($client->taux_aib / 100);
    //             }

    //             $montantTTC = ($totalHT - $totalRemise) + $totalTVA + $totalAIB;
    //             $montantRegle = $request->montant_regle ?? 0;

    //             // Vérification selon la catégorie du client
    //             if ($client->categorie === 'comptoir') {
    //                 if ($montantRegle < $montantTTC) {
    //                     return response()->json([
    //                         'status' => 'error',
    //                         'message' => 'Les clients de type Comptoir doivent payer la totalité de leur facture.'
    //                     ], 422);
    //                 }
    //             } else {
    //                 // Pour les autres types de clients, vérification du crédit
    //                 $totalImpayes = $client->facturesClient()
    //                     ->whereIn('statut', ['validee', 'brouillon'])
    //                     ->get()
    //                     ->sum(function ($facture) {
    //                         return $facture->montant_ttc - $facture->montant_regle;
    //                     });

    //                 $nouveauCredit = $totalImpayes + ($montantTTC - $montantRegle);

    //                 if ($nouveauCredit > $client->plafond_credit) {
    //                     $depassement = $nouveauCredit - $client->plafond_credit;
    //                     return response()->json([
    //                         'status' => 'error',
    //                         'message' => "Ce client a atteint son plafond de crédit.\n" .
    //                                    "Plafond autorisé: " . number_format($client->plafond_credit, 0, ',', ' ') . " FCFA\n" .
    //                                    "Total impayés actuels: " . number_format($totalImpayes, 0, ',', ' ') . " FCFA\n" .
    //                                    "Montant de la nouvelle facture: " . number_format($montantTTC, 0, ',', ' ') . " FCFA\n" .
    //                                    "Montant réglé: " . number_format($montantRegle, 0, ',', ' ') . " FCFA\n" .
    //                                    "Dépassement: " . number_format($depassement, 0, ',', ' ') . " FCFA"
    //                     ], 422);
    //                 }
    //             }

    //             DB::beginTransaction();

    //             try {
    //                 // Données de base de la facture
    //                 $factureData = [
    //                     'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
    //                     'client_id' => $request->client_id,
    //                     'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
    //                     'session_caisse_id' => $sessionCaisse->id,
    //                     'created_by' => auth()->id(),
    //                     'observations' => $request->observations,
    //                     'montant_ht' => 0,
    //                     'montant_remise' => 0,
    //                     'montant_tva' => 0,
    //                     'montant_aib' => 0,
    //                     'montant_ttc' => 0,
    //                     'taux_tva' => $configuration->taux_tva,
    //                     'taux_aib' => $client->taux_aib,
    //                     'statut' => 'brouillon',
    //                 ];

    //                 // Ajouter conditions_reglement seulement s'il est présent
    //                 if ($request->has('conditions_reglement')) {
    //                     $factureData['conditions_reglement'] = $request->conditions_reglement;
    //                 }

    //                 // Création de la facture
    //                 $facture = new FactureClient();
    //                 $facture->fill($factureData);
    //                 $facture->save();

    //                 // Création des lignes et calcul des totaux
    //                 foreach ($request->lignes as $ligne) {
    //                     $tarification = $ligne['tarification_id'];
    //                     Log::info('********************', [
    //                         'request' => $ligne['tarification_id']
    //                     ]);
    //                     $ligneFactureData = [
    //                         'article_id' => $ligne['article_id'],
    //                         // 'tarification_id' => $ligne['tarification_id'],
    //                         'unite_vente_id' => $ligne['unite_vente_id'],
    //                         'quantite' => $ligne['quantite'],
    //                         'prix_unitaire_ht' => $ligne['tarification_id'],
    //                         'taux_tva' => $configuration->taux_tva,
    //                         'taux_aib' => $client->taux_aib
    //                     ];

    //                     // Ajouter taux_remise seulement s'il est présent
    //                     if (isset($ligne['taux_remise'])) {
    //                         $ligneFactureData['taux_remise'] = $ligne['taux_remise'];
    //                     }

    //                     $ligneFacture = new LigneFacture();
    //                     $ligneFacture->fill($ligneFactureData);
    //                     $facture->lignes()->save($ligneFacture);

    //                     $totalHT += $ligneFacture->montant_ht;
    //                     $totalRemise += $ligneFacture->montant_remise;
    //                     $totalTVA += $ligneFacture->montant_tva;
    //                     $totalAIB += $ligneFacture->montant_aib;
    //                 }

    //                 // Calcul des montants finaux
    //                 $montantHTApresRemise = $totalHT - $totalRemise;
    //                 $montantTTC = $montantHTApresRemise + $totalTVA + $totalAIB;

    //                 // Vérification du montant réglé
    //                 if ($request->montant_regle > $montantTTC) {
    //                     throw new Exception("Le montant réglé ({$request->montant_regle}) ne peut pas être supérieur au montant total de la facture ({$montantTTC})");
    //                 }

    //                 // Mise à jour des totaux de la facture
    //                 $facture->update([
    //                     'montant_ht' => $totalHT,
    //                     'montant_remise' => $totalRemise,
    //                     'montant_ht_apres_remise' => $montantHTApresRemise,
    //                     'montant_tva' => $totalTVA,
    //                     'montant_aib' => $totalAIB,
    //                     'montant_ttc' => $montantTTC,
    //                     'montant_regle' => $request->montant_regle
    //                 ]);

    //                 // Recharger la facture
    //                 $facture->refresh();

    //                 // Création du règlement si montant > 0
    //                 if ($request->montant_regle > 0) {
    //                     $reglementData = [
    //                         'facture_client_id' => $facture->id,
    //                         'date_reglement' => Carbon::parse($request->date_facture),
    //                         'type_reglement' => $request->moyen_reglement,
    //                         'montant' => $request->montant_regle,
    //                         'created_by' => auth()->id(),
    //                         'statut' => 'brouillon',
    //                         'notes' => 'Règlement initial lors de la création de la facture',
    //                         'session_caisse_id' => $sessionCaisse->id,
    //                         'banque' => $request->banque,
    //                         'reference_preuve' => $request->reference_preuve,
    //                     ];

    //                     $reglement = new ReglementClient($reglementData);
    //                     $reglement->facture()->associate($facture);

    //                     if (!$reglement->verifierMontant()) {
    //                         throw new Exception('Le montant du règlement est invalide par rapport au reste à payer de la facture');
    //                     }

    //                     $reglement->save();

    //                     // Valider la facture si totalement réglée
    //                     if ($request->montant_regle >= $montantTTC) {
    //                         $facture->update([
    //                             'statut' => 'validee',
    //                             'date_validation' => now(),
    //                             'validated_by' => auth()->id()
    //                         ]);
    //                     }
    //                 }

    //                 // Mise à jour session caisse
    //                 $sessionCaisse->mettreAJourTotaux();

    //                 DB::commit();

    //                 return response()->json([
    //                     'status' => 'success',
    //                     'message' => 'Facture créée avec succès',
    //                     'data' => [
    //                         'facture' => $facture->load([
    //                             'client',
    //                             'lignes.article',
    //                             'lignes.uniteVente',
    //                             'lignes.tarification',
    //                             'sessionCaisse',
    //                             'createdBy',
    //                             'reglements'
    //                         ])
    //                     ]
    //                 ]);

    //             } catch (Exception $e) {
    //                 DB::rollBack();
    //                 throw $e;
    //             }
    //         } catch (Exception $e) {
    //             Log::error('Erreur lors de la création de la facture', [
    //                 'error' => $e->getMessage(),
    //                 'trace' => $e->getTraceAsString()
    //             ]);

    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Une erreur est survenue lors de la création de la facture: ' . $e->getMessage()
    //             ], 500);
    //         }
    //     }
    // }

    public function store(Request $request)
    {
        try {
            Log::info('Début création facture', ['request' => $request->all()]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $client = Client::findOrFail($request->client_id);
            $configuration = Societe::firstOrFail();

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',
                'lignes' => 'required|array|min:1',
                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Création de la facture
                $facture = new FactureClient();
                $facture->fill([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                ]);
                $facture->save();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Création des lignes
                foreach ($request->lignes as $ligne) {
                    $ligneFacture = new LigneFacture([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib
                    ]);

                    $facture->lignes()->save($ligneFacture);

                    $totalHT += $ligneFacture->montant_ht;
                    $totalRemise += $ligneFacture->montant_remise;
                    if ($request->type_facture === 'normaliser') {
                        $totalTVA += $ligneFacture->montant_tva;
                        $totalAIB += $ligneFacture->montant_aib;
                    }
                }

                $montantHTApresRemise = $totalHT - $totalRemise;
                $montantTTC = $montantHTApresRemise;
                if ($request->type_facture === 'normaliser') {
                    $montantTTC += $totalTVA + $totalAIB;
                }

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                    'montant_regle' => $request->montant_regle
                ]);

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture créée avec succès',
                    'data' => ['facture' => $facture->load([
                        'client', 'lignes.article', 'lignes.uniteVente',
                        'sessionCaisse', 'createdBy', 'reglements'
                    ])]
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur création facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur création facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $facture = FactureClient::findOrFail($id);
            $client = Client::findOrFail($request->client_id);
            $configuration = Societe::firstOrFail();

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',
                'lignes' => 'required|array|min:1',
                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Mise à jour de la facture
                $facture->update([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
                    'updated_by' => auth()->id(),
                    'observations' => $request->observations,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                    'montant_regle' => $request->montant_regle
                ]);

                // Suppression des anciens règlements
                $facture->reglements()->delete();

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                // Réinitialisation des totaux et suppression des anciennes lignes
                $facture->lignes()->delete();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Mise à jour des lignes
                foreach ($request->lignes as $ligne) {
                    $ligneFacture = new LigneFacture([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib
                    ]);

                    $facture->lignes()->save($ligneFacture);

                    $totalHT += $ligneFacture->montant_ht;
                    $totalRemise += $ligneFacture->montant_remise;
                    if ($request->type_facture === 'normaliser') {
                        $totalTVA += $ligneFacture->montant_tva;
                        $totalAIB += $ligneFacture->montant_aib;
                    }
                }

                $montantHTApresRemise = $totalHT - $totalRemise;
                $montantTTC = $montantHTApresRemise;
                if ($request->type_facture === 'normaliser') {
                    $montantTTC += $totalTVA + $totalAIB;
                }

                // Mise à jour des totaux de la facture
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                ]);

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                        'client', 'lignes.article', 'lignes.uniteVente',
                        'sessionCaisse', 'reglements'
                    ])]
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur mise à jour facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur mise à jour facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchArticles(Request $request)
    {
        $search = $request->get('q');

        $pv = PointDeVente::find(auth()->user()->point_de_vente_id);
        $depot = Depot::find($pv->id);

        $articles = Article::query()
                ->where(function ($query) use ($search) {
                    $query->where('code_article', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%");
                })
                ->where('statut', 'actif')
                ->with(['stocks' => function ($query) use ($depot) {
                    $query->where('depot_id', $depot->id);
                }])
                ->select(['id', 'code_article', 'designation'])
                ->limit(10)
                ->get();  // Ceci retourne maintenant une Collection

        return response()->json([
            'results' => $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'text' => $article->designation,
                    'code_article' => $article->code_article,
                    'stock' => $article?->stocks[0]?->quantite_reelle ?? 0
                ];
            })
        ]);
    }

    public function getTarifs(Request $request, $articleId)
    {
        try {
            $article = Article::with(['tarifications.typeTarif'])
                ->findOrFail($articleId);

            $tarifs = $article->tarifications
                ->where('statut', true)
                ->map(function ($tarif) {
                    return [
                        'id' => $tarif->id,
                        'text' => sprintf(
                            '%s FCFA - %s',
                            number_format($tarif->prix, 2),
                            $tarif->typeTarif->libelle_type_tarif ?? ''
                        ),
                        'prix' => $tarif->prix
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tarifs' => $tarifs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des tarifs'
            ], 500);
        }
    }

    // public function getUnites(Request $request, $articleId)
    // {
    //     try {
    //         Log::info('Début récupération des unités', ['article_id' => $articleId]);

    //         // Récupérer l'article avec sa famille
    //         $article = Article::with('famille.uniteBase')->findOrFail($articleId);

    //         if (!$article->famille) {
    //             throw new \Exception("Cet article n'a pas de famille associée");
    //         }

    //         $familleId = $article->famille_id;

    //         // 1. Obtenir l'unité de base de la famille si elle existe
    //         $unites = collect();
    //         if ($article->famille->uniteBase) {
    //             $unites->push([
    //                 'id' => $article->famille->uniteBase->id,
    //                 'text' => $article->famille->uniteBase->libelle_unite
    //             ]);
    //         }

    //         // 2. Obtenir toutes les unités ayant des conversions pour cette famille
    //         $unitesConversion = ConversionUnite::where('famille_id', $familleId)
    //             ->where('statut', true)
    //             ->with(['uniteSource', 'uniteDest'])
    //             ->get();

    //         // Ajouter les unités source
    //         $unitesConversion->pluck('uniteSource')
    //             ->where('statut', true)
    //             ->unique('id')
    //             ->each(function ($unite) use (&$unites) {
    //                 if (!$unites->contains('id', $unite->id)) {
    //                     $unites->push([
    //                         'id' => $unite->id,
    //                         'text' => $unite->libelle_unite
    //                     ]);
    //                 }
    //             });

    //         // Ajouter les unités destination
    //         $unitesConversion->pluck('uniteDest')
    //             ->where('statut', true)
    //             ->unique('id')
    //             ->each(function ($unite) use (&$unites) {
    //                 if (!$unites->contains('id', $unite->id)) {
    //                     $unites->push([
    //                         'id' => $unite->id,
    //                         'text' => $unite->libelle_unite
    //                     ]);
    //                 }
    //             });

    //         Log::info('Unités récupérées avec succès', [
    //             'article_id' => $articleId,
    //             'nombre_unites' => $unites->count(),
    //             'unites' => $unites->toArray()
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => [
    //                 'unites' => $unites->values()->all()
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Erreur lors de la récupération des unités', [
    //             'article_id' => $articleId,
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Erreur lors de la récupération des unités: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getUnites(Request $request, $articleId)
    {
        try {
            Log::info('Début récupération des unités', ['article_id' => $articleId]);

            // Récupérer l'article avec son unité de mesure
            $article = Article::with('uniteMesure')->findOrFail($articleId);

            $unites = collect();

            // 1. Ajouter l'unité de base de l'article si elle existe
            if ($article->uniteMesure) {
                $unites->push([
                    'id' => $article->uniteMesure->id,
                    'text' => $article->uniteMesure->libelle_unite
                ]);
            }

            // 2. Obtenir toutes les unités ayant des conversions pour cet article
            $unitesConversion = ConversionUnite::where('article_id', $articleId)
                ->where('statut', true)
                ->with(['uniteSource', 'uniteDest'])
                ->get();

            // Ajouter les unités source actives
            $unitesConversion->pluck('uniteSource')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            // Ajouter les unités destination actives
            $unitesConversion->pluck('uniteDest')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            Log::info('Unités récupérées avec succès', [
                'article_id' => $articleId,
                'nombre_unites' => $unites->count(),
                'unites' => $unites->toArray()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'unites' => $unites->values()->all()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des unités', [
                'article_id' => $articleId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des unités: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            Log::info('Début du chargement des détails de la facture', ['facture_id' => $id]);

            $facture = FactureClient::with([
                'client',
                'lignes.article',
                'lignes.uniteVente',
                'lignes.tarification.typeTarif',
                'sessionCaisse',
                'createdBy'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'facture' => $facture,
                    'dateFacture' => $facture->date_facture->format('d/m/Y'),
                    'dateEcheance' => $facture->date_echeance->format('d/m/Y'),
                    'montantHT' => number_format($facture->montant_ht, 0, ',', ' '),
                    'montantTVA' => number_format($facture->montant_tva, 0, ',', ' '),
                    'montantTTC' => number_format($facture->montant_ttc, 0, ',', ' '),
                    'montantRegle' => number_format($facture->montant_regle, 0, ',', ' '),
                    'montantRestant' => number_format($facture->montant_ttc - $facture->montant_regle, 0, ',', ' '),
                    'tauxTVA' => $facture->taux_tva,
                    'tauxAIB' => $facture->taux_aib
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement des détails de la facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture'
            ], 500);
        }
    }

    public function validateFacture($id)
    {
        try {
            DB::beginTransaction();

            $facture = FactureClient::with(['client', 'lignes.article', 'reglements'])
                ->findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
            }

            $sessionCaisse = SessionCaisse::ouverte()
            ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                throw new Exception('Session de caisse requise');
            }

            $updateData = [
                'date_validation' => now(),
                'validated_by' => auth()->id(),
                'statut' => 'validee'
            ];


            $facture->update($updateData);

            if ($reglement = $facture->reglements->first()) {
                $reglement->update([
                    'date_validation' => now(),
                    'validated_by' => auth()->id(),
                    'statut' => 'validee'
                ]);
            }

            $sessionCaisse->mettreAJourTotaux();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture validée',
                'data' => ['facture' => $facture->fresh(['client', 'createdBy'])]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $facture = FactureClient::findOrFail($id);

            // Vérifier le statut
            if ($facture->statut === 'validee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer une facture validée'
                ], 422);
            }

            // Supprimer les règlements de manière forcée
            $facture->reglements()->forceDelete();

            // Supprimer les lignes
            $facture->lignes()->delete();

            // Supprimer la facture
            $facture->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture et règlements supprimés avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression facture', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
    public function details(FactureClient $facture)
    {
        return response()->json([
            'id' => $facture->id,
            'numero' => $facture->numero,
            'client' => [
                'id' => $facture->client->id,
                'raison_sociale' => $facture->client->raison_sociale
            ],
            'montant_ttc' => $facture->montant_ttc,
            'montant_ttc' => $facture->montant_ttc,
            'montant_regle' => $facture->montant_regle,
            'reste_a_payer' => $facture->reste_a_payer,
            'date_facture' => $facture->date_facture->format('Y-m-d'),
            'statut' => $facture->statut
        ]);
    }

    public function print(FactureClient $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);


        $pdf = PDF::loadView('pages.ventes.facture.partials.print-facture', compact('facture'));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }


    /**
     * Obtenir les détails d'une facture
     *
     * @param FactureClient $facture
     * @return JsonResponse
     */
    public function getDetailsFacture(FactureClient $facture): JsonResponse
    {
        // Changement dans le chargement des relations
        $facture->load([
            'client',
            'sessionCaisse', // On charge d'abord la session
            'lignes.article'
        ]);

        return response()->json([
            'numero' => $facture->numero,
            'date_facture' => $facture->date_facture->format('d/m/Y'),
            'client' => [
                'raison_sociale' => $facture->client->raison_sociale
            ],
            'point_vente' => $facture->sessionCaisse ? [
                'libelle' => $facture->sessionCaisse->point_de_vente_id ?
                    PointDeVente::find($facture->sessionCaisse->point_de_vente_id)->nom_pv : '-'
            ] : null,
            'montant_ht' => number_format($facture->montant_ht, 0, ',', ' '),
            'montant_tva' => number_format($facture->montant_tva, 0, ',', ' '),
            'montant_ttc' => number_format($facture->montant_ttc, 0, ',', ' '),
            'lignes' => $facture->lignes->map(function($ligne) {
                return [
                    'article' => [
                        'designation' => $ligne->article->designation
                    ],
                    'quantite' => number_format($ligne->quantite, 0, ',', ' '),
                    'prix_unitaire' => number_format($ligne->prix_unitaire_ht, 0, ',', ' '),
                    'montant_total' => number_format($ligne->montant_ttc, 0, ',', ' ')
                ];
            })
        ]);
    }
}
