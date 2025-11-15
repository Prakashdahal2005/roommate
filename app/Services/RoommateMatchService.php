<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RoommateMatchService implements RoommateMatchServiceInterface, KMeanBatchUpdateAdminInterface
{
    private array $featureRanges = [];
    private array $globalDefaults = [];
    private array $featureWeights = [];

    public function __construct()
    {
        $profiles = Profile::all();
        if ($profiles->isNotEmpty()) {
            $this->globalDefaults = [
                'age' => (int) round($profiles->avg('age')),
                'gender' => 1,
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

    public function findMatches(Profile $profile, int $limit = 50): Collection
    {
        $profiles = Profile::all();
        if ($profiles->isEmpty()) return collect();

        $this->computeFeatureRanges($profiles);

        $completion_score = $profile->completion_score;
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
         * 3. Assign user to nearest cluster
         * ------------------------------------------------- */
        $bestCluster = $clusterDistances[0]['cluster'];
        $profile->cluster_id = $bestCluster->id;
        $profile->save();

        /* -------------------------------------------------
         * 4. Start collecting matches cluster-by-cluster
         * ------------------------------------------------- */
        $collected = collect();

        foreach ($clusterDistances as $clusterInfo) {
            if ($collected->count() >= $limit) break;

            $clusterId = $clusterInfo['cluster']->id;

            $clusterUsers = Profile::where('cluster_id', $clusterId)
                ->where('id', '<>', $profile->id)
                ->get();

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


    public function recalcClusters(int $k = 4): void
    {
        // Acquire a lock for up to 10 minutes
        $lock = Cache::lock('kmeans_lock', 600);

        if (!$lock->get()) {
            // Someone else is running it
            throw new \Exception("Cluster recalculation in progress. Try again later.");
        }

        try {
            // ---- YOUR ORIGINAL CODE STARTS ----

            $profiles = Profile::all();
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

            Profile::query()->update(['cluster_id' => null]);
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

                foreach ($clusterSamples as $sample) {
                    $profileId = array_search($sample, $samples, true);
                    if ($profileId !== false) {
                        Profile::where('id', $profileId)->update(['cluster_id' => $clusterId]);
                    }
                }
            }


            sleep(120);

        } finally {
            // release lock even if something breaks
            $lock->release();
        }
    }


    /* ---------------------- Helpers ---------------------- */

    private function profileToVector(Profile $profile, bool $fillNulls = false): array
    {
        $genderMap = ['male' => 0, 'female' => 1, 'other' => 2];
        $cleanlinessMap = ['very_clean' => 3, 'clean' => 2, 'average' => 1, 'messy' => 0];
        $scheduleMap = ['morning_person' => 2, 'flexible' => 1, 'night_owl' => 0];

        return [
            $profile->age ?? ($fillNulls ? $this->globalDefaults['age'] : 0),
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
            $min = $this->featureRanges[$i]['min'];
            $max = $this->featureRanges[$i]['max'];
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
}
