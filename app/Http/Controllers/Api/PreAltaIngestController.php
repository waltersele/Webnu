<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreAlta\PreAltaIngestRequest;
use App\Services\PreAlta\PreAltaIngestService;
use Illuminate\Http\JsonResponse;

class PreAltaIngestController extends Controller
{
    public function store(PreAltaIngestRequest $request, PreAltaIngestService $ingestService): JsonResponse
    {
        try {
            $result = $ingestService->ingest($request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result, 201);
    }
}
