<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClusterController extends Controller
{
    public function kMeanBatchUpdate(KMeanBatchUpdateAdminInterface $kMeanBatchUpdate)
    {
        $kMeanBatchUpdate->recalcClusters(5);
    }
}
