<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class ClusterController extends Controller
{
    public function kMeanBatchUpdate(KMeanBatchUpdateAdminInterface $kMeanBatchUpdate,int $k)
    {
        try{
        $kMeanBatchUpdate->recalcClusters($k);
        return '<h1 style="color:green;">clusters updated successfully</h1>';
        }
        catch(Exception $e)
        {
            return '<h1 style="color:red;">Failed to update the clusters!! </h1>'.$e;
        }

    }
}
