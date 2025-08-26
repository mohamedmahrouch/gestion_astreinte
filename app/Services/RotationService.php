<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\PeriodeAstreinte;
use App\Models\Planning;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RotationService
{
    public function genererPlanningPourService(Service $service, Carbon $dateDebutMois, int $adminId): array
    {
        Log::info("Début de la génération du planning pour le service '{$service->nom}' pour le mois de " . $dateDebutMois->format('F Y'));
        
        $dateFinMois = $dateDebutMois->copy()->endOfMonth();

        // 1. Récupérer les agents actifs et disponibles du service, triés par nom
        $agents = $service->agents()
            ->where('is_disponible_astreinte', true)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        if ($agents->isEmpty()) {
            Log::warning("Aucun agent disponible trouvé pour le service '{$service->nom}'.");
            return ['status' => 'error', 'message' => 'Aucun agent disponible pour ce service.'];
        }

        // 2. Récupérer les périodes d'astreinte pour le mois donné
        $periodes = $service->periodesAstreinte()
            ->where('is_active', true)
            ->where('date_debut', '>=', $dateDebutMois)
            ->where('date_fin', '<=', $dateFinMois)
            ->orderBy('date_debut')
            ->get();

        if ($periodes->isEmpty()) {
            Log::warning("Aucune période d'astreinte définie pour le service '{$service->nom}' ce mois-ci.");
            return ['status' => 'error', 'message' => 'Aucune période d\'astreinte définie pour ce mois.'];
        }

        $affectationsCrees = 0;
        $erreurs = 0;
        $agentIndex = 0; // Pointeur pour la rotation

        DB::beginTransaction();
        try {
            foreach ($periodes as $periode) {
                // Pour chaque agent requis (généralement 1, mais extensible)
                for ($i = 0; $i < $periode->nb_agents_requis; $i++) {
                    
                    $agentTrouve = false;
                    $tentatives = 0;

                    // 3. Boucle pour trouver un agent disponible
                    while (!$agentTrouve && $tentatives < $agents->count()) {
                        $agentActuel = $agents[$agentIndex % $agents->count()];

                        // 4. Vérifier les indisponibilités de l'agent
                        $estIndisponible = $agentActuel->indisponibilites()
                            ->where('statut', 'approuve')
                            ->where('date_debut', '<=', $periode->date_fin)
                            ->where('date_fin', '>=', $periode->date_debut)
                            ->exists();

                        if (!$estIndisponible) {
                            // 5. Créer l'affectation dans le planning
                            Planning::create([
                                'periode_astreinte_id' => $periode->id,
                                'agent_id' => $agentActuel->id,
                                'created_by' => $adminId,
                                'statut' => 'planifie',
                            ]);
                            $affectationsCrees++;
                            $agentTrouve = true;
                        }

                        $agentIndex++;
                        $tentatives++;
                    }
                    if (!$agentTrouve) {
                        Log::error("Impossible de trouver un agent disponible pour la période {$periode->id}.");
                        $erreurs++;
                    }
                }
            }
            DB::commit();
            Log::info("Génération terminée: {$affectationsCrees} affectations créées, {$erreurs} erreurs.");
            return [
                'status' => 'success', 
                'message' => "Génération terminée. {$affectationsCrees} affectations créées.",
                'affectations' => $affectationsCrees,
                'erreurs' => $erreurs
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la génération du planning: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Une erreur est survenue: ' . $e->getMessage()];
        }
    }
}