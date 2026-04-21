<?php

namespace App\Providers;

use App\Events\CatalogProduitsImportes;
use App\Listeners\RecalculerVolatiliteListener;
use App\Models\ParametresEntreprise;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(CatalogProduitsImportes::class, RecalculerVolatiliteListener::class);
        // Forcer HTTPS en production
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Partage $peppolMode avec toutes les vues (lecture unique via static)
        View::composer('*', function ($view) {
            static $peppolMode = null;
            if ($peppolMode === null) {
                try {
                    $peppolMode = ParametresEntreprise::instance()->peppol_mode ?? 'desactive';
                } catch (\Exception) {
                    $peppolMode = 'desactive';
                }
            }
            $view->with('peppolMode', $peppolMode);
        });
    }
}
