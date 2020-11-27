<?php

namespace Core\Acl;

use Core\Acl\Contracts\PolicyEnforcementPoint as PEP;
use Core\Acl\Contracts\PolicyInformationPoint as PIP;
use Core\Acl\Contracts\PolicyDecisionPoint as PDP;
use Core\Acl\Contracts\PolicyRepository;
use Core\Acl\Repository\FileRepository;
use Illuminate\Support\ServiceProvider;


class AclServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     * @return void
     */
    public function boot()
    {
        $default_config_path = dirname(__DIR__).'/config/abac.php';
        $this->publishes([
            $default_config_path => $this->app->configPath('abac.php'),
        ]);

        $this->app->configure('abac');

        $this->app->resolving(PolicyInformationPoint::class, function(PolicyInformationPoint $pip) {
            $resolvers = $this->app->config->get('abac.resolvers');
            foreach ($resolvers as $key => $resolver) $pip->setResolver($key, $resolver);
        });

        $this->app->resolving(PEP::class, function (PEP $pep) {
            $strategy_class = $this->app->config->get('abac.default_strategy');
            $ref = new \ReflectionClass($strategy_class);
            $pep->setStrategy($ref->newInstance());
        });

        $this->app->singleton(\Illuminate\Contracts\Auth\Access\Gate::class, Gate::class);
    }

    /**
     * Register services.
     * @return void
     */
    public function register()
    {
        $default_config_path = dirname(__DIR__).'/config/abac.php';
        $this->mergeConfigFrom($default_config_path, 'abac');
        $this->registerAclComponents();
    }


    protected function registerAclComponents()
    {
        $this->app->singleton(PolicyRepository::class, FileRepository::class);
        $this->app->singleton(PIP::class, PolicyInformationPoint::class);
        $this->app->singleton(PEP::class, PolicyEnforcementPoint::class);
        $this->app->singleton(PDP::class, PolicyDecisionPoint::class);

        $this->app->routeMiddleware(['acl' => AclMiddleware::class]);
    }
}