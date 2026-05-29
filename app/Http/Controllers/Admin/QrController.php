<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Concerns\SendsTcpdfResponse;
use App\Http\Controllers\Controller;
use App\Mail\QrCodeMail;
use App\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TCPDF;
use TCPDF2DBarcode;
use Throwable;

class QrController extends Controller
{
    use SendsTcpdfResponse;

    /** Presets de copias por hoja A4 (columnas × filas). */
    protected const COPY_PRESETS = [
        1 => ['cols' => 1, 'rows' => 1],
        4 => ['cols' => 2, 'rows' => 2],
        12 => ['cols' => 3, 'rows' => 4],
    ];

    public function qrgenerator(Request $request, Company $company)
    {
        $this->authorize('view', $company);

        $menuUrl = $company->publicUrl();
        $forceDownload = $request->boolean('download');
        $copies = $this->resolveCopies($request->query('copies'));

        $ctx = $this->companyContext($company);

        if ($request->query('format') === 'png') {
            return $this->renderPngForUrl($menuUrl, $ctx['slug'], $forceDownload);
        }

        try {
            $pdf = $this->buildQrPdfForContext($ctx, $menuUrl, $copies);
            $filename = $this->qrFilenameForContext($ctx, $copies, 'pdf');

            if ($forceDownload && method_exists($this, 'downloadPdfResponse')) {
                return $this->downloadPdfResponse($pdf, $filename);
            }

            return $this->inlinePdfResponse($pdf, $filename, $forceDownload);
        } catch (Throwable $e) {
            Log::warning('QR PDF generation failed, devolviendo PNG de respaldo', [
                'company_id' => $company->id,
                'company_slug' => $company->slug,
                'message' => $e->getMessage(),
            ]);

            return $this->renderPngForUrl($menuUrl, $ctx['slug'], $forceDownload, $e->getMessage());
        }
    }

    /**
     * QR del HUB del owner: una sola URL /carta/{owner} que lista todas
     * las cartas y menús del negocio.
     */
    public function hubQrgenerator(Request $request)
    {
        $ctx = $this->ownerHubContext($request);
        if ($ctx === null) abort(404);

        $forceDownload = $request->boolean('download');
        $copies = $this->resolveCopies($request->query('copies'));

        if ($request->query('format') === 'png') {
            return $this->renderPngForUrl($ctx['url'], $ctx['slug'], $forceDownload);
        }

        try {
            $pdf = $this->buildQrPdfForContext($ctx, $ctx['url'], $copies);
            $filename = $this->qrFilenameForContext($ctx, $copies, 'pdf');

            if ($forceDownload && method_exists($this, 'downloadPdfResponse')) {
                return $this->downloadPdfResponse($pdf, $filename);
            }

            return $this->inlinePdfResponse($pdf, $filename, $forceDownload);
        } catch (Throwable $e) {
            Log::warning('Hub QR PDF generation failed, devolviendo PNG de respaldo', [
                'owner_slug' => $ctx['slug'],
                'message' => $e->getMessage(),
            ]);

            return $this->renderPngForUrl($ctx['url'], $ctx['slug'], $forceDownload, $e->getMessage());
        }
    }

    public function hubPrint(Request $request)
    {
        $ctx = $this->ownerHubContext($request);
        if ($ctx === null) abort(404);

        $copies = $this->resolveCopies($request->query('copies'));
        $preset = self::COPY_PRESETS[$copies];

        return view('admin.qr.print', [
            'company' => null,
            'menuUrl' => $ctx['url'],
            'pngUrl' => route('admin.qr.hub.generator', ['format' => 'png']),
            'copies' => $copies,
            'cols' => $preset['cols'],
            'rows' => $preset['rows'],
            'displayName' => $ctx['name'],
        ]);
    }

