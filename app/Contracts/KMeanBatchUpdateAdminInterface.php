<?php
namespace App\Contracts;
interface KMeanBatchUpdateAdminInterface
{
    public function recalcClusters(int $k):void;
    public function evaluateClustering(): array;
}