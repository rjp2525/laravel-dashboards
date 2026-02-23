<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Reno\Dashboard\Actions\ApplyPreset;
use Reno\Dashboard\Actions\CreatePreset;
use Reno\Dashboard\Http\Requests\PresetRequest;
use Reno\Dashboard\Http\Resources\PresetResource;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;

class PresetController extends Controller
{
    use AuthorizesRequests;

    public function index(string $slug): AnonymousResourceCollection
    {
        $dashboard = Dashboard::where('slug', $slug)->firstOrFail();

        return PresetResource::collection(
            $dashboard->presets()->orderBy('is_system', 'desc')->orderBy('name')->get()
        );
    }

    public function store(PresetRequest $request, string $slug, CreatePreset $createPreset): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $nameRaw = $request->validated('name');
        $name = is_string($nameRaw) ? $nameRaw : '';

        /** @var array<int, array<string, mixed>> $layout */
        $layout = (array) $request->validated('layout');

        $preset = $createPreset->execute(
            $user,
            $slug,
            $name,
            $layout,
        );

        return new PresetResource($preset)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $slug, string $presetId): PresetResource
    {
        $preset = DashboardPreset::findOrFail($presetId);

        return new PresetResource($preset);
    }

    public function update(PresetRequest $request, string $slug, string $presetId): PresetResource
    {
        $preset = DashboardPreset::findOrFail($presetId);

        $this->authorize('update', $preset);

        $preset->update($request->validated());

        return new PresetResource($preset);
    }

    public function destroy(Request $request, string $slug, string $presetId): JsonResponse
    {
        $preset = DashboardPreset::findOrFail($presetId);

        $this->authorize('delete', $preset);

        $preset->delete();

        return response()->json(['message' => 'Preset deleted.']);
    }

    public function apply(Request $request, string $slug, string $presetId, ApplyPreset $applyPreset): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $applyPreset->execute($user, $presetId);

        return response()->json(['message' => 'Preset applied.']);
    }
}
