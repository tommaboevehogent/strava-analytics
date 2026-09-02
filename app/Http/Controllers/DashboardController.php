<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Session-authenticated web UI on top of the existing /api/activities data —
 * a place to actually click through your trainings instead of only reading
 * raw JSON via curl/Postman. Deliberately a thin, read-only view layer: the
 * API (Api\ActivityController) stays the source of truth for querying.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'type' => ['sometimes', 'string'],
        ]);

        $type = $request->query('type');

        $activities = Activity::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('started_at')
            ->paginate(15)
            ->withQueryString();

        $types = Activity::query()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('trainingen.index', [
            'activities' => $activities,
            'types' => $types,
            'activeType' => $type,
        ]);
    }

    public function show(Activity $activity): View
    {
        return view('trainingen.show', ['activity' => $activity]);
    }
}
