<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'PagesController@index')->name('home');
Route::get('/landing-preview', 'PagesController@landingPreview')->name('landing.preview');
Route::view('/welcome', 'welcome')->name('welcome');
Route::post('/pay_product', 'PaymentController@pay_product')->name('pay_product');
Route::post('/process_subscription', 'PaymentController@process_subscription')->name('process_subscription');
Route::post('/te_llamamos', 'PagesController@te_llamamos')->middleware('throttle:10,1')->name('te_llamamos');
Route::post('/sugerencia', 'PagesController@suggestion')->middleware('throttle:10,1')->name('suggestion');
Route::post('/table_reservation', 'PagesController@table_reservation')->middleware('throttle:10,1')->name('table_reservation');

Route::post(
    'stripe/webhook',
    '\Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook'
);

Route::get('pre-alta/{slug}', 'PreAltaPreviewController@show')->name('pre-alta.preview');
Route::get('pre-alta/media/{id}', 'PreAltaPreviewController@media')->name('pre-alta.media')->where('id', '[0-9]+');

Route::get('activar/{token}', 'PreAltaClaimController@show')->name('pre-alta.claim.show')->where('token', '[a-fA-F0-9]{64}');
Route::post('activar/{token}', 'PreAltaClaimController@store')->name('pre-alta.claim.store')->where('token', '[a-fA-F0-9]{64}');

// Menú en carta con URL simple: /carta/{company}/menu/{menu}
Route::get('carta/{companySlug}/menu/{menuSlug}', function ($companySlug, $menuSlug) {
    $qs = request()->getQueryString();
    $to = route('public.company.menu', ['companySlug' => $companySlug, 'menuSlug' => $menuSlug]);

    return redirect($to . ($qs ? '?' . $qs : ''), 301);
})
    ->where('companySlug', '[a-z0-9][a-z0-9-]*')
    ->where('menuSlug', '[a-z0-9][a-z0-9-]*')
    ->name('public.menu.simple');

// Menú público concreto: /carta/{owner}/{company}/menu/{menu}
Route::get('carta/{ownerSlug}/{companySlug}/menu/{menuSlug}', function ($ownerSlug, $companySlug, $menuSlug) {
    $qs = request()->getQueryString();
    $to = route('public.company.menu', ['companySlug' => $companySlug, 'menuSlug' => $menuSlug]);

    return redirect($to . ($qs ? '?' . $qs : ''), 301);
})
    ->where('ownerSlug', '[a-z0-9][a-z0-9-]*')
    ->where('companySlug', '[a-z0-9][a-z0-9-]*')
    ->where('menuSlug', '[a-z0-9][a-z0-9-]*')
    ->name('public.menu');

// URL pública canónica: /carta/{owner}/{company}
Route::get('carta/{ownerSlug}/{companySlug}', function ($ownerSlug, $companySlug) {
    $qs = request()->getQueryString();
    $to = route('public.company', ['companySlug' => $companySlug]);

    return redirect($to . ($qs ? '?' . $qs : ''), 301);
})
    ->where('ownerSlug', '[a-z0-9][a-z0-9-]*')
    ->where('companySlug', '[a-z0-9][a-z0-9-]*')
    ->name('see_menu');

// /carta/{slug} (un solo segmento) — discriminamos:
//   - Si es un User slug -> hub del negocio (lista cartas y menús).
//   - Si es una Company slug -> redirect 301 al formato canónico.
//   - 'demo' (o variantes) -> controlador legacy.
Route::get('carta/{slug}', function ($slug) {
    $redirectTarget = app(\App\Services\PublicUrlRedirectService::class)->resolveFromRequest(request());
    if ($redirectTarget) {
        return redirect($redirectTarget, 301);
    }

    if ($slug === 'demo' || strpos($slug, 'demo') === 0) {
        return app(\App\Http\Controllers\PagesController::class)
            ->see_menu(app(\App\Services\MenuService::class), request(), null, $slug);
    }

    $user = \App\User::where('slug', $slug)->first();
    if ($user) {
        $qs = request()->getQueryString();
        $to = route('public.owner.hub', ['ownerSlug' => $slug]);

        return redirect($to . ($qs ? '?' . $qs : ''), 301);
    }

    $company = \App\Company::where('slug', $slug)->with('user')->first();
    if ($company) {
        $qs = request()->getQueryString();
        $to = route('public.company', ['companySlug' => $company->slug]);

        return redirect($to . ($qs ? '?' . $qs : ''), 301);
    }

    abort(404);
})->where('slug', '[a-z0-9][a-z0-9-]*')->name('public.hub');

