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
    /**
     * Génère le planning pour un service et un mois donnés.
     * La logique regroupe les périodes par semaine pour assigner un seul agent par semaine.
     */
    public function genererPlanningPourService(Service $service, Carbon $dateDebutMois, int $adminId): array
    {
        Log::info("Début de la génération du planning pour le service '{$service->nom}' pour le mois de " . $dateDebutMois->format('F Y'));
        
        $dateFinMois = $dateDebutMois->copy()->endOfMonth();

        // 1. Récupérer les agents actifs et disponibles, triés par nom.
        $agents = $service->agents()
            ->where('is_disponible_astreinte', true)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        if ($agents->isEmpty()) {
            Log::warning("Aucun agent disponible trouvé pour le service '{$service->nom}'.");
            return ['status' => 'error', 'message' => 'Aucun agent disponible pour ce service.'];
        }

        // 2. Récupérer les périodes d'astreinte pour le mois donné.
        $periodesDuMois = $service->periodesAstreinte()
            ->where('is_active', true)
            ->where('date_debut', '>=', $dateDebutMois)
            ->where('date_fin', '<=', $dateFinMois)
            ->orderBy('date_debut')
            ->get();

        if ($periodesDuMois->isEmpty()) {
            Log::warning("Aucune période d'astreinte définie pour le service '{$service->nom}' ce mois-ci.");
            return ['status' => 'error', 'message' => 'Aucune période d\'astreinte définie pour ce mois.'];
        }

        // 3. Regrouper les périodes par leur numéro de semaine calendaire.
        $periodesParSemaine = $periodesDuMois->groupBy(function ($periode) {
            // 'W' donne le numéro de la semaine ISO-8601 (Lundi est le 1er jour)
            return Carbon::parse($periode->date_debut)->format('W');
        });

        $affectationsCrees = 0;
        $erreurs = 0;
        $agentIndex = 0; // Pointeur pour la rotation, il continue de semaine en semaine.

        DB::beginTransaction();
        try {
            // 4. Boucler sur chaque groupe de SEMAINE (pas sur chaque période).
            foreach ($periodesParSemaine as $numeroSemaine => $periodesDeLaSemaine) {
                
                $agentPourLaSemaine = null;
                $tentatives = 0;

                // 5. Boucle pour trouver UN SEUL agent disponible pour TOUTE la semaine.
                while (is_null($agentPourLaSemaine) && $tentatives < $agents->count()) {
                    $agentCandidat = $agents[$agentIndex % $agents->count()];
                    
                    // On vérifie si cet agent est indisponible pendant AU MOINS UNE des périodes de la semaine.
                    $estIndisponibleCetteSemaine = false;
                    foreach ($periodesDeLaSemaine as $periode) {
                        $estIndisponible = $agentCandidat->indisponibilites()
                            ->where('statut', 'approuve')
                            ->where('date_debut', '<=', $periode->date_fin)
                            ->where('date_fin', '>=', $periode->date_debut)
                            ->exists();
                        
                        if ($estIndisponible) {
                            $estIndisponibleCetteSemaine = true;
                            break; // L'agent est absent, on arrête de vérifier cette semaine pour lui.
                        }
                    }

                    if (!$estIndisponibleCetteSemaine) {
                        $agentPourLaSemaine = $agentCandidat; // On a trouvé notre agent !
                    }

                    $agentIndex++; // On avance le pointeur pour la prochaine tentative ou la prochaine semaine.
                    $tentatives++;
                }

                // 6. Si on a trouvé un agent, on l'assigne à TOUTES les périodes de cette semaine.
                if ($agentPourLaSemaine) {
                    foreach ($periodesDeLaSemaine as $periode) {
                        Planning::create([
                            'periode_astreinte_id' => $periode->id,
                            'agent_id' => $agentPourLaSemaine->id,
                            'created_by' => $adminId,
                            'statut' => 'planifie',
                        ]);
                        $affectationsCrees++;
                    }
                } else {
                    Log::error("Impossible de trouver un agent disponible pour la semaine {$numeroSemaine} du service {$service->nom}.");
                    $erreurs++;
                }
            } // Fin de la boucle sur les semaines

            DB::commit();
            Log::info("Génération terminée pour le service '{$service->nom}': {$affectationsCrees} affectations créées, {$erreurs} erreurs.");
            return [
                'status' => 'success', 
                'message' => "Génération terminée. {$affectationsCrees} affectations créées.",
                'affectations' => $affectationsCrees,
                'erreurs' => $erreurs
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la génération du planning pour le service '{$service->nom}': " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Une erreur technique est survenue: ' . $e->getMessage()];
        }
    }
}