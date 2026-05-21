<?php



namespace App\Http\Controllers\Admin;



use App\Company;

use App\Http\Controllers\Concerns\SendsTcpdfResponse;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;

use TCPDF;

use Throwable;



class QrController extends Controller

{

    use SendsTcpdfResponse;



    public function qrgenerator(Company $company)

    {

        $this->authorize('view', $company);



        try {

            $menuUrl = route('see_menu', $company->slug);

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

            Log::error('QR PDF generation failed', [

                'company_id' => $company->id,

                'message' => $e->getMessage(),

            ]);



            return redirect()

                ->route('admin.dashboard')

                ->with('flash', 'No se pudo generar el código QR. Inténtalo de nuevo o usa el enlace de descarga en imagen desde el onboarding.');

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

        if (!is_readable($path)) {

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


