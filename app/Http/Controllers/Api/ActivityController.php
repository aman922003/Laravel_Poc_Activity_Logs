<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    /**
     * Paginated activity logs for admin.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 50);

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Show single activity item
     */
    public function show($id)
    {
        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);
        return response()->json($activity);
    }
}
