<?php

namespace App\Providers;

// 1. IMPORTER TOUS LES MODÈLES ET POLICIES NÉCESSAIRES
use App\Models\Agent;
use App\Models\IndisponibiliteAgent;
use App\Models\PeriodeAstreinte;
use App\Models\Planning;
use App\Models\Service;
use App\Policies\AgentPolicy;
use App\Policies\IndisponibiliteAgentPolicy;
use App\Policies\PeriodeAstreintePolicy;
use App\Policies\PlanningPolicy;
use App\Policies\ServicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 2. AJOUTER TOUTES VOS POLICIES ICI
        Service::class => ServicePolicy::class,
        Agent::class => AgentPolicy::class,
        PeriodeAstreinte::class => PeriodeAstreintePolicy::class,
        
        // J'ajoute déjà celles pour les prochaines étapes, comme ça c'est fait !
        Planning::class => PlanningPolicy::class,
        IndisponibiliteAgent::class => IndisponibiliteAgentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Ce code est nécessaire pour que les policies fonctionnent
        $this->registerPolicies();
    }
}