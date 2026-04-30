<?php

namespace Relistix\HealthChecker;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Relistix\HealthChecker\Console\CheckMailCommand;
use Relistix\HealthChecker\Console\CheckQueueCommand;
use Relistix\HealthChecker\Support\Pinger;
use Relistix\HealthChecker\Support\PingUrlBuilderFactory;

class HealthCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/healthchecker.php', 'healthchecker');

        $this->app->singleton(
            PingUrlBuilderFactory::class,
            fn ($app) => new PingUrlBuilderFactory($app['config']),
        );

        $this->app->singleton(Pinger::class, fn ($app) => new Pinger(
            $app->make(HttpFactory::class),
            $app->make(LoggerInterface::class),
            (int) $app['config']->get('healthchecker.http.timeout', 5),
            (int) $app['config']->get('healthchecker.http.retries', 0),
        ));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'healthchecker');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/healthchecker.php' => config_path('healthchecker.php'),
            ], 'healthchecker-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/healthchecker'),
            ], 'healthchecker-views');

            $this->commands([
                CheckQueueCommand::class,
                CheckMailCommand::class,
            ]);
        }
    }
}