Route::get('tv/{companySlug}/sync.json', 'TvMenuController@sync')->name('tv.sync');
Route::get('tv/{companySlug}', 'TvMenuController@show')->name('tv.show');
Route::get('tv/{companySlug}/{layout}', 'TvMenuController@show')->name('tv.show.layout');

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'subscribed', 'redirect.sales.from.admin']], function () {
    Route::get('check-public-path', 'PublicPathController@check')->name('admin.check-public-path');

    Route::get('onboarding', 'OnboardingController@show')->name('admin.onboarding');
    Route::post('onboarding', 'OnboardingController@update')->name('admin.onboarding.update');
    Route::post('onboarding/skip', 'OnboardingController@skip')->name('admin.onboarding.skip');

    Route::get('settings', 'AccountSettingsController@index')->name('admin.settings');
    Route::put('settings/profile', 'AccountSettingsController@updateProfile')->name('admin.settings.profile');
    Route::put('settings/billing-info', 'AccountSettingsController@updateBillingInfo')->name('admin.settings.billing-info');
    Route::post('billing/portal', 'BillingController@portal')->name('admin.billing.portal');

    Route::get('billing', 'BillingController@index')->name('admin.billing');

    Route::group(['prefix' => 'platform', 'middleware' => ['super.admin']], function () {
        Route::get('/', 'PlatformDashboardController@index')->name('admin.platform.dashboard');
        Route::get('settings', 'PlatformSettingsController@edit')->name('admin.platform.settings');
        Route::put('settings', 'PlatformSettingsController@update')->name('admin.platform.settings.update');
        Route::match(['post', 'put'], 'settings/test-gemini', 'PlatformSettingsController@testGemini')->name('admin.platform.settings.test-gemini');
        Route::match(['post', 'put'], 'settings/test-mail', 'PlatformSettingsController@testMail')->name('admin.platform.settings.test-mail');
        Route::match(['post', 'put'], 'settings/test-stripe', 'PlatformSettingsController@testStripe')->name('admin.platform.settings.test-stripe');
        Route::post('settings/brand/{key}', 'PlatformSettingsController@uploadBrandAsset')
            ->name('admin.platform.settings.brand.upload')
            ->where('key', 'logo|isotipo|favicon|og')
            ->middleware('throttle:30,1');
        Route::delete('settings/brand/{key}', 'PlatformSettingsController@deleteBrandAsset')
            ->name('admin.platform.settings.brand.delete')
            ->where('key', 'logo|isotipo|favicon|og')
            ->middleware('throttle:30,1');
        Route::get('billing', 'PlatformBillingController@index')->name('admin.platform.billing.index');
        Route::post('billing/create-price', 'PlatformBillingController@createPrice')->name('admin.platform.billing.create-price');
        Route::post('billing/recreate-price', 'PlatformBillingController@recreatePrice')->name('admin.platform.billing.recreate-price');
        Route::post('billing/save-amount', 'PlatformBillingController@saveAmount')->name('admin.platform.billing.save-amount');
        Route::post('billing/create-all', 'PlatformBillingController@createAllPrices')->name('admin.platform.billing.create-all');
        Route::post('billing/clear-catalog', 'PlatformBillingController@clearCatalog')->name('admin.platform.billing.clear-catalog');
        Route::post('billing/save-price-id', 'PlatformBillingController@savePriceId')->name('admin.platform.billing.save-price-id');
        Route::get('users', 'PlatformUsersController@index')->name('admin.platform.users.index');
        Route::get('users/{user}', 'PlatformUsersController@show')->name('admin.platform.users.show');
        Route::post('users/{user}/grant-super-admin', 'PlatformUsersController@grantSuperAdmin')
            ->name('admin.platform.users.grant-super-admin')
            ->middleware('throttle:30,1');
        Route::post('users/{user}/cancel-subscription', 'PlatformUsersController@cancelSubscription')
            ->name('admin.platform.users.cancel-subscription')
            ->middleware('throttle:30,1');
        Route::post('users/{user}/resume-subscription', 'PlatformUsersController@resumeSubscription')
            ->name('admin.platform.users.resume-subscription')
            ->middleware('throttle:30,1');
        Route::put('users/{user}/billing', 'PlatformUsersController@updateBilling')
            ->name('admin.platform.users.update-billing')
            ->middleware('throttle:20,1');
        Route::get('comercial', 'PlatformSalesController@index')->name('admin.platform.sales.index');
        Route::put('comercial', 'PlatformSalesController@update')->name('admin.platform.sales.update');
        Route::get('comercial/export', 'PlatformSalesController@export')->name('admin.platform.sales.export');
        Route::post('comercial/comerciales', 'PlatformSalesController@storeRep')->name('admin.platform.sales.reps.store')->middleware('throttle:20,1');
        Route::post('comercial/comerciales/{user}/reenviar-acceso', 'PlatformSalesController@resendRepAccess')->name('admin.platform.sales.reps.resend-access')->middleware('throttle:20,1');
        Route::post('users/{user}/grant-sales-rep', 'PlatformSalesController@grantSalesRep')
            ->name('admin.platform.users.grant-sales-rep')
            ->middleware('throttle:30,1');
        Route::post('users/{user}/revoke-sales-rep', 'PlatformSalesController@revokeSalesRep')
            ->name('admin.platform.users.revoke-sales-rep')
            ->middleware('throttle:30,1');
        Route::post('users/{user}/impersonate', 'PlatformUsersController@impersonate')
            ->name('admin.platform.users.impersonate')
            ->middleware('throttle:30,1');
    });
});

