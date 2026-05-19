<?php

namespace App\Contracts;

use App\Services\MenuScan\MenuScanResult;

interface MenuScanProvider
{
    /**
     * @param array<int, string> $absoluteFilePaths Rutas absolutas a imágenes o PDF
     */
    public function scan(array $absoluteFilePaths): MenuScanResult;

    public function name(): string;
}
