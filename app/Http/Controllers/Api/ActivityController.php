<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'type' => ['sometimes', 'string'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $activities = Activity::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('from'), fn ($q) => $q->where('started_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('started_at', '<=', $request->date('to')))
            ->orderByDesc('started_at')
            ->paginate($request->integer('per_page', 25));

        return ActivityResource::collection($activities);
    }

    public function show(Activity $activity): JsonResponse
    {
        return response()->json(new ActivityResource($activity));
    }
}
