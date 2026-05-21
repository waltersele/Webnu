<?php

namespace App\Http\Controllers\Concerns;

use TCPDF;

trait SendsTcpdfResponse
{
    protected function inlinePdfResponse(TCPDF $pdf, string $filename)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $binary = $pdf->Output($filename, 'S');

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $filename) . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}
