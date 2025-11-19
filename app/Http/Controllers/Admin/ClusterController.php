<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClusterController extends Controller
{
    // Show dashboard (read-only evaluation)
    public function index(KMeanBatchUpdateAdminInterface $kMeanBatchUpdate)
{
    // Evaluate new optimal K based on current data
    $results = $kMeanBatchUpdate->evaluateClustering();

    // Fetch current clusters metrics (precision & performance) if clusters exist
    $currentClusters = DB::table('clusters')->get();
    $showRecalc = true;

    if ($currentClusters->isNotEmpty()) {
        // Evaluate the current clusters **without changing DB**
        $currentMetrics = $kMeanBatchUpdate->evaluateClustering(true);

        // Only show the button if the new evaluation is better
        $showRecalc = ($results['precision_gain'] ?? 0) > ($currentMetrics['precision_gain'] ?? 0)
                   && ($results['performance_gain'] ?? 0) > ($currentMetrics['performance_gain'] ?? 0);
    }

    return view('admin.dashboard', compact('results', 'showRecalc'));
}


    // Recalculate clusters only, do NOT re-evaluate
    public function kMeanBatchUpdate(KMeanBatchUpdateAdminInterface $kMeanBatchUpdate, int $k)
    {
        try {
            $kMeanBatchUpdate->recalcClusters($k); // just rebuild clusters table

            return redirect()
                ->route('admin.dashboard')
                ->with('status', "✅ Clusters updated successfully with K = $k.");
        } catch (Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('status', "❌ Failed to update clusters: " . $e->getMessage());
        }
    }
}
