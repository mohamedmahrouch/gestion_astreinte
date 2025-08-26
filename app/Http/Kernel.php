

'api' => [
    // La ligne suivante est celle qu'il faut ajouter ou s'assurer qu'elle est présente
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],