<?php

namespace AlexMuller\Filemanager;

use AlexMuller\Filemanager\Voyager\AmfmImageFormField;
use Illuminate\Support\ServiceProvider;
use TCG\Voyager\Facades\Voyager;


class AMServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->loadViewsFrom(__DIR__.'/views', 'amfm');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__ . '/Helpers/helpers.php';
        Voyager::addFormField(AmfmImageFormField::class);
    }
}
