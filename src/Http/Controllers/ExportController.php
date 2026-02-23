<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Reno\Dashboard\Actions\ExportWidget;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected ExportWidget $exportWidget,
    ) {}

    public function __invoke(Request $request, string $key): StreamedResponse
    {
        $formatRaw = $request->input('format', 'csv');
        $format = is_string($formatRaw) ? $formatRaw : 'csv';

        return $this->exportWidget->execute($key, $format);
    }
}
