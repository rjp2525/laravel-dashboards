<?php

namespace Reno\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Reno\Dashboard\Actions\LoadDashboardLayout;
use Reno\Dashboard\Actions\SaveDashboardLayout;
use Reno\Dashboard\Http\Requests\SaveLayoutRequest;
use Reno\Dashboard\Http\Resources\LayoutResource;

class LayoutController extends Controller
{
    public function __construct(
        protected LoadDashboardLayout $loadLayout,
        protected SaveDashboardLayout $saveLayout,
    ) {}

    public function show(Request $request, string $slug): LayoutResource
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $layout = $this->loadLayout->execute($user, $slug);

        return new LayoutResource($layout);
    }

    public function update(SaveLayoutRequest $request, string $slug): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        /** @var array<int, array<string, mixed>> $layout */
        $layout = (array) $request->validated('layout');

        /** @var array<string, mixed>|null $filters */
        $filters = $request->validated('filters') !== null ? (array) $request->validated('filters') : null;

        $userDashboard = $this->saveLayout->execute(
            $user,
            $slug,
            $layout,
            $filters,
        );

        return response()->json([
            'message' => 'Layout saved successfully.',
            'layout' => $userDashboard->layout,
        ]);
    }
}
