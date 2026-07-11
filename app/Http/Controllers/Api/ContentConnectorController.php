<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentConnector\ContentConnectorPostRequest;
use App\Services\ContentConnectorService;
use Illuminate\Http\JsonResponse;

class ContentConnectorController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function index(ContentConnectorService $connector): JsonResponse
    {
        return response()->json([
            'posts' => $connector->listPostsForConnector(),
        ]);
    }

    public function categories(ContentConnectorService $connector): JsonResponse
    {
        return response()->json([
            'categories' => $connector->listCategories(),
        ]);
    }

    public function store(ContentConnectorPostRequest $request, ContentConnectorService $connector): JsonResponse
    {
        $result = $connector->upsertFromPayload($request->validated());

        return response()->json($result, 201);
    }

    public function update(
        ContentConnectorPostRequest $request,
        ContentConnectorService $connector,
        int|string $id
    ): JsonResponse {
        $result = $connector->updateByTranslationId($id, $request->validated());

        return response()->json($result);
    }
}
