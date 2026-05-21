<?php

namespace App\Http\Controllers\Sales;

use App\Company;
use App\Http\Controllers\Controller;
use App\MenuScanJob;
use App\Services\MenuImportService;
use App\Services\MenuScan\MenuScanResult;
use App\Services\MenuScanService;
use App\Services\Platform\PlatformSettingsService;
use App\Services\Sales\SalesLeadService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MenuScanController extends Controller
{
    public function create(Company $company, SalesLeadService $leads, PlatformSettingsService $platformSettings, UserPlanService $plans)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        return view('sales.menu-scan.create', [
            'visit' => $visit,
            'scanConfigured' => $platformSettings->hasGeminiApiKey(),
        ]);
    }

    public function store(Request $request, Company $company, SalesLeadService $leads, MenuScanService $scanService, UserPlanService $plans)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $scanTimeout = (int) config('menu_scan.gemini.timeout', 90);
        $retries = (int) config('menu_scan.gemini.max_retries', 3);
        @set_time_limit($scanTimeout + ($retries * 45) + 30);

        $maxFiles = (int) config('menu_scan.limits.max_files', 10);
        $maxKb = (int) config('menu_scan.limits.max_mb', 8) * 1024;

        $this->validate($request, [
            'files' => 'required|array|min:1|max:' . $maxFiles,
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:' . $maxKb,
        ]);

        $user = auth()->user();
        $plans->assertCanUseMenuScan($user, $visit);

        $lifetimeLimit = $plans->canBypassMenuScanLimits($user, $visit) ? null : $plans->menuScanLimit($user);

        $job = MenuScanJob::create([
            'company_id' => $visit->id,
            'user_id' => $user->id,
            'status' => MenuScanJob::STATUS_PROCESSING,
        ]);

        $storedPaths = [];
        $absolutePaths = [];
        $disk = config('menu_scan.storage_disk', 'local');
        $dir = $job->storageDirectory();

        foreach ($request->file('files', []) as $file) {
            $path = $file->store($dir, $disk);
            $storedPaths[] = $path;
            $absolutePaths[] = Storage::disk($disk)->path($path);
        }

        $job->source_files = $storedPaths;
        $job->save();

        $result = $scanService->scan($absolutePaths);

        if ($result->isSuccess()) {
            if ($lifetimeLimit === null && ! $user->isSuperAdmin() && ! $plans->canBypassMenuScanLimits($user, $visit)) {
                $rateKey = 'menu-scan:' . $user->id;
                $maxScansPerHour = (int) config('menu_scan.limits.scans_per_hour', 5);
                if ($maxScansPerHour > 0) {
                    Cache::put($rateKey, (int) Cache::get($rateKey, 0) + 1, now()->addHour());
                }
            }

            $job->status = MenuScanJob::STATUS_REVIEW;
            $job->provider = $result->provider;
            $job->fallback_used = $result->fallbackUsed;
            $job->parsed_menu = $result->toParsedMenu();
            $job->error_message = null;
            $job->save();

            return redirect()->route('sales.menu-scan.show', [$visit->id, $job->id]);
        }

        $job->status = MenuScanJob::STATUS_FAILED;
        $job->provider = $result->provider;
        $job->error_message = $result->errorMessage ?? 'No se pudo analizar la carta.';
        $job->save();

        return redirect()->route('sales.menu-scan.show', [$visit->id, $job->id])
            ->withErrors(['scan' => $job->error_message]);
    }

    public function show(Company $company, MenuScanJob $job, SalesLeadService $leads)
    {
        $visit = $this->authorizeJob($leads, $company, $job);

        return view('sales.menu-scan.review', [
            'job' => $job,
            'visit' => $visit,
        ]);
    }

    public function update(Request $request, Company $company, MenuScanJob $job, SalesLeadService $leads)
    {
        $this->authorizeJob($leads, $company, $job);

        if ($job->status !== MenuScanJob::STATUS_REVIEW) {
            return redirect()->route('sales.menu-scan.show', [$company->id, $job->id]);
        }

        $validated = $this->validate($request, [
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.products' => 'required|array|min:1',
            'sections.*.products.*.name' => 'required|string|max:255',
            'sections.*.products.*.description' => 'nullable|string|max:2000',
            'sections.*.products.*.price_unit' => 'nullable|string|max:32',
            'sections.*.products.*.price_portion' => 'nullable|string|max:32',
        ]);

        $normalized = MenuScanResult::normalizeSections($validated['sections']);
        if (count($normalized) === 0) {
            return back()->withErrors(['sections' => 'Añade al menos una sección con un plato.']);
        }

        $job->parsed_menu = ['sections' => $normalized];
        $job->save();

        return redirect()->route('sales.menu-scan.show', [$company->id, $job->id])
            ->with('flash', 'Borrador guardado.');
    }

    public function import(Request $request, Company $company, MenuScanJob $job, SalesLeadService $leads, MenuImportService $importService)
    {
        $visit = $this->authorizeJob($leads, $company, $job);

        if ($job->status !== MenuScanJob::STATUS_REVIEW) {
            return redirect()->route('sales.menu-scan.show', [$company->id, $job->id]);
        }

        $request->validate([
            'import_mode' => 'required|in:append,replace',
        ]);

        $mode = $request->get('import_mode');
        if ($mode === MenuImportService::MODE_REPLACE && ! $request->boolean('replace_confirm')) {
            return back()->withErrors([
                'replace_confirm' => 'Debes confirmar que quieres reemplazar toda la carta.',
            ]);
        }

        try {
            $count = $importService->import($visit, $job->parsed_menu ?? [], $mode);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['import' => $e->getMessage()]);
        }

        $job->status = MenuScanJob::STATUS_IMPORTED;
        $job->save();
        $this->deleteJobFiles($job);

        return redirect()
            ->route('sales.visit.present', $visit->id)
            ->with('flash', "Carta importada: {$count} platos. Personaliza el diseño y preséntala al restaurante.");
    }

    protected function authorizeJob(SalesLeadService $leads, Company $company, MenuScanJob $job): Company
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);

        if ((int) $job->company_id !== (int) $visit->id) {
            abort(404);
        }

        if ((int) $job->user_id !== (int) auth()->id() && ! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $this->authorize('update', $visit);

        return $visit;
    }

    protected function deleteJobFiles(MenuScanJob $job): void
    {
        $disk = config('menu_scan.storage_disk', 'local');
        Storage::disk($disk)->deleteDirectory($job->storageDirectory());
    }
}