Route::post('admin/stop-impersonating', 'Admin\\PlatformUsersController@stopImpersonating')
    ->name('admin.platform.users.stop-impersonating')
    ->middleware(['auth']);

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'subscribed', 'onboarding.complete', 'selected.company']], function () {
    Route::get('/', 'AdminController@index')->name('admin.dashboard');
    Route::post('profile-wizard/dismiss', 'ProfileWizardController@dismiss')->name('admin.profile-wizard.dismiss');
    Route::get('companies', 'CompaniesController@index')->name('admin.companies.index');
    Route::post('companies', 'CompaniesController@store')->name('admin.companies.store');
    Route::get('companies/{company}', 'CompaniesController@edit')->name('admin.companies.edit');
    Route::put('companies/{company}', 'CompaniesController@update')->name('admin.companies.update');
    Route::put('companies/{company}/daily-highlights', 'CompaniesController@updateDailyHighlights')->name('admin.companies.daily-highlights');
    Route::get('companies/{company}/languages', 'TranslationController@edit')->name('admin.companies.languages');
    Route::put('companies/{company}/languages', 'TranslationController@updateLocales')->name('admin.companies.languages.update');
    Route::post('companies/{company}/languages/translate', 'TranslationController@generate')->name('admin.companies.languages.translate');
    Route::put('companies/{company}/languages/sections/{section}', 'TranslationController@updateSection')->name('admin.companies.languages.section');
    Route::put('companies/{company}/languages/products/{product}', 'TranslationController@updateProduct')->name('admin.companies.languages.product');
    Route::delete('companies', 'CompaniesController@delete')->name('admin.companies.delete');
    Route::patch('companies/{company}/toggle-enabled', 'CompaniesController@toggleEnabled')->name('admin.companies.toggle-enabled');
    Route::post('companies/{company}/logo', 'CompaniesController@storelogo')->name('admin.companies.storelogo');
    Route::delete('companies/{company}/deletelogo', 'CompaniesController@deletelogo')->name('admin.companies.deletelogo');
    Route::post('companies/{company}/header', 'CompaniesController@storeheader')->name('admin.companies.storeheader');
    Route::delete('companies/{company}/deleteheader', 'CompaniesController@deleteheader')->name('admin.companies.deleteheader');
    Route::post('companies/changecompany', 'CompaniesController@changecompany')->name('admin.companies.changecompany');

    Route::get('sections', 'SectionsController@index')->name('admin.sections.index');
    Route::get('sections/create', 'SectionsController@create')->name('admin.sections.create');
    Route::post('sections', 'SectionsController@store')->name('admin.sections.store');
    Route::put('sections', 'SectionsController@update')->name('admin.sections.update');
    Route::delete('sections', 'SectionsController@delete')->name('admin.sections.delete');
    Route::put('products/order_section', 'SectionsController@order_section')->name('admin.sections.order_section');
    Route::put('sections/update_menu_type', 'SectionsController@update_menu_type')->name('admin.sections.updatemenutype');
    Route::put('sections/update_pdf_menu', 'SectionsController@update_pdf_menu')->name('admin.sections.updatepdfmenu');
    Route::get('qrgenerator/{company}', 'QrController@qrgenerator')->name('admin.qrgenerator');
    Route::get('qrgenerator/{company}/print', 'QrController@print')->name('admin.qr.print');
    Route::post('qrgenerator/{company}/email', 'QrController@email')->name('admin.qr.email');
    // QR del hub público del owner
    Route::get('qrgenerator-hub', 'QrController@hubQrgenerator')->name('admin.qr.hub.generator');
    Route::get('qrgenerator-hub/print', 'QrController@hubPrint')->name('admin.qr.hub.print');
    Route::post('qrgenerator-hub/email', 'QrController@hubEmail')->name('admin.qr.hub.email');
    Route::get('menu-print/{company}', 'MenuPrintController@printPdf')->name('admin.menu-print');

    Route::redirect('integrations', '/admin/tvpik', 301)->name('admin.integrations.index');
    Route::redirect('signage', '/admin/tvpik', 301)->name('admin.signage.index');
    Route::post('signage/token', 'SignageIntegrationController@regenerateToken')->name('admin.signage.regenerate');
    Route::post('integrations/token', 'SignageIntegrationController@regenerateToken')->name('admin.integrations.regenerate');

    Route::get('tvpik', 'TvpikController@index')->name('admin.tvpik.index');
    Route::post('tvpik/connect', 'TvpikController@connect')->name('admin.tvpik.connect');
    Route::post('tvpik/disconnect', 'TvpikController@disconnect')->name('admin.tvpik.disconnect');
    Route::post('tvpik/publish', 'TvpikController@publish')->name('admin.tvpik.publish');
    Route::post('tvpik/publish-all', 'TvpikController@publishAll')->name('admin.tvpik.publish-all');
    Route::get('tvpik/preview', 'TvpikController@preview')->name('admin.tvpik.preview');
    Route::get('tvpik/player', 'TvpikController@player')->name('admin.tvpik.player');

    Route::get('menus', 'MenusController@index')->name('admin.menus.index');
    Route::post('menus', 'MenusController@store')->name('admin.menus.store');
    Route::post('menus/combine', 'MenusController@updateCombine')->name('admin.menus.combine');
    Route::get('menus/{menu}/edit', 'MenusController@edit')->name('admin.menus.edit');
    Route::put('menus/{menu}', 'MenusController@update')->name('admin.menus.update');
    Route::delete('menus/{menu}', 'MenusController@destroy')->name('admin.menus.destroy');
    Route::post('menus/reorder', 'MenusController@reorder')->name('admin.menus.reorder');
    Route::post('menus/{menu}/items/upload-image', 'MenusController@uploadItemImage')->name('admin.menus.items.upload_image');

    // QR del menú individual
    Route::get('menus/{menu}/qr', 'QrController@menuQrgenerator')->name('admin.qr.menu.generator');
    Route::get('menus/{menu}/qr/print', 'QrController@menuPrint')->name('admin.qr.menu.print');
    Route::post('menus/{menu}/qr/email', 'QrController@menuEmail')->name('admin.qr.menu.email');

    Route::get('products', 'ProductsController@index')->name('admin.products.index');
    Route::get('products/{product}/edit', 'ProductsController@edit')->name('admin.products.edit');
    Route::post('products', 'ProductsController@store')->name('admin.products.store');
    Route::put('products', 'ProductsController@update')->name('admin.products.update');
    Route::delete('products', 'ProductsController@delete')->name('admin.products.delete');
    Route::delete('products/delete_image_product/{product}', 'ProductsController@delete_image_product')->name('admin.products.delete_image_product');
    Route::delete('products/delete_video_product/{product}', 'ProductsController@delete_video_product')->name('admin.products.delete_video_product');
    Route::post('products/{product}/upload-image', 'ProductsController@uploadImageInline')->name('admin.products.upload_image');
    Route::post('products/{product}/upload-video', 'ProductsController@uploadVideoInline')->name('admin.products.upload_video');
    Route::put('products/order_product', 'ProductsController@order_product')->name('admin.products.order_product');
    Route::patch('products/{product}/enabled', 'ProductsController@toggle_enabled')->name('admin.products.toggle_enabled');

    Route::get('menu-scan', 'MenuScanController@create')->name('admin.menu-scan.create');
    Route::post('menu-scan', 'MenuScanController@store')->name('admin.menu-scan.store');
    Route::get('menu-scan/{job}', 'MenuScanController@show')->name('admin.menu-scan.show');
    Route::put('menu-scan/{job}', 'MenuScanController@update')->name('admin.menu-scan.update');
    Route::post('menu-scan/{job}/import', 'MenuScanController@import')->name('admin.menu-scan.import');
    Route::delete('menu-scan/{job}', 'MenuScanController@destroy')->name('admin.menu-scan.destroy');
});

