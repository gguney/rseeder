<?php
namespace GGuney\RSeeder;

use Illuminate\Support\ServiceProvider;

class RSeederServiceProvider extends ServiceProvider
{
    protected array $commands = ['GGuney\RSeeder\Commands\MakeReverseSeeder'];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    public function boot()
    {
        //
    }
}
