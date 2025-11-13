<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Support\Collection;

class RoommateMatchService implements RoommateMatchServiceInterface, KMeanBatchUpdateAdminInterface
{
    private array $featureRanges = [];
    private array $globalDefaults = [];

    public function __construct()
    {
        // Compute global statistics to fill nulls during algorithm
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
    }

    /**
     * Find top N matches for a user based on Euclidean distance
     */

    public function findMatches(Profile $profile, int $limit = 50): Collection
    {
        $profiles = Profile::all();
        if ($profiles->isEmpty()) return collect();

        $this->computeFeatureRanges($profiles);

        // Clone profile to avoid mutating DB object
        $userProfile = clone $profile;
        $userVector = $this->normalizeVector($this->profileToVector($userProfile, true));

        $clusters = DB::table('clusters')->get();

        $bestCluster = null;
        $bestScore = INF;
        foreach ($clusters as $cluster) {
            $centroid = json_decode($cluster->vector, true);
            $distance = $this->euclideanDistance($userVector, $centroid);
            if ($distance < $bestScore) {
                $bestScore = $distance;
                $bestCluster = $cluster;
            }
        }

        // Assign cluster_id to user
        $profile->cluster_id = $bestCluster?->id;
        $profile->save();

        // Get other profiles in the same cluster
        $clusterUsers = Profile::where('cluster_id', $bestCluster?->id)
            ->where('id', '<>', $profile->id)
            ->get();

        // Map similarity and attach to profile instance
        $matchedProfiles = $clusterUsers->map(function ($p) use ($userVector) {
            $pClone = clone $p;
            $vector = $this->normalizeVector($this->profileToVector($pClone, true));
            $distance = $this->euclideanDistance($userVector, $vector);
            $similarity = (1 - min($distance, 1)) * 100; // percentage
            $p->setAttribute('similarity', round($similarity, 2)); // attach similarity
            return $p;
        })->sortByDesc('similarity')->take($limit);

        return $matchedProfiles->values(); // return as Collection of Profile instances
    }


    /**
     * Admin method: Recalculate KMeans clusters and store in clusters table
     */
    public function recalcClusters(int $k = 5): void
    {
        $profiles = Profile::all();
        if ($profiles->isEmpty()) return;

        $this->computeFeatureRanges($profiles);

        $samples = [];
        foreach ($profiles as $profile) {
            $samples[$profile->id] = $this->normalizeVector($this->profileToVector(clone $profile, true));
        }

        // KMeans++ clustering
        $kmeans = new KMeans($k, KMeans::INIT_KMEANS_PLUS_PLUS);
        $clusters = $kmeans->cluster(array_values($samples));

        // First, set all cluster_ids to NULL
        Profile::query()->update(['cluster_id' => null]);

        // Clear existing clusters
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clusters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Save centroids and update profile cluster_ids
        foreach ($clusters as $clusterIndex => $clusterSamples) {
            $centroid = $this->computeCentroid($clusterSamples);

            // Insert centroid and get the auto-generated ID
            $clusterId = DB::table('clusters')->insertGetId([
                'vector' => json_encode($centroid),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign profiles to cluster
            foreach ($clusterSamples as $sample) {
                $profileId = array_search($sample, $samples);
                Profile::where('id', $profileId)->update(['cluster_id' => $clusterId]);
            }
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