Route::group(['prefix' => 'comercial', 'namespace' => 'Sales'], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('sales.login');
    Route::post('login', 'LoginController@login')->middleware('throttle:10,1');
    Route::post('logout', 'LoginController@logout')->name('sales.logout')->middleware('auth');

    Route::group(['middleware' => ['auth', 'sales.rep']], function () {
        Route::get('/', 'DashboardController@index')->name('sales.dashboard');
        Route::post('visitas', 'VisitController@store')->name('sales.visit.store');
        Route::get('visitas/{company}', 'VisitController@show')->name('sales.visit.show')->where('company', '[0-9]+');
        Route::get('visitas/{company}/carta', 'VisitController@carta')->name('sales.visit.carta')->where('company', '[0-9]+');
        Route::get('visitas/{company}/presentar', 'VisitDesignController@show')->name('sales.visit.present')->where('company', '[0-9]+');
        Route::put('visitas/{company}/diseno', 'VisitDesignController@update')->name('sales.visit.design.update')->where('company', '[0-9]+');

        Route::get('visitas/{company}/menu-scan', 'MenuScanController@create')->name('sales.menu-scan.create')->where('company', '[0-9]+');
        Route::post('visitas/{company}/menu-scan', 'MenuScanController@store')->name('sales.menu-scan.store')->where('company', '[0-9]+');
        Route::get('visitas/{company}/menu-scan/{job}', 'MenuScanController@show')->name('sales.menu-scan.show')->where(['company' => '[0-9]+', 'job' => '[0-9]+']);
        Route::put('visitas/{company}/menu-scan/{job}', 'MenuScanController@update')->name('sales.menu-scan.update')->where(['company' => '[0-9]+', 'job' => '[0-9]+']);
        Route::post('visitas/{company}/menu-scan/{job}/import', 'MenuScanController@import')->name('sales.menu-scan.import')->where(['company' => '[0-9]+', 'job' => '[0-9]+']);

        Route::get('visitas/{company}/demo-productos', 'DemoProductsController@index')->name('sales.demo-products.index')->where('company', '[0-9]+');
        Route::post('visitas/{company}/demo-productos/{product}', 'DemoProductsController@update')->name('sales.demo-products.update')->where(['company' => '[0-9]+', 'product' => '[0-9]+']);

        Route::get('visitas/{company}/cerrar', 'HandoffController@show')->name('sales.handoff.show')->where('company', '[0-9]+');
        Route::post('visitas/{company}/cerrar', 'HandoffController@store')->name('sales.handoff.store')->middleware('throttle:10,1')->where('company', '[0-9]+');
    });
});

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('auth/google', 'Auth\GoogleAuthController@redirect')->name('auth.google.redirect');
Route::get('auth/google/callback', 'Auth\GoogleAuthController@callback')->name('auth.google.callback');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('register', 'Auth\RegisterController@index')->name('register');
Route::post('register', 'Auth\RegisterController@register');

Route::get('password/request', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// Hub público del owner: /@{ownerSlug}
Route::get('@{ownerSlug}', 'PagesController@ownerHub')
    ->where('ownerSlug', '[a-z0-9][a-z0-9-]*')
    ->name('public.owner.hub');

// Nueva URL pública canónica: /{companySlug}/{menuSlug}
Route::get('{companySlug}/{menuSlug}', 'PagesController@publicCompanyMenu')
    ->where('companySlug', '[a-z0-9][a-z0-9-]*')
    ->where('menuSlug', '[a-z0-9][a-z0-9-]*')
    ->name('public.company.menu');

// Nueva URL pública canónica: /{companySlug}
Route::get('{companySlug}', 'PagesController@publicCompanyHub')
    ->where('companySlug', '[a-z0-9][a-z0-9-]*')
    ->name('public.company');
