<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\ReglementFournisseur;
use App\Models\Achat\FactureFournisseur;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ReglementFournisseurController extends Controller
{
    public function index(Request $request)
    {

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Query de base avec relations
        $query = ReglementFournisseur::with(['facture.fournisseur', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('date_reglement', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('date_reglement', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereYear('date_reglement', Carbon::now()->year)
                        ->whereMonth('date_reglement', Carbon::now()->month);
                    break;
            }
        }

        if ($request->has('mode')) {
            $query->where('mode_reglement', $request->mode);
        }

        // Récupération des règlements paginés
        // $reglements = $query->paginate(15);
        $reglements = $query->get();

        // Statistiques
        $stats = [
            'date' => Carbon::now()->format('d/m/Y'),
            'nombreReglements' => ReglementFournisseur::whereNotNull('validated_at')
                ->whereYear('date_reglement', Carbon::now()->year)
                ->whereMonth('date_reglement', Carbon::now()->month)
                ->count(),
            'montantTotal' => ReglementFournisseur::whereNotNull('validated_at')
                ->sum('montant_reglement'),
            'facturesPayees' => FactureFournisseur::where('statut_paiement', 'PAYE')->count()
        ];

        // Récupération des factures non ou partiellement payées
        $factures = FactureFournisseur::whereIn('statut_paiement', ['NON_PAYE', 'PARTIELLEMENT_PAYE'])
            ->with('fournisseur', 'reglements')
            ->get()
            ->filter(function ($query) {
                return $query->facture_amont() > 0;
            });

        if ($request->ajax()) {
            return view('pages.achat.reglement-frs.index', [
                'date' => $date,
                'reglements' => $reglements,
                'nombreReglements' => $stats['nombreReglements'],
                'montantTotal' => $stats['montantTotal'],
                'facturesPayees' => $stats['facturesPayees'],
                'factures' => $factures
            ]);

            //    return view('achat.reglements.liste-partielle', compact('reglements', 'stats'))->render();
        }

        return view('pages.achat.reglement-frs.index', [
            'date' => $date,
            'reglements' => $reglements,
            'nombreReglements' => $stats['nombreReglements'],
            'montantTotal' => $stats['montantTotal'],
            'facturesPayees' => $stats['facturesPayees'],
            'factures' => $factures
        ]);
        // return view('pages.achat.reglement-frs.index', compact('date','reglements', 'stats', 'factures'));
    }

    public function store(Request $request)
    {
        // dd($request->get("facture_fournisseur_id"));
        try {
            DB::beginTransaction();

            $validated = $request->validate(ReglementFournisseur::$rules);

            $facturefournisseurs = FactureFournisseur::whereIn("id", $request->get("facture_fournisseur_id"))->get();

            // en cas d'une seule facture
            if (count($facturefournisseurs) == 1) {
                $facture = $facturefournisseurs[0];
                $data = array_merge($validated, ["facture_fournisseur_id" => $facture->id, "montant_reglement" => $facture->facture_amont()]);
            }
            // dd(count($facturefournisseurs));

            // en cas de pluisieures factures
            if (count($facturefournisseurs) > 1) {
                $totalRegle = $facturefournisseurs->sum(function ($query) {
                    return $query->facture_amont();
                });
                // dd($request->get("facture_fournisseur_id"));
                $facturesId = null;
                foreach ($request->get("facture_fournisseur_id") as $factureId) {
                    $facturesId = $facturesId . ',' . $factureId;
                }
                // dd($facturesId);
                $data = array_merge($validated, ["facture_fournisseur_id" =>  null, "factures" =>  $facturesId, "montant_reglement" => $totalRegle]);
            }

            $reglement = new ReglementFournisseur($data);
            $reglement->save();

            // 
            if ($request->has('validate') && $request->validate) {
                $reglement->validate();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement créé avec succès',
                'data' => $reglement
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du règlement : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du règlement ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(ReglementFournisseur $reglement)
    {
        $reglement->load(['facture.fournisseur', 'creator', 'validator']);
        $facture = FactureFournisseur::whereIn('statut_paiement', ['NON_PAYE', 'PARTIELLEMENT_PAYE'])
            ->where('id', $reglement->facture_fournisseur_id)
            ->with(['reglements' => function ($query) {
                $query->whereNotNull('validated_at'); // Filtrer uniquement les règlements valides
            }])
            ->first();

        $montantRestant = $facture?->montant_ttc - $facture?->reglements->sum('montant_reglement');

        return response()->json([
            'success' => true,
            'data' => $reglement,
            'montant_restant' => $montantRestant,
        ]);
    }

    public function update(Request $request, ReglementFournisseur $reglement)
    {
        try {
            if ($reglement->isValidated()) {
                throw new \Exception('Impossible de modifier un règlement validé');
            }

            DB::beginTransaction();

            $rules = ReglementFournisseur::$rules;
            $rules['code'] = 'required|unique:reglement_fournisseurs,code,' . $reglement->id;

            $validated = $request->validate($rules);
            $reglement->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement modifié avec succès',
                'data' => $reglement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du règlement : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ReglementFournisseur $reglement)
    {
        try {
            if ($reglement->isValidated()) {
                throw new \Exception('Impossible de supprimer un règlement validé');
            }

            DB::beginTransaction();
            $reglement->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function validateReglement(ReglementFournisseur $reglement)
    {
        try {
            DB::beginTransaction();

            if (!$reglement->validate()) {
                throw new \Exception('Impossible de valider ce règlement');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Le règlement a été validé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation du règlement : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    // public function print(ReglementFournisseur $reglement)
    // {
    //     $reglement->load(['facture.fournisseur', 'creator', 'validator']);

    //     $pdf = PDF::loadView('achat.reglements.print', compact('reglement'));

    //     return $pdf->download("REGLEMENT_{$reglement->code}.pdf");
    // }
}
