<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RoommateMatchService implements RoommateMatchServiceInterface, KMeanBatchUpdateAdminInterface
{
    private array $featureRanges = [];
    private array $globalDefaults = [];
    private array $featureWeights = [];

    public function __construct()
    {
        // Load users relation because age lives on user
        $profiles = Profile::with('user')->get();
        if ($profiles->isNotEmpty()) {
            $ages = $profiles->pluck('user.age')->filter(); // remove nulls

            $this->globalDefaults = [
                'age' => $ages->isNotEmpty() ? (int) round($ages->avg()) : 25,
                'gender' => rand(1, 2),
                'budget_min' => (int) round($profiles->avg('budget_min')),
                'budget_max' => (int) round($profiles->avg('budget_max')),
                'cleanliness' => 1,
                'schedule' => 1,
                'smokes' => 0,
                'pets_ok' => 0,
            ];
        }

        $rawWeights = [
            0 => 0.20,
            1 => 0.25,
            2 => 0.15,
            3 => 0.15,
            4 => 0.15,
            5 => 0.05,
            6 => 0.03,
            7 => 0.02,
        ];

        $sum = array_sum($rawWeights) ?: 1;
        foreach ($rawWeights as $i => $w) {
            $this->featureWeights[$i] = $w / $sum;
        }
    }

    public function findMatches(Profile $profile, int $limit = 50, ?Request $request = null): Collection
    {
        // load profiles with user to ensure age available without extra queries
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) return collect();

        $this->computeFeatureRanges($profiles);

        $completion_score = $profile->completion_score ?? 1;
        $userProfile = clone $profile;

        $userVector = $this->applyFeatureWeights(
            $this->normalizeVector($this->profileToVector($userProfile, true))
        );

        $clusters = DB::table('clusters')->get();
        if ($clusters->isEmpty()) {
            return collect();
        }

        /* -------------------------------------------------
         * 1. Compute distance to ALL centroids
         * ------------------------------------------------- */
        $clusterDistances = [];
        foreach ($clusters as $cluster) {
            $centroid = json_decode($cluster->vector, true);
            $distance = $this->euclideanDistance($userVector, $centroid);
            $clusterDistances[] = [
                'cluster' => $cluster,
                'distance' => $distance
            ];
        }

        /* -------------------------------------------------
         * 2. Sort clusters by distance ascending
         * ------------------------------------------------- */
        usort($clusterDistances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        /* -------------------------------------------------
         * 3. Assign user to nearest cluster (in-memory only — NO DB write)
         * ------------------------------------------------- */
        $bestCluster = $clusterDistances[0]['cluster'] ?? null;
        // DO NOT write $profile->cluster_id or save to DB (explicitly requested no profile/user mutation)

        /* -------------------------------------------------
         * 4. Start collecting matches cluster-by-cluster WITH FILTERS
         * ------------------------------------------------- */
        $collected = collect();

        foreach ($clusterDistances as $clusterInfo) {
            if ($collected->count() >= $limit) break;

            $clusterId = $clusterInfo['cluster']->id;

            // Apply filters at database level to reduce search cost.
            // Age filters must be applied on related user.
            $clusterUsersQuery = Profile::where('cluster_id', $clusterId)
                ->where('id', '<>', $profile->id)
                ->with('user');

            // Apply filters if provided
            if ($request) {
                $clusterUsersQuery = $this->applyFilters($clusterUsersQuery, $request);
            }

            $clusterUsers = $clusterUsersQuery->get();

            foreach ($clusterUsers as $p) {
                if ($collected->count() >= $limit) break;

                $pClone = clone $p;
                $vector = $this->applyFeatureWeights(
                    $this->normalizeVector($this->profileToVector($pClone, true))
                );

                $distance = $this->euclideanDistance($userVector, $vector);
                $similarity = (1 - min($distance, 1)) * $completion_score * 100;

                // --- Improved budget adjustment ---
                $budgetMin1 = $userProfile->budget_min;
                $budgetMax1 = $userProfile->budget_max;
                $budgetMin2 = $pClone->budget_min;
                $budgetMax2 = $pClone->budget_max;
                $budgetAdjustment = 0;

                if ($budgetMin1 !== null && $budgetMax1 !== null && $budgetMin2 !== null && $budgetMax2 !== null) {

                    $overlapMin = max($budgetMin1, $budgetMin2);
                    $overlapMax = min($budgetMax1, $budgetMax2);

                    if ($overlapMax >= $overlapMin) {
                        $budgetAdjustment = ($overlapMax - $overlapMin) / max($budgetMax1 - $budgetMin1, 1);
                    } else {
                        $distance2 = max($budgetMin2 - $budgetMax1, $budgetMin1 - $budgetMax2);
                        $rangeSize = max($budgetMax1 - $budgetMin1, 1);
                        $budgetAdjustment = -min(1, $distance2 / $rangeSize);
                    }
                }

                $similarity += $budgetAdjustment * 30;
                $similarity = max(0, min(100, $similarity));

                $p->setAttribute('similarity', round($similarity, 2));
                $collected->push($p);
            }
        }

        /* -------------------------------------------------
         * 5. Sort + final limit
         * ------------------------------------------------- */
        return $collected
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();
    }

    /**
     * Apply filters to reduce search space
     *
     * Note: Profile has no age column now — age lives on related user.
     */
    private function applyFilters($query, Request $request)
    {
        // Age filter: filter via related user
        if ($request->filled('age_min')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('age', '>=', $request->age_min);
            });
        }

        if ($request->filled('age_max')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('age', '<=', $request->age_max);
            });
        }

        // Gender filter (still on profile)
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Budget filter
        if ($request->filled('budget_max')) {
            $query->where('budget_max', '<=', $request->budget_max);
        }

        return $query;
    }

    public function recalcClusters(int $k = 4): void
    {
        // Load profiles with user relation since age is on user
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) return;

        $this->computeFeatureRanges($profiles);

        $samples = [];
        foreach ($profiles as $profile) {
            $normalized = $this->normalizeVector($this->profileToVector(clone $profile, true));
            $weighted = $this->applyFeatureWeights($normalized);
            $samples[$profile->id] = $weighted;
        }

        $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
        $clusters = $kmeans->cluster(array_values($samples));

        // NOTE: Do not mutate Profile rows (explicitly avoid updating profile->cluster_id).
        // We still rebuild the clusters table (if desired); preserve original behavior on clusters table.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clusters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($clusters as $clusterSamples) {

            $centroid = $this->computeCentroid($clusterSamples);

            $clusterId = DB::table('clusters')->insertGetId([
                'vector' => json_encode($centroid),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // DO NOT write cluster_id back to profiles (respecting "no profile/user mutation" requirement)
            // If you later want to persist mapping, do it to a separate mapping table or call a different admin task.
        }
    }

    /* ---------------------- Helpers ---------------------- */

    private function profileToVector(Profile $profile, bool $fillNulls = false): array
    {
        $genderMap = ['male' => 0, 'female' => 1, 'other' => 2];
        $cleanlinessMap = ['very_clean' => 3, 'clean' => 2, 'average' => 1, 'messy' => 0];
        $scheduleMap = ['morning_person' => 2, 'flexible' => 1, 'night_owl' => 0];

        // ALWAYS take age from the related user. Do not mutate models.
        $age = $profile->user->age ?? null;

        if ($age === null && $fillNulls) {
            $age = $this->globalDefaults['age'] ?? 0;
        }

        if ($age === null) {
            $age = 0;
        }

        return [
            $age,
            $genderMap[$profile->gender] ?? ($fillNulls ? $this->globalDefaults['gender'] : 1),
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
        $weighted = [];
        foreach ($vector as $i => $val) {
            $w = $this->featureWeights[$i] ?? 0;
            $weighted[$i] = $val * $w;
        }
        return $weighted;
    }

    private function computeFeatureRanges($profiles): void
    {
        // Ensure profiles are provided with user relation to compute age ranges properly
        $vectors = array_map(fn($p) => $this->profileToVector($p, true), $profiles->all());
        $numFeatures = count($vectors[0]);
        $ranges = [];
        for ($i = 0; $i < $numFeatures; $i++) {
            $col = array_column($vectors, $i);
            $ranges[$i] = [
                'min' => min($col),
                'max' => max($col),
            ];
        }
        $this->featureRanges = $ranges;
    }

    private function computeCentroid(array $samples): array
    {
        $numSamples = count($samples);
        if ($numSamples === 0) return [];

        $validSamples = array_filter($samples, fn($s) => is_array($s) && !empty($s));
        $numValid = count($validSamples);
        if ($numValid === 0) return [];

        $first = reset($validSamples);
        $numFeatures = count($first);
        $centroid = array_fill(0, $numFeatures, 0);

        foreach ($validSamples as $sample) {
            if (count($sample) !== $numFeatures) continue;
            foreach ($sample as $i => $val) {
                $centroid[$i] += $val;
            }
        }

        return array_map(fn($v) => $v / $numValid, $centroid);
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        foreach ($a as $i => $val) {
            $sum += ($val - ($b[$i] ?? 0)) ** 2;
        }
        return sqrt($sum);
    }

    /**
     * Evaluate KMeans++ clustering vs pure Euclidean
     * Returns optimal k, silhouette scores, precision & performance gains, and warning if clustering is ineffective.
     */
    public function evaluateClustering(): array
    {
        // Load profiles with user relation
        $profiles = Profile::with('user')->get();
        if ($profiles->isEmpty()) {
            return [
                'optimal_k' => null,
                'warning' => 'No profiles available to evaluate.',
            ];
        }

        $this->computeFeatureRanges($profiles);

        // Prepare in-memory weighted & normalized vectors (cloned)
        $samples = [];
        foreach ($profiles as $p) {
            $vector = $this->applyFeatureWeights(
                $this->normalizeVector($this->profileToVector(clone $p, true))
            );
            $samples[$p->id] = $vector;
        }

        $maxK = min(8, count($samples) - 1); // avoid k > N
        $silhouettes = [];
        $precisionGains = [];
        $performanceGains = [];
        $bestK = null;
        $bestScore = -INF;

        $allProfileVectors = array_values($samples);

        // Baseline: pure Euclidean (top 10 closest for each profile)
        $baselineTopN = 10;
        $baselineMatches = [];
        foreach ($allProfileVectors as $i => $vecA) {
            $distances = [];
            foreach ($allProfileVectors as $j => $vecB) {
                if ($i === $j) continue;
                $distances[] = $this->euclideanDistance($vecA, $vecB);
            }
            sort($distances);
            $baselineMatches[$i] = array_slice($distances, 0, $baselineTopN);
        }

        for ($k = 2; $k <= $maxK; $k++) {
            $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
            $clusters = $kmeans->cluster(array_values($samples));

            // Compute silhouette score (simple approximation)
            $sums = [];
            foreach ($clusters as $cluster) {
                foreach ($cluster as $idxA => $vecA) {
                    // a = avg intra-cluster distance
                    $a = 0;
                    foreach ($cluster as $idxB => $vecB) {
                        if ($idxA === $idxB) continue;
                        $a += $this->euclideanDistance($vecA, $vecB);
                    }
                    $a = count($cluster) > 1 ? $a / (count($cluster) - 1) : 0;

                    // b = min avg distance to other clusters
                    $b = INF;
                    foreach ($clusters as $otherCluster) {
                        if ($otherCluster === $cluster) continue;
                        $avgDist = 0;
                        foreach ($otherCluster as $vecB) {
                            $avgDist += $this->euclideanDistance($vecA, $vecB);
                        }
                        $avgDist = count($otherCluster) ? $avgDist / count($otherCluster) : INF;
                        $b = min($b, $avgDist);
                    }

                    $sums[] = ($b - $a) / max($a, $b, 1e-9); // avoid div by 0
                }
            }

            $silhouette = count($sums) ? array_sum($sums) / count($sums) : 0;
            $silhouettes[$k] = round($silhouette, 3);

            // Compute precision@10 with cluster filtering vs baseline
            $precisionSum = 0;
            $clusterVecs = array_values($samples);
            $collectedCount = 0;
            foreach ($clusters as $cluster) {
                $clusterIds = array_map(fn($v) => array_search($v, $samples, true), $cluster);
                foreach ($clusterIds as $i) {
                    $vecA = $samples[$i];
                    $distances = [];
                    foreach ($cluster as $vecB) {
                        if ($vecA === $vecB) continue;
                        $distances[] = $this->euclideanDistance($vecA, $vecB);
                    }
                    sort($distances);
                    $topN = array_slice($distances, 0, $baselineTopN);
                    // compare with baseline
                    $baseline = $baselineMatches[$i] ?? [];
                    $matchCount = 0;
                    foreach ($topN as $val) {
                        if (in_array($val, $baseline, true)) $matchCount++;
                    }
                    $precisionSum += $matchCount / $baselineTopN;
                    $collectedCount++;
                }
            }
            $precision = $collectedCount ? $precisionSum / $collectedCount : 0;
            $precisionGains[$k] = round($precision * 100, 2);

            // performance gain = avg reduction in search space
            $totalPairs = count($allProfileVectors) * (count($allProfileVectors) - 1);
            $clusterPairs = 0;
            foreach ($clusters as $cluster) {
                $n = count($cluster);
                $clusterPairs += $n * ($n - 1);
            }
            $performanceGains[$k] = round(100 * (1 - $clusterPairs / max($totalPairs, 1)), 2);

            // pick best silhouette for optimal k
            if ($silhouette > $bestScore) {
                $bestScore = $silhouette;
                $bestK = $k;
            }
        }

        // Determine if KMeans++ is actually useful
        $warning = null;
        if (
            $bestScore < 0.15 ||
            ($precisionGains[$bestK] ?? 0) < 5 ||
            ($performanceGains[$bestK] ?? 0) < 20
        ) {
            $warning = 'KMeans++ clustering is not improving similarity or speed significantly. Consider disabling it.';
        }

        return [
            'optimal_k' => $bestK,
            'silhouette_scores' => $silhouettes,
            'precision_gain' => $precisionGains[$bestK] ?? null,
            'performance_gain' => $performanceGains[$bestK] ?? null,
            'warning' => $warning,
        ];
    }
}
