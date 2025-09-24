<?php

namespace App\Providers;

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
        Service::class => ServicePolicy::class,
        Agent::class => AgentPolicy::class,
        PeriodeAstreinte::class => PeriodeAstreintePolicy::class,
        Planning::class => PlanningPolicy::class,
        IndisponibiliteAgent::class => IndisponibiliteAgentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}