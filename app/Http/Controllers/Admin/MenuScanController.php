<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\MenuScanJob;
use App\Services\MenuImportService;
use App\Services\MenuScan\MenuScanResult;
use App\Services\MenuScanService;
use App\Services\Platform\PlatformSettingsService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MenuScanController extends Controller
{
    public function create(PlatformSettingsService $platformSettings, UserPlanService $plans)
    {
        $company = $this->selectedCompanyOrFail();
        $this->authorize('update', $company);
        $user = auth()->user();

        return view('admin.menu-scan.create', [
            'company' => $company,
            'scanConfigured' => $platformSettings->hasGeminiApiKey(),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'scansRemaining' => $plans->menuScansRemaining($user),
            'scansUsed' => $plans->menuScansUsed($user),
            'scanLimit' => $plans->menuScanLimit($user),
            'canScan' => $plans->canUseMenuScan($user),
            'billingUrl' => route('admin.billing'),
        ]);
    }

    public function store(Request $request, MenuScanService $scanService, UserPlanService $plans)
    {
        $scanTimeout = (int) config('menu_scan.gemini.timeout', 90);
        $retries = (int) config('menu_scan.gemini.max_retries', 3);
        @set_time_limit($scanTimeout + ($retries * 45) + 30);

        $company = $this->selectedCompanyOrFail();
        $this->authorize('update', $company);

        $maxFiles = (int) config('menu_scan.limits.max_files', 10);
        $maxKb = (int) config('menu_scan.limits.max_mb', 8) * 1024;

        $this->validate($request, [
            'files' => 'required|array|min:1|max:' . $maxFiles,
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:' . $maxKb,
        ]);

        $user = auth()->user();
        $plans->assertCanUseMenuScan($user);

        $lifetimeLimit = $plans->menuScanLimit($user);
        if ($lifetimeLimit === null && ! $user->isSuperAdmin()) {
            $rateKey = 'menu-scan:' . $user->id;
            $maxScans = (int) config('menu_scan.limits.scans_per_hour', 5);
            if ($maxScans > 0) {
                $attempts = (int) Cache::get($rateKey, 0);
                if ($attempts >= $maxScans) {
                    return back()->withErrors([
                        'files' => 'Has alcanzado el límite de escaneos por hora (' . $maxScans . '). Inténtalo más tarde.',
                    ]);
                }
            }
        }

        $job = MenuScanJob::create([
            'company_id' => $company->id,
            'user_id' => auth()->id(),
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
            if ($lifetimeLimit === null && ! $user->isSuperAdmin()) {
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

            return redirect()->route('admin.menu-scan.show', $job);
        }

        $job->status = MenuScanJob::STATUS_FAILED;
        $job->provider = $result->provider;
        $job->error_message = $result->errorMessage ?? 'No se pudo analizar la carta.';
        $job->save();

        return redirect()->route('admin.menu-scan.show', $job)
            ->withErrors(['scan' => $job->error_message]);
    }

    public function show(MenuScanJob $job)
    {
        $this->authorizeJob($job);

        return view('admin.menu-scan.review', [
            'job' => $job,
            'company' => $job->company,
        ]);
    }

    public function update(Request $request, MenuScanJob $job)
    {
        $this->authorizeJob($job);

        if ($job->status !== MenuScanJob::STATUS_REVIEW) {
            return redirect()->route('admin.menu-scan.show', $job);
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

        return redirect()->route('admin.menu-scan.show', $job)
            ->with('flash', 'Borrador guardado.');
    }

    public function import(Request $request, MenuScanJob $job, MenuImportService $importService)
    {
        $this->authorizeJob($job);

        if ($job->status !== MenuScanJob::STATUS_REVIEW) {
            return redirect()->route('admin.menu-scan.show', $job);
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
            $count = $importService->import($job->company, $job->parsed_menu ?? [], $mode);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['import' => $e->getMessage()]);
        }

        $job->status = MenuScanJob::STATUS_IMPORTED;
        $job->save();
        $this->deleteJobFiles($job);

        return redirect()->route('admin.sections.index')
            ->with('flash', "Carta importada: {$count} platos añadidos.");
    }

    public function destroy(MenuScanJob $job)
    {
        $this->authorizeJob($job);
        $this->deleteJobFiles($job);
        $job->delete();

        return redirect()->route('admin.menu-scan.create')
            ->with('flash', 'Importación cancelada.');
    }

    protected function authorizeJob(MenuScanJob $job): void
    {
        if ((int) $job->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $companyId = Cookie::get('selected_company');
        if ((int) $job->company_id !== (int) $companyId) {
            abort(403);
        }

        $this->authorize('update', $job->company);
    }

    protected function deleteJobFiles(MenuScanJob $job): void
    {
        $disk = config('menu_scan.storage_disk', 'local');
        Storage::disk($disk)->deleteDirectory($job->storageDirectory());
    }

    protected function selectedCompanyOrFail(): Company
    {
        $companyId = Cookie::get('selected_company');
        if (! $companyId) {
            abort(403);
        }

        return Company::where('user_id', auth()->id())
            ->where('id', $companyId)
            ->firstOrFail();
    }
}
