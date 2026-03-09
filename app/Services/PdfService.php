<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Download a Blade view as PDF
     *
     * @param string $view Blade view name
     * @param array $data Data to pass to view
     * @param string $filename File name to download
     * @param string $orientation 'portrait' or 'landscape'
     */
    public function download(string $view, array $data = [], string $filename = 'document.pdf', string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($view, $data)
                  ->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
