<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use PDF;

class QrController extends Controller
{
    public function qrgenerator(Company $company)
    {
        $this->authorize('view', $company);

        if ($company->logo) {
            $companyLogoPath = '/img/' . $company->logo;
        } else {
            $companyLogoPath = '/adminlte/img/webnu.png';
        }

        $html = '
        <p></p>
        <p style="text-align:center;"><img src="' . $companyLogoPath . '" height="60" style=""></p>
        <p style="font-weight:bold;font-size:20px;text-align:center;">ESCANEA</p>
        <p style="font-size:15px;text-align:center;">NUESTRA CARTA</p>';

        PDF::SetTitle('Carta QR ' . $company->name);
        PDF::setCellHeightRatio(0.8);
        PDF::SetFont('helvetica', '', 20);
        PDF::AddPage();
        PDF::writeHTML($html, true, false, true, false, '');

        $style = [
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1,
        ];

        PDF::write2DBarcode(URL::to('/') . '/carta/' . $company->slug, 'QRCODE,Q', 17, 72, 175, 175, $style, 'N');

        $html = '
        <p></p>
        <p style="font-weight:bold;font-size:20px;text-align:center;">' . e($company->name) . '</p>
        <p style="font-size:15px;text-align:center;">webnu.es</p>';
        PDF::writeHTML($html, true, false, true, false, '');

        PDF::Output('carta_qr.pdf');
    }
}
