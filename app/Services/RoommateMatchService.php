<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RoommateMatchService implements RoommateMatchServiceInterface, KMeanBatchUpdateAdminInterface
{
    private array $featureRanges = [];
    private array $globalDefaults = [];
    private array $featureWeights = [];

    public function __construct()
    {
        $profiles = Profile::with('user')->get();
        if ($profiles->isNotEmpty()) {
            $ages = $profiles->pluck('user.age')->filter();
            $this->globalDefaults = [
                'age' => $ages->isNotEmpty() ? (int) round($ages->avg()) : 25,
                'gender' => rand(0, 2),
                'budget_min' => (int) round($profiles->avg('budget_min')),
                'budget_max' => (int) round($profiles->avg('budget_max')),
                'cleanliness' => 1,
                'schedule' => 1,
                'smokes' => 0,
                'pets_ok' => 0,
            ];
        }

        $rawWeights = [0.2, 0.25, 0.15, 0.15, 0.15, 0.05, 0.03, 0.02];
        $sum = array_sum($rawWeights) ?: 1;
        foreach ($rawWeights as $i => $w) {
            $this->featureWeights[$i] = $w / $sum;
        }
    }

    public function findMatches(Profile $profile, int $limit = 50, ?Request $request = null): Collection
    {
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) return collect();

        $this->computeFeatureRanges($profiles);

        $userVector = $this->applyFeatureWeights(
            $this->normalizeVector($this->profileToVector($profile, true))
        );

        $clusters = DB::table('clusters')->get();
        if ($clusters->isEmpty()) return collect();

        $clusterDistances = [];
        foreach ($clusters as $cluster) {
            $centroid = json_decode($cluster->vector, true);
            $clusterDistances[] = [
                'cluster' => $cluster,
                'distance' => $this->euclideanDistance($userVector, $centroid),
            ];
        }

        usort($clusterDistances, fn($a, $b) => $a['distance'] <=> $b['distance']);
        $collected = collect();

        foreach ($clusterDistances as $clusterInfo) {
            if ($collected->count() >= $limit) break;
            $clusterUsersQuery = Profile::where('cluster_id', $clusterInfo['cluster']->id)
                ->where('id', '<>', $profile->id)
                ->with('user');

            if ($request) {
                $clusterUsersQuery = $this->applyFilters($clusterUsersQuery, $request);
            }

            $clusterUsers = $clusterUsersQuery->get();
            foreach ($clusterUsers as $p) {
                if ($collected->count() >= $limit) break;
                $vector = $this->applyFeatureWeights(
                    $this->normalizeVector($this->profileToVector($p, true))
                );
                $distance = $this->euclideanDistance($userVector, $vector);
                $similarity = max(0, min(100, (1 - min($distance, 1)) * 100));

                // Budget adjustment
                $overlapMin = max($profile->budget_min, $p->budget_min);
                $overlapMax = min($profile->budget_max, $p->budget_max);
                $budgetAdjustment = ($overlapMax >= $overlapMin)
                    ? ($overlapMax - $overlapMin) / max($profile->budget_max - $profile->budget_min, 1)
                    : -min(1, max($p->budget_min - $profile->budget_max, $profile->budget_min - $p->budget_max) / max($profile->budget_max - $profile->budget_min, 1));

                $similarity += $budgetAdjustment * 30;
                $similarity = max(0, min(100, $similarity));

                $p->setAttribute('similarity', round($similarity, 2));
                $collected->push($p);
            }
        }

        return $collected->sortByDesc('similarity')->take($limit)->values();
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('age_min')) {
            $query->whereHas('user', fn($q) => $q->where('age', '>=', $request->age_min));
        }
        if ($request->filled('age_max')) {
            $query->whereHas('user', fn($q) => $q->where('age', '<=', $request->age_max));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('budget_max')) {
            $query->where('budget_max', '<=', $request->budget_max);
        }
        return $query;
    }

    public function recalcClusters(int $k = 4): void
    {
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) return;

        $this->computeFeatureRanges($profiles);
        $samples = [];
        foreach ($profiles as $profile) {
            $samples[$profile->id] = $this->applyFeatureWeights($this->normalizeVector($this->profileToVector($profile, true)));
        }

        $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
        $clusters = $kmeans->cluster(array_values($samples));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clusters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($clusters as $clusterSamples) {
            $centroid = $this->computeCentroid($clusterSamples);
            DB::table('clusters')->insert([
                'vector' => json_encode($centroid),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function profileToVector(Profile $profile, bool $fillNulls = false): array
    {
        $genderMap = ['male' => 0, 'female' => 1, 'other' => 2];
        $cleanlinessMap = ['very_clean' => 3, 'clean' => 2, 'average' => 1, 'messy' => 0];
        $scheduleMap = ['morning_person' => 2, 'flexible' => 1, 'night_owl' => 0];

        $age = $profile->user->age ?? ($fillNulls ? $this->globalDefaults['age'] : 0);
        return [
            $age,
            $genderMap[$profile->gender] ?? ($fillNulls ? $this->globalDefaults['gender'] : 0),
            $profile->budget_min ?? ($fillNulls ? $this->globalDefaults['budget_min'] : 0),
            $profile->budget_max ?? ($fillNulls ? $this->globalDefaults['budget_max'] : 0),
            $cleanlinessMap[$profile->cleanliness] ?? ($fillNulls ? $this->globalDefaults['cleanliness'] : 1),
            $scheduleMap[$profile->schedule] ?? ($fillNulls ? $this->globalDefaults['schedule'] : 1),
            $profile->smokes ? 1 : ($fillNulls ? $this->globalDefaults['smokes'] : 0),
            $profile->pets_ok ? 1 : ($fillNulls ? $this->globalDefaults['pets_ok'] : 0),
        ];
    }

    private function normalizeVector(array $vector): array
    {
        $normalized = [];
        foreach ($vector as $i => $val) {
            $min = $this->featureRanges[$i]['min'] ?? 0;
            $max = $this->featureRanges[$i]['max'] ?? 0;
            $normalized[$i] = $max > $min ? ($val - $min) / ($max - $min) : 0;
        }
        return $normalized;
    }

    private function applyFeatureWeights(array $vector): array
    {
        return array_map(fn($val, $i) => ($val * ($this->featureWeights[$i] ?? 1)), $vector, array_keys($vector));
    }

    private function computeFeatureRanges($profiles): void
    {
        $vectors = array_map(fn($p) => $this->profileToVector($p, true), $profiles->all());
        $numFeatures = count($vectors[0]);
        $ranges = [];
        for ($i = 0; $i < $numFeatures; $i++) {
            $col = array_column($vectors, $i);
            $ranges[$i] = ['min' => min($col), 'max' => max($col)];
        }
        $this->featureRanges = $ranges;
    }

    private function computeCentroid(array $samples): array
    {
        $numSamples = count($samples);
        if ($numSamples === 0) return [];
        $numFeatures = count($samples[0]);
        $centroid = array_fill(0, $numFeatures, 0);
        foreach ($samples as $sample) {
            foreach ($sample as $i => $val) $centroid[$i] += $val;
        }
        return array_map(fn($v) => $v / $numSamples, $centroid);
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        foreach ($a as $i => $val) $sum += ($val - ($b[$i] ?? 0)) ** 2;
        return sqrt($sum);
    }

    public function evaluateClustering(bool $existingClusters = false): array
    {
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) {
            return ['optimal_k' => null, 'warning' => 'No profiles to evaluate.'];
        }

        $this->computeFeatureRanges($profiles);
        $samples = [];
        foreach ($profiles as $p) {
            $samples[$p->id] = $this->applyFeatureWeights($this->normalizeVector($this->profileToVector($p, true)));
        }

        $profileIds = array_keys($samples);
        $numProfiles = count($samples);
        $topN = 10;
        $baselineMatches = [];
        foreach ($profiles as $p) {
            $baselineMatches[$p->id] = $this->findMatches($p, $topN)->pluck('id')->toArray();
        }

        $maxK = min(15, $numProfiles - 1); // allow wider K search
        $silhouettes = [];
        $precisionScores = [];
        $performanceGains = [];
        $bestK = 2;
        $bestScore = -INF;

        for ($k = 2; $k <= $maxK; $k++) {

            if (!$existingClusters) {
                $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
                $clusters = $kmeans->cluster(array_values($samples));

                $clusterMap = [];
                foreach ($clusters as $cluster) {
                    $clusterIds = [];
                    foreach ($cluster as $vec) {
                        foreach ($samples as $pid => $v) {
                            if ($v === $vec) $clusterIds[] = $pid;
                        }
                    }
                    $clusterMap[] = $clusterIds;
                }
            } else {
                $dbClusters = DB::table('clusters')->get();
                $clusterMap = [];
                foreach ($dbClusters as $dbCluster) {
                    $centroid = json_decode($dbCluster->vector, true);
                    $clusterIds = [];
                    foreach ($samples as $pid => $vec) {
                        if ($this->euclideanDistance($vec, $centroid) < 0.5) $clusterIds[] = $pid;
                    }
                    $clusterMap[] = $clusterIds;
                }
            }

            // Silhouette
            $sums = [];
            foreach ($clusterMap as $cluster) {
                foreach ($cluster as $i => $pidA) {
                    $vecA = $samples[$pidA];
                    $a = count($cluster) > 1 ? array_sum(array_map(fn($pidB) => $pidA === $pidB ? 0 : $this->euclideanDistance($vecA, $samples[$pidB]), $cluster)) / (count($cluster) - 1) : 0;
                    $b = INF;
                    foreach ($clusterMap as $otherCluster) {
                        if ($otherCluster === $cluster) continue;
                        $avgDist = count($otherCluster) ? array_sum(array_map(fn($pidB) => $this->euclideanDistance($vecA, $samples[$pidB]), $otherCluster)) / count($otherCluster) : INF;
                        $b = min($b, $avgDist);
                    }
                    $sums[] = ($b - $a) / max($a, $b, 1e-9);
                }
            }
            $silhouettes[$k] = round(count($sums) ? array_sum($sums) / count($sums) : 0, 3);

            // Precision
            $precisionSum = 0;
            foreach ($profiles as $p) {
                $topClusterMatches = collect();
                foreach ($clusterMap as $cluster) {
                    if (in_array($p->id, $cluster)) {
                        foreach ($cluster as $otherId) if ($otherId !== $p->id) $topClusterMatches->push($otherId);
                    }
                }
                $topClusterMatches = $topClusterMatches->unique()->take($topN)->toArray();
                $precisionSum += count(array_intersect($topClusterMatches, $baselineMatches[$p->id])) / $topN;
            }
            $precisionScores[$k] = round($precisionSum / $numProfiles * 100, 2);

            // Performance
            $totalPairs = $numProfiles * ($numProfiles - 1);
            $clusterPairs = array_sum(array_map(fn($c) => count($c) * (count($c) - 1), $clusterMap));
            $performanceGains[$k] = round(100 * (1 - $clusterPairs / max($totalPairs, 1)), 2);

            $score = ($silhouettes[$k] ?? 0) + ($precisionScores[$k] ?? 0)/100;
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestK = $k;
            }
        }

        $warning = null;
        if (($silhouettes[$bestK] ?? 0) < 0.25 || ($precisionScores[$bestK] ?? 0) < 50) {
            $warning = 'KMeans++ clustering is not significantly preserving top matches or improving speed.';
        }

        return [
            'optimal_k' => $bestK,
            'silhouette_scores' => $silhouettes,
            'precision_gain' => $precisionScores[$bestK] ?? null,
            'performance_gain' => $performanceGains[$bestK] ?? null,
            'warning' => $warning,
        ];
    }
}
