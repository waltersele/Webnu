<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Concerns\SendsTcpdfResponse;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\UserPlanService;
use TCPDF;

class MenuPrintController extends Controller
{
    use SendsTcpdfResponse;

    public function printPdf(Company $company, MenuService $menuService, UserPlanService $plans)
    {
        $this->authorize('view', $company);
        $plans->assertCanUsePdfMenu(auth()->user());

        if ((int) $company->menu_type === 2 && $company->menu_type_2_pdf) {
            $pdfPath = public_path('img/' . ltrim($company->menu_type_2_pdf, '/'));
            if (is_file($pdfPath)) {
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="carta-' . $company->slug . '.pdf"',
                ]);
            }
        }

        $sections = $menuService->sectionsForCompany($company)
            ->filter(function ($section) {
                return (bool) $section->enabled;
            });

        $html = $this->buildMenuHtml($company, $sections);
        $pdf = $this->newPdf('Carta ' . $company->name);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'carta-' . preg_replace('/[^a-z0-9\-]+/i', '-', $company->slug) . '.pdf';

        return $this->inlinePdfResponse($pdf, $filename);
    }

    protected function buildMenuHtml(Company $company, $sections): string
    {
        $logoHtml = '';
        $logoPath = $this->imagePathForTcpdf($company->logo);
        if ($logoPath !== '') {
            $logoHtml = '<p style="text-align:center;"><img src="@' . $logoPath . '" height="48"></p>';
        }

        $html = $logoHtml
            . '<h1 style="text-align:center;font-size:22px;margin-bottom:4px;">' . e($company->name) . '</h1>'
            . '<p style="text-align:center;font-size:10px;color:#666;margin-bottom:16px;">'
            . e(route('see_menu', $company->slug))
            . '</p>';

        foreach ($sections as $section) {
            $products = $section->products->filter(function ($product) {
                return (bool) $product->enabled;
            });

            if ($products->isEmpty()) {
                continue;
            }

            $html .= '<h2 style="font-size:14px;border-bottom:1px solid #ddd;padding-bottom:4px;margin:14px 0 8px;">'
                . e($section->name) . '</h2>';

            foreach ($products as $product) {
                $price = $this->formatProductPrice($product);
                $desc = trim((string) $product->description);
                if (strlen($desc) > 180) {
                    $desc = substr($desc, 0, 177) . '…';
                }

                $html .= '<table cellpadding="2" cellspacing="0" style="width:100%;margin-bottom:6px;">'
                    . '<tr>'
                    . '<td style="width:72%;vertical-align:top;">'
                    . '<strong style="font-size:11px;">' . e($product->name) . '</strong>';

                if ($desc !== '') {
                    $html .= '<br><span style="font-size:9px;color:#555;">' . e($desc) . '</span>';
                }

                $html .= '</td>'
                    . '<td style="width:28%;text-align:right;vertical-align:top;font-size:11px;white-space:nowrap;">'
                    . ($price !== '' ? e($price) : '')
                    . '</td>'
                    . '</tr></table>';
            }
        }

        $publishedCount = 0;
        foreach ($sections as $section) {
            $publishedCount += $section->products->filter(function ($product) {
                return (bool) $product->enabled;
            })->count();
        }

        if ($publishedCount === 0) {
            $html .= '<p style="text-align:center;color:#666;margin-top:24px;">Aún no hay platos publicados en tu carta.</p>';
        }

        $html .= '<p style="text-align:center;font-size:8px;color:#999;margin-top:20px;">Generado con Webnu.es · ' . e(now()->format('d/m/Y')) . '</p>';

        return $html;
    }

    protected function formatProductPrice($product): string
    {
        $parts = [];
        if ($product->price_unit) {
            $parts[] = $product->price_unit . ' €';
        }
        if ($product->price_portion) {
            $parts[] = $product->price_portion . ' € / ración';
        }

        return implode(' · ', $parts);
    }

    protected function newPdf(string $title): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name', 'Webnu'));
        $pdf->SetAuthor(config('app.name', 'Webnu'));
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(14, 14, 14);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->SetFont('helvetica', '', 10);

        return $pdf;
    }

    protected function imagePathForTcpdf(?string $relative): string
    {
        if (!$relative) {
            return '';
        }

        $path = public_path('img/' . ltrim($relative, '/'));
        if (!is_file($path)) {
            return '';
        }

        $resolved = realpath($path) ?: $path;

        return str_replace('\\', '/', $resolved);
    }

}
