<?php

namespace App\Http\Controllers\Rapport;

use App\Models\Vente\Client;
use App\Models\Vente\SoldeInitialClient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SoldeInitialClientController extends Controller
{
    /**
     * Importe les soldes initiaux des clients depuis un fichier Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls',
                'date_solde' => 'required|date',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Enlever l'en-tête
            array_shift($rows);

            DB::beginTransaction();

            $errors = [];
            $imported = 0;
            $debug = []; // Pour le débogage

            foreach ($rows as $index => $row) {
                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Nettoyer et préparer les données
                    $codeClient = trim((string)$row[0]);
                    $montant = str_replace([' ', ','], ['', '.'], $row[1]);
                    $montant = floatval($montant);
                    $type = strtoupper(trim((string)$row[2]));
                    $commentaire = trim((string)($row[3] ?? ''));

                    // Vérifier le client
                    $client = Client::where('code_client', $codeClient)->first();

                    if (!$client) {
                        $errors[] = "Ligne " . ($index + 2) . " : Client non trouvé (Code: {$codeClient})";
                        $debug[] = ["error" => "Client non trouvé", "code" => $codeClient];
                        continue;
                    }

                    // Vérifier le type
                    if (!in_array($type, ['DEBITEUR', 'CREDITEUR'])) {
                        $errors[] = "Ligne " . ($index + 2) . " : Type invalide (doit être DEBITEUR ou CREDITEUR)";
                        $debug[] = ["error" => "Type invalide", "type" => $type];
                        continue;
                    }

                    // Vérifier le montant
                    if ($montant <= 0) {
                        $errors[] = "Ligne " . ($index + 2) . " : Montant invalide";
                        $debug[] = ["error" => "Montant invalide", "montant" => $montant];
                        continue;
                    }

                    // Supprimer l'ancien solde
                    SoldeInitialClient::where('client_id', $client->id)->delete();

                    // Créer le nouveau solde
                    $solde = new SoldeInitialClient();
                    $solde->client_id = $client->id;
                    $solde->montant = $montant;
                    $solde->type = $type;
                    $solde->date_solde = $request->date_solde;
                    $solde->commentaire = $commentaire;

                    if (!$solde->save()) {
                        throw new \Exception("Erreur lors de l'enregistrement du solde");
                    }

                    $imported++;
                    $debug[] = [
                        "success" => true,
                        "client_id" => $client->id,
                        "montant" => $montant,
                        "type" => $type,
                        "date" => $request->date_solde
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($index + 2) . " : " . $e->getMessage();
                    $debug[] = ["error" => "Exception", "message" => $e->getMessage()];
                }
            }

            // Log des informations de débogage
            Log::info('Import soldes clients - Debug', $debug);

            if (!empty($errors)) {
                DB::rollBack();
                Log::error('Import soldes clients - Erreurs', $errors);
                return back()
                    ->with('error', 'Erreurs lors de l\'import : ' . implode("\n", $errors))
                    ->withInput();
            }

            DB::commit();
            return back()->with('success', "Import réussi ! $imported solde(s) importé(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import soldes clients - Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Une erreur est survenue lors de l\'import : ' . $e->getMessage())
                ->withInput();
        }
    }
}
