<?php

namespace clueapps\registration_module;

use Illuminate\Support\ServiceProvider;

class RegistrationModuleServiceProvider extends ServiceProvider {

    public function boot() {
        
       /*
       *--------------------------------------------------------------------------
       * Publishes For Package In Installation
       *--------------------------------------------------------------------------
       *
       * This will be your files when copied in their directory
       *
       */
//        $this->publishes([
//            __DIR__ . '/config/' => config_path('contact.php')
//        ]);
//
        $this->publishes([
            __DIR__ . '/Http/Controllers/' => base_path('/app/Http/Controllers/')
        ]);
    
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ]);
        
//        $this->publishes([
//            __DIR__ . '/views/' => base_path('/resources/views/')
//        ]);
    
        $this->publishes([
            __DIR__ . '/Models/' => base_path('app/')
        ]);
    
        
        $this->publishes([
            __DIR__ . '/Helper/' => base_path('/app/Http/Controllers/')
        ]);
    
//        $this->publishes([
//            __DIR__ . '/routes/' => base_path('routes/')
//        ]);
        
    }
    
    public function register() {
    
    }
}