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
    $results = $kMeanBatchUpdate->evaluateClustering();

    return view('admin.dashboard', compact('results'));
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
