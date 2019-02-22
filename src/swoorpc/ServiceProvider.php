<?php

namespace Swoorpc;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Swoorpc\Commands\SwoorpcCmd;


class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoute();
        $this->loadCommands();
    }

    /**
     * 加载命令
     *
     * @return void
     */
    protected function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SwoorpcCmd::class
            ]);
        }
    }

    /**
     * 加载路由文件
     *
     * @return void
     */
    protected function loadRoute()
    {
        if (str_is('5.2.*', $this->app::VERSION)) {
            $routeFilePath = base_path('app/Http/rpc.php');
        } else {
            $routeFilePath = base_path('routes/rpc.php');
        }

        if (file_exists($routeFilePath)) {
            require $routeFilePath;
        } else {
            require __DIR__ . '/rpc.php';
        }
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setupConfig();
        $this->setupRoute();
        $this->registerSwoorpcServer();
        $this->registerSwoorpcRouter();
    }

    /**
     * 设置配置文件
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/config.php');
        $this->publishes([$source => config_path('swoorpc.php')]);
        $this->mergeConfigFrom($source, 'swoorpc');
    }

    /**
     * 设置路由
     */
    protected function setupRoute()
    {
        $source = realpath(__DIR__ . '/rpc.php');

        if (str_is('5.2.*', $this->app::VERSION)) {
            $targetPath = base_path('app/Http/rpc.php');
        } else {
            $targetPath = base_path('routes/rpc.php');
        }

        $this->publishes([$source => $targetPath]);
    }


    /**
     * 注册server
     */
    private function registerSwoorpcServer()
    {
        $this->app->singleton('swoorpc.server', function ($app) {
            $config = config('swoorpc.server');
            $server = Swoorpc::createServer($config);
            return $server;
        });
    }


    /**
     * 注册路由
     */
    private function registerSwoorpcRouter()
    {
        $this->app->singleton('swoorpc.router.facade', function ($app) {
            return new Router\Router();
        });
    }
}