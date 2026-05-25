<?php

namespace App\Providers;

use App\Company;
use App\Product;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/admin';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * Laravel 10 ya no soporta `map()`; usamos el callback de
     * `$this->routes()` y mantenemos el `namespace` para que los strings
     * "Controller@action" sigan resolviéndose sin tocar todas las rutas.
     *
     * @return void
     */
    public function boot()
    {
        Route::bind('company', function ($value) {
            if (! auth()->check()) {
                abort(404);
            }

            $user = auth()->user();

            return Company::where('id', $value)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                    if ($user->isSalesRep()) {
                        $query->orWhere(function ($q) use ($user) {
                            $q->where('sales_rep_user_id', $user->id)
                                ->whereNull('sales_converted_at');
                        });
                    }
                })
                ->firstOrFail();
        });

        Route::bind('product', function ($value) {
            if (!auth()->check()) {
                abort(404);
            }

            return Product::where('id', $value)
                ->whereHas('section.company', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
        });

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
}
