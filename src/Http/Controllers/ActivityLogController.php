<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Recent member-management activity: state transitions, role syncs,
     * role CRUD, and profile submissions/resubmissions. Trait-level CRUD
     * noise (log_name `default`) is excluded; it stays in the audit trail.
     */
    public function index(Request $request): View
    {
        $activities = Activity::query()
            ->whereIn('log_name', ['member_management', 'role_management', 'profile'])
            ->with(['causer', 'subject'])
            ->latest()
            ->simplePaginate(20);

        return view('alumkit::activity-log.index', [
            'activities' => $activities,
        ]);
    }
}
