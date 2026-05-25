<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Concerns\SendsTcpdfResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TCPDF;
use TCPDF2DBarcode;
use Throwable;

class QrController extends Controller
{
    use SendsTcpdfResponse;

    public function qrgenerator(Request $request, Company $company)
    {
        $this->authorize('view', $company);

        $menuUrl = route('see_menu', $company->slug);

        // Si el usuario pide PNG explícitamente, o si el PDF falla en runtime,
        // entregamos un QR como imagen PNG (más robusto y sin depender de GD ricos).
        if ($request->query('format') === 'png') {
            return $this->renderPngFallback($menuUrl, $company);
        }

        try {
            $logoPath = $this->resolveLogoPath($company);
            $logoSrc = $logoPath !== '' ? '@' . $logoPath : '';

            $html = '
            <p></p>
            <p style="text-align:center;">' . ($logoSrc !== '' ? '<img src="' . $logoSrc . '" height="60">' : '') . '</p>
            <p style="font-weight:bold;font-size:20px;text-align:center;">ESCANEA</p>
            <p style="font-size:15px;text-align:center;">NUESTRA CARTA</p>';

            $pdf = $this->newPdf('Carta QR ' . $company->name);
            $pdf->setCellHeightRatio(0.8);
            $pdf->SetFont('helvetica', '', 20);
            $pdf->AddPage();
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
            <p style="font-weight:bold;font-size:20px;text-align:center;">' . e($company->name) . '</p>
            <p style="font-size:15px;text-align:center;">webnu.es</p>';
            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'carta-qr-' . preg_replace('/[^a-z0-9\-]+/i', '-', $company->slug) . '.pdf';

            return $this->inlinePdfResponse($pdf, $filename);
        } catch (Throwable $e) {
            Log::warning('QR PDF generation failed, devolviendo PNG de respaldo', [
                'company_id' => $company->id,
                'company_slug' => $company->slug,
                'message' => $e->getMessage(),
                'trace_first' => $e->getTraceAsString() ? substr($e->getTraceAsString(), 0, 800) : null,
            ]);

            return $this->renderPngFallback($menuUrl, $company, $e->getMessage());
        }
    }

    /**
     * Devuelve el QR como PNG, sin depender de TCPDF/PDF.
     * Si todo falla, redirige con un mensaje claro.
     */
    protected function renderPngFallback(string $menuUrl, Company $company, ?string $errorContext = null)
    {
        try {
            if (! class_exists(TCPDF2DBarcode::class)) {
                throw new \RuntimeException('TCPDF2DBarcode no disponible.');
            }

            $barcode = new TCPDF2DBarcode($menuUrl, 'QRCODE,Q');
            $png = $barcode->getBarcodePngData(10, 10, [0, 0, 0]);

            if ($png === false || $png === '') {
                throw new \RuntimeException('TCPDF2DBarcode devolvió contenido vacío.');
            }

            $filename = 'carta-qr-' . preg_replace('/[^a-z0-9\-]+/i', '-', $company->slug) . '.png';

            return response($png, 200)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (Throwable $e) {
            Log::error('QR PNG fallback failed', [
                'company_id' => $company->id,
                'company_slug' => $company->slug,
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