    public function hubEmail(Request $request)
    {
        $ctx = $this->ownerHubContext($request);
        if ($ctx === null) abort(404);

        $user = $request->user();
        if (! $user || ! $user->email) {
            return response()->json([
                'ok' => false,
                'message' => 'No hemos podido determinar tu dirección de correo.',
            ], 422);
        }

        $copies = $this->resolveCopies($request->input('copies'));

        try {
            $pdf = $this->buildQrPdfForContext($ctx, $ctx['url'], $copies);
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $binary = $pdf->Output('', 'S');
            $filename = $this->qrFilenameForContext($ctx, $copies, 'pdf');

            // Reutilizamos el mailable con una "company-like" envoltura mínima.
            Mail::to($user->email)->send(new QrCodeMail(
                $this->companyForMail($ctx),
                $binary,
                $filename,
                $copies
            ));

            return response()->json([
                'ok' => true,
                'email' => $user->email,
                'message' => 'Te hemos enviado el QR a ' . $user->email . '.',
            ]);
        } catch (Throwable $e) {
            Log::error('Hub QR email send failed', [
                'owner_slug' => $ctx['slug'],
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el email. Inténtalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    /**
     * QR de un MENÚ individual.
     */
    public function menuQrgenerator(Request $request, Menu $menu)
    {
        $this->authorizeMenu($menu);
        $ctx = $this->menuContext($menu);

        $forceDownload = $request->boolean('download');
        $copies = $this->resolveCopies($request->query('copies'));

        if ($request->query('format') === 'png') {
            return $this->renderPngForUrl($ctx['url'], $ctx['slug'], $forceDownload);
        }

        try {
            $pdf = $this->buildQrPdfForContext($ctx, $ctx['url'], $copies);
            $filename = $this->qrFilenameForContext($ctx, $copies, 'pdf');

            if ($forceDownload && method_exists($this, 'downloadPdfResponse')) {
                return $this->downloadPdfResponse($pdf, $filename);
            }

            return $this->inlinePdfResponse($pdf, $filename, $forceDownload);
        } catch (Throwable $e) {
            Log::warning('Menu QR PDF generation failed, devolviendo PNG de respaldo', [
                'menu_id' => $menu->id,
                'menu_slug' => $menu->slug,
                'message' => $e->getMessage(),
            ]);

            return $this->renderPngForUrl($ctx['url'], $ctx['slug'], $forceDownload, $e->getMessage());
        }
    }

    public function menuPrint(Request $request, Menu $menu)
    {
        $this->authorizeMenu($menu);
        $ctx = $this->menuContext($menu);

        $copies = $this->resolveCopies($request->query('copies'));
        $preset = self::COPY_PRESETS[$copies];

        return view('admin.qr.print', [
            'company' => $menu->company,
            'menuUrl' => $ctx['url'],
            'pngUrl' => route('admin.qr.menu.generator', ['menu' => $menu, 'format' => 'png']),
            'copies' => $copies,
            'cols' => $preset['cols'],
            'rows' => $preset['rows'],
            'displayName' => $ctx['name'],
        ]);
    }

    public function menuEmail(Request $request, Menu $menu)
    {
        $this->authorizeMenu($menu);
        $ctx = $this->menuContext($menu);

        $user = $request->user();
        if (! $user || ! $user->email) {
            return response()->json([
                'ok' => false,
                'message' => 'No hemos podido determinar tu dirección de correo.',
            ], 422);
        }

        $copies = $this->resolveCopies($request->input('copies'));

        try {
            $pdf = $this->buildQrPdfForContext($ctx, $ctx['url'], $copies);
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $binary = $pdf->Output('', 'S');
            $filename = $this->qrFilenameForContext($ctx, $copies, 'pdf');

            Mail::to($user->email)->send(new QrCodeMail(
                $this->companyForMail($ctx),
                $binary,
                $filename,
                $copies
            ));

            return response()->json([
                'ok' => true,
                'email' => $user->email,
                'message' => 'Te hemos enviado el QR a ' . $user->email . '.',
            ]);
        } catch (Throwable $e) {
            Log::error('Menu QR email send failed', [
                'menu_id' => $menu->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el email. Inténtalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    protected function menuContext(Menu $menu): array
    {
        $company = $menu->company;
        return [
            'name' => $menu->name . ' · ' . $company->name,
            'slug' => 'menu-' . $company->slug . '-' . $menu->slug,
            'logoPath' => $this->resolveLogoPath($company),
            'title' => 'QR del menú ' . $menu->name,
            'filenamePrefix' => 'menu-qr',
            'url' => $menu->publicUrl(),
            'kind' => 'menu',
            'brandCompany' => $company,
            'company' => $company,
            'menu' => $menu,
        ];
    }

    protected function authorizeMenu(Menu $menu): void
    {
        $userId = (int) auth()->id();
        if ((int) optional($menu->company)->user_id !== $userId) {
            abort(403);
        }
    }

    /**
     * Devuelve un array contextual para generar QR de una company.
     */
    protected function companyContext(Company $company): array
    {
        return [
            'name' => $company->name,
            'slug' => $company->slug,
            'logoPath' => $this->resolveLogoPath($company),
            'title' => 'Carta QR ' . $company->name,
            'filenamePrefix' => 'carta-qr',
            'url' => $company->publicUrl(),
            'kind' => 'company',
            'company' => $company,
        ];
    }

    /**
     * Devuelve un array contextual para generar QR del hub público del owner
     * autenticado, o null si no se puede resolver.
     */
    protected function ownerHubContext(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) return null;
        $slug = method_exists($user, 'resolveSlug') ? $user->resolveSlug() : ($user->slug ?? null);
        if (! $slug) return null;

        $url = route('public.owner.hub', ['ownerSlug' => $slug]);
        $displayName = $user->name ?: ($user->legal_name ?? 'Mi negocio');

        $brandCompany = $user->companies()->orderBy('id')->first();

        return [
            'name' => $displayName,
            'slug' => 'hub-' . $slug,
            'logoPath' => $brandCompany ? $this->resolveLogoPath($brandCompany) : '',
            'title' => 'QR de ' . $displayName,
            'filenamePrefix' => 'negocio-qr',
            'url' => $url,
            'kind' => 'hub',
            'brandCompany' => $brandCompany,
            'owner_slug' => $slug,
        ];
    }

    /**
     * Mailable necesita una Company-like. Si no tenemos una real, usamos
     * la primera Company del owner como fallback de marca.
     */
    protected function companyForMail(array $ctx): Company
    {
        if (! empty($ctx['brandCompany']) && $ctx['brandCompany'] instanceof Company) {
            $clone = clone $ctx['brandCompany'];
            $clone->name = $ctx['name'];
            return $clone;
        }
        $tmp = new Company();
        $tmp->name = $ctx['name'];
        $tmp->slug = $ctx['slug'];
        return $tmp;
    }

    /**
     * Vista HTML imprimible con N copias del QR en grid.
     * Pensada para abrir en pestaña y pulsar Cmd/Ctrl+P (auto window.print()).
     */
    public function print(Request $request, Company $company)
    {
        $this->authorize('view', $company);

        $copies = $this->resolveCopies($request->query('copies'));
        $preset = self::COPY_PRESETS[$copies];

        return view('admin.qr.print', [
            'company' => $company,
            'menuUrl' => $company->publicUrl(),
            'pngUrl' => route('admin.qrgenerator', ['company' => $company, 'format' => 'png']),
            'copies' => $copies,
            'cols' => $preset['cols'],
            'rows' => $preset['rows'],
            'displayName' => $company->name,
        ]);
    }

    /**
     * Envía el PDF con N copias al email del usuario autenticado.
     * Devuelve JSON para AJAX.
     */
    public function email(Request $request, Company $company)
    {
        $this->authorize('view', $company);

        $user = $request->user();
        if (! $user || ! $user->email) {
            return response()->json([
                'ok' => false,
                'message' => 'No hemos podido determinar tu dirección de correo.',
            ], 422);
        }

        $copies = $this->resolveCopies($request->input('copies'));
        $menuUrl = $company->publicUrl();

        try {
            $pdf = $this->buildQrPdf($company, $menuUrl, $copies);
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $binary = $pdf->Output('', 'S');
            $filename = $this->qrFilename($company, $copies, 'pdf');

            Mail::to($user->email)->send(new QrCodeMail($company, $binary, $filename, $copies));

            return response()->json([
                'ok' => true,
                'email' => $user->email,
                'message' => 'Te hemos enviado el QR a ' . $user->email . '.',
            ]);
        } catch (Throwable $e) {
            Log::error('QR email send failed', [
                'company_id' => $company->id,
                'company_slug' => $company->slug,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el email. Inténtalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    protected function resolveCopies($value): int
    {
        $value = (int) $value;
        return array_key_exists($value, self::COPY_PRESETS) ? $value : 1;
    }

    protected function qrFilename(Company $company, int $copies, string $ext): string
    {
        return $this->qrFilenameForContext($this->companyContext($company), $copies, $ext);
    }

    protected function qrFilenameForContext(array $ctx, int $copies, string $ext): string
    {
        $prefix = $ctx['filenamePrefix'] ?? 'carta-qr';
        $base = $prefix . '-' . preg_replace('/[^a-z0-9\-]+/i', '-', $ctx['slug'] ?? 'qr');
        if ($copies > 1) {
            $base .= '-x' . $copies;
        }
        return $base . '.' . $ext;
    }

    /**
     * Construye el PDF de QR. Si $copies === 1 mantiene el layout clásico
     * (logo + título + 1 QR centrado + nombre). Si > 1 imprime un grid.
     */
    protected function buildQrPdf(Company $company, string $menuUrl, int $copies): TCPDF
    {
        return $this->buildQrPdfForContext($this->companyContext($company), $menuUrl, $copies);
    }

    protected function buildQrPdfForContext(array $ctx, string $menuUrl, int $copies): TCPDF
    {
        $pdf = $this->newPdf($ctx['title'] ?? 'QR');
        $pdf->setCellHeightRatio(0.8);
        $pdf->SetFont('helvetica', '', 20);
        $pdf->AddPage();

        if ($copies === 1) {
            $this->writeSinglePageForContext($pdf, $ctx, $menuUrl);
        } else {
            $this->writeGridPageForContext($pdf, $ctx, $menuUrl, $copies);
        }

        return $pdf;
    }

    protected function writeSinglePageForContext(TCPDF $pdf, array $ctx, string $menuUrl): void
    {
        $logoPath = $ctx['logoPath'] ?? '';
        $logoSrc = $logoPath !== '' ? '@' . $logoPath : '';
        $kind = $ctx['kind'] ?? '';
        $sub = match ($kind) {
            'hub' => 'NUESTRA CARTA Y MENÚS',
            'menu' => mb_strtoupper(($ctx['menu']->name ?? 'MENÚ'), 'UTF-8'),
            default => 'NUESTRA CARTA',
        };

        $html = '
        <p></p>
        <p style="text-align:center;">' . ($logoSrc !== '' ? '<img src="' . $logoSrc . '" height="60">' : '') . '</p>
        <p style="font-weight:bold;font-size:20px;text-align:center;">ESCANEA</p>
        <p style="font-size:15px;text-align:center;">' . e($sub) . '</p>';
        $pdf->writeHTML($html, true, false, true, false, '');

        $style = [
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1,
        ];

        $pdf->write2DBarcode($menuUrl, 'QRCODE,Q', 17, 72, 175, 175, $style, 'N');

        $html = '
        <p></p>
        <p style="font-weight:bold;font-size:20px;text-align:center;">' . e($ctx['name'] ?? '') . '</p>
        <p style="font-size:15px;text-align:center;">webnu.es</p>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    /**
     * Imprime un grid de QRs con etiqueta inferior. A4 = 210x297 mm.
     */
    protected function writeGridPageForContext(TCPDF $pdf, array $ctx, string $menuUrl, int $copies): void
    {
        $preset = self::COPY_PRESETS[$copies];
        $cols = $preset['cols'];
        $rows = $preset['rows'];

        // Márgenes y geometría
        $pageW = 210.0;
        $pageH = 297.0;
        $marginX = 15.0;
        $marginY = 15.0;
        $gutter = 6.0;

        $cellW = ($pageW - 2 * $marginX - ($cols - 1) * $gutter) / $cols;
        $cellH = ($pageH - 2 * $marginY - ($rows - 1) * $gutter) / $rows;

        $style = [
            'border' => 1,
            'vpadding' => 1,
            'hpadding' => 1,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1,
        ];

        $labelH = min(8.0, $cellH * 0.18);
        $qrSize = min($cellW, $cellH - $labelH) - 4;

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $x0 = $marginX + $c * ($cellW + $gutter);
                $y0 = $marginY + $r * ($cellH + $gutter);

                $qrX = $x0 + ($cellW - $qrSize) / 2;
                $qrY = $y0 + 2;
                $pdf->write2DBarcode($menuUrl, 'QRCODE,Q', $qrX, $qrY, $qrSize, $qrSize, $style, 'N');

                $pdf->SetXY($x0, $qrY + $qrSize + 1);
                $pdf->SetFont('helvetica', 'B', $copies >= 12 ? 7 : 9);
                $pdf->Cell($cellW, 4, e($ctx['name'] ?? ''), 0, 2, 'C');
                $pdf->SetFont('helvetica', '', $copies >= 12 ? 6 : 7);
                $pdf->Cell($cellW, 3, 'webnu.es', 0, 0, 'C');
            }
        }
    }

    /**
     * Devuelve el QR como PNG, sin depender de TCPDF/PDF.
     */
    protected function renderPngFallback(string $menuUrl, Company $company, ?string $errorContext = null, bool $forceDownload = false)
    {
        return $this->renderPngForUrl($menuUrl, $company->slug, $forceDownload, $errorContext);
    }

    protected function renderPngForUrl(string $url, string $slugForFile, bool $forceDownload = false, ?string $errorContext = null)
    {
        try {
            if (! class_exists(TCPDF2DBarcode::class)) {
                throw new \RuntimeException('TCPDF2DBarcode no disponible.');
            }

            $barcode = new TCPDF2DBarcode($url, 'QRCODE,Q');
            $png = $barcode->getBarcodePngData(10, 10, [0, 0, 0]);

            if ($png === false || $png === '') {
                throw new \RuntimeException('TCPDF2DBarcode devolvió contenido vacío.');
            }

            $filename = 'carta-qr-' . preg_replace('/[^a-z0-9\-]+/i', '-', $slugForFile) . '.png';
            $disposition = ($forceDownload ? 'attachment' : 'inline') . '; filename="' . $filename . '"';

            return response($png, 200)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', $disposition)
                ->header('Cache-Control', 'public, max-age=300');
        } catch (Throwable $e) {
            Log::error('QR PNG fallback failed', [
                'slug' => $slugForFile,
                'context' => $errorContext,
                'message' => $e->getMessage(),
            ]);

            $message = 'No se pudo generar el código QR. Verifica los permisos del directorio storage/ y que la extensión PHP «gd» esté instalada en el servidor. Si el problema continúa, escríbenos al soporte.';

            return redirect()
                ->route('admin.dashboard')
                ->with('flash_warning', $message);
        }
    }

    protected function newPdf(string $title): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name', 'Webnu'));
        $pdf->SetAuthor(config('app.name', 'Webnu'));
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        return $pdf;
    }

    protected function resolveLogoPath(Company $company): string
    {
        if ($company->logo) {
            $path = public_path('img/' . ltrim($company->logo, '/'));
            if (is_file($path) && $this->isReadableImage($path)) {
                return $this->normalizePath($path);
            }
        }

        foreach (['logo', 'isotipo', 'favicon'] as $brandKey) {
            $path = \App\PlatformSetting::brandPath($brandKey);
            if (is_file($path) && $this->isReadableImage($path)) {
                return $this->normalizePath($path);
            }
        }

        return '';
    }

    protected function isReadableImage(string $path): bool
    {
        if (! is_readable($path)) {
            return false;
        }

        $size = @getimagesize($path);

        return is_array($size);
    }

    protected function normalizePath(string $path): string
    {
        $resolved = realpath($path) ?: $path;

        return str_replace('\\', '/', $resolved);
    }
}
