<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

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
                'move_in_lat' => $profiles->avg('move_in_lat') ?? 27.7172,
                'move_in_lng' => $profiles->avg('move_in_lng') ?? 85.3240,
                'move_in_date' => 0,
            ];
        }

        $rawWeights = [
            0.20, // age
            0.20, // gender
            0.12, // budget_min
            0.12, // budget_max
            0.10, // cleanliness
            0.05, // schedule
            0.03, // smokes
            0.03, // pets_ok
            0.08, // move_in_lat
            0.08, // move_in_lng
            0.09  // move_in_date
        ];
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
            $samples[$profile->id] = $this->applyFeatureWeights(
                $this->normalizeVector($this->profileToVector($profile, true))
            );
        }

        $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
        $clusters = $kmeans->cluster(array_values($samples));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clusters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($clusters as $clusterVectors) {
            $centroid = $this->computeCentroid($clusterVectors);
            $clusterRecordId = DB::table('clusters')->insertGetId([
                'vector' => json_encode($centroid),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($clusterVectors as $vector) {
                $profileId = array_search($vector, $samples, true);
                if ($profileId !== false) {
                    Profile::where('id', $profileId)->update(['cluster_id' => $clusterRecordId]);
                }
            }
        }
    }

    private function profileToVector(Profile $profile, bool $fillNulls = false): array
    {
        $genderMap = ['male' => 0, 'female' => 1, 'other' => 2];
        $cleanlinessMap = ['very_clean' => 3, 'clean' => 2, 'average' => 1, 'messy' => 0];
        $scheduleMap = ['morning_person' => 2, 'flexible' => 1, 'night_owl' => 0];

        $age = $profile->user->age ?? ($fillNulls ? $this->globalDefaults['age'] : 0);
        $moveInDate = $profile->move_in_date
            ? max(0, Carbon::now()->diffInDays(Carbon::parse($profile->move_in_date)))
            : ($fillNulls ? $this->globalDefaults['move_in_date'] : 0);

        return [
            $age,
            $genderMap[$profile->gender] ?? ($fillNulls ? $this->globalDefaults['gender'] : 0),
            $profile->budget_min ?? ($fillNulls ? $this->globalDefaults['budget_min'] : 0),
            $profile->budget_max ?? ($fillNulls ? $this->globalDefaults['budget_max'] : 0),
            $cleanlinessMap[$profile->cleanliness] ?? ($fillNulls ? $this->globalDefaults['cleanliness'] : 1),
            $scheduleMap[$profile->schedule] ?? ($fillNulls ? $this->globalDefaults['schedule'] : 1),
            $profile->smokes ? 1 : ($fillNulls ? $this->globalDefaults['smokes'] : 0),
            $profile->pets_ok ? 1 : ($fillNulls ? $this->globalDefaults['pets_ok'] : 0),
            $profile->move_in_lat ?? ($fillNulls ? $this->globalDefaults['move_in_lat'] : 0),
            $profile->move_in_lng ?? ($fillNulls ? $this->globalDefaults['move_in_lng'] : 0),
            $moveInDate
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

        $samples = array_filter($samples, fn($s) => is_array($s) && count($s) > 0);
        if (empty($samples)) return [];

        $numFeatures = count($samples[array_key_first($samples)]);
        $centroid = array_fill(0, $numFeatures, 0);

        foreach ($samples as $sample) {
            foreach ($sample as $i => $val) {
                $centroid[$i] += $val;
            }
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
    $minPrecision = 55;
    $profiles = Profile::with('user')->orderBy('id')->get();
    if ($profiles->isEmpty()) {
        return ['optimal_k' => null, 'precision' => null, 'performance_gain' => null];
    }

    $this->computeFeatureRanges($profiles);

    $samples = [];
    $profileIds = [];
    foreach ($profiles as $p) {
        $samples[] = $this->applyFeatureWeights($this->normalizeVector($this->profileToVector($p, true)));
        $profileIds[] = $p->id;
    }

    $numProfiles = count($samples);
    if ($numProfiles < 2) {
        return ['optimal_k' => null, 'precision' => null, 'performance_gain' => null];
    }

    $topN = min(10, $numProfiles - 1);

    // --- Baseline: exhaustive top-N distances ---
    $baselineMatches = [];
    $baselineComputations = 0;
    for ($i = 0; $i < $numProfiles; $i++) {
        $dists = [];
        for ($j = 0; $j < $numProfiles; $j++) {
            if ($i === $j) continue;
            $dists[$profileIds[$j]] = $this->euclideanDistance($samples[$i], $samples[$j]);
            $baselineComputations++;
        }
        asort($dists);
        $baselineMatches[$profileIds[$i]] = array_slice(array_keys($dists), 0, $topN);
    }

    $maxK = min(15, $numProfiles - 1);
    $validKs = [];

    for ($k = 2; $k <= $maxK; $k++) {
        $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
        $clusters = $kmeans->cluster($samples);

        $centroids = [];
        foreach ($clusters as $clusterVectors) {
            $centroids[] = $this->computeCentroid($clusterVectors);
        }
        if (empty($centroids)) $centroids[] = $this->computeCentroid($samples);

        // Assign each profile to nearest cluster
        $clusterAssignments = [];
        foreach ($samples as $idx => $vec) {
            $bestD = INF;
            $bestC = 0;
            foreach ($centroids as $cIdx => $centroid) {
                $d = $this->euclideanDistance($vec, $centroid);
                if ($d < $bestD) {
                    $bestD = $d;
                    $bestC = $cIdx;
                }
            }
            $clusterAssignments[$idx] = $bestC;
        }

        // Map cluster -> members
        $clusterMap = [];
        foreach ($clusterAssignments as $idx => $cIdx) {
            $clusterMap[$cIdx][] = $profileIds[$idx];
        }

        $precisionSum = 0;
        $distComputations = 0;

        foreach ($profileIds as $pid) {
            $clusterId = $clusterAssignments[array_search($pid, $profileIds)];
            $members = $clusterMap[$clusterId];
            $members = array_diff($members, [$pid]);

            // Compute distances only for top-N within cluster
            $memberDists = [];
            foreach ($members as $mId) {
                $idx = array_search($mId, $profileIds);
                $memberDists[$mId] = $this->euclideanDistance($samples[array_search($pid, $profileIds)], $samples[$idx]);
                $distComputations++;
            }
            asort($memberDists);
            $retrieved = array_slice(array_keys($memberDists), 0, $topN);

            $truePos = count(array_intersect($retrieved, $baselineMatches[$pid]));
            $precisionSum += $truePos / max($topN, 1);
        }

        $avgPrecision = ($precisionSum / $numProfiles) * 100;

        // Rational performance gain relative to baseline
        $performanceGain = round(100 * (1 - ($distComputations / $baselineComputations)), 2);

        if ($avgPrecision >= $minPrecision) {
            $validKs[$k] = [
                'precision' => round($avgPrecision, 2),
                'performance_gain' => $performanceGain,
            ];
        }
    }

    if (!empty($validKs)) {
        $optimalK = max(array_keys($validKs));
        return [
            'optimal_k' => $optimalK,
            'precision' => $validKs[$optimalK]['precision'],
            'performance_gain' => $validKs[$optimalK]['performance_gain'],
        ];
    }

    // fallback
    return [
        'optimal_k' => 2,
        'precision' => round($avgPrecision ?? 0, 2),
        'performance_gain' => $performanceGain ?? 0,
    ];
}

}
