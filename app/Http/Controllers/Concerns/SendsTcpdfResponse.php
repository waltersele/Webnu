<?php

namespace App\Http\Controllers\Concerns;

use TCPDF;

trait SendsTcpdfResponse
{
    protected function inlinePdfResponse(TCPDF $pdf, string $filename, bool $download = false)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $binary = $pdf->Output($filename, 'S');
        $disposition = ($download ? 'attachment' : 'inline') . '; filename="' . str_replace('"', '', $filename) . '"';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    protected function downloadPdfResponse(TCPDF $pdf, string $filename)
    {
        return $this->inlinePdfResponse($pdf, $filename, true);
    }
}
