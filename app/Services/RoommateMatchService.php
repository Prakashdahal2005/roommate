<?php

namespace App\Services;

use App\Contracts\KMeanBatchUpdateAdminInterface;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use App\Contracts\RoommateMatchServiceInterface;

class RoommateMatchService implements RoommateMatchServiceInterface,KMeanBatchUpdateAdminInterface
{
    private array $featureRanges = [];

    /**
     * Find top N matches for a user based on cosine similarity
     */
    public function findMatches(Profile $profile, int $limit = 50): array
    {
        $profiles = Profile::all();
        if ($profiles->isEmpty()) return [];

        $this->computeFeatureRanges($profiles);

        // Normalize user vector
        $userVector = $this->normalizeVector($this->profileToVector($profile));

        // Get clusters directly from table
        $clusters = DB::table('clusters')->get();

        // Find nearest cluster centroid
        $bestCluster = null;
        $bestScore = -1;
        foreach ($clusters as $cluster) {
            $centroid = json_decode($cluster->vector, true);
            $similarity = $this->cosineSimilarity($userVector, $centroid);
            if ($similarity > $bestScore) {
                $bestScore = $similarity;
                $bestCluster = $cluster;
            }
        }

        // Assign cluster_id to user
        if ($bestCluster) {
    $profile->cluster_id = $bestCluster->id;
    $profile->save();
} else {
    $profile->cluster_id = null;
    $profile->save();
}

        // Get other profiles in the same cluster
        $clusterUsers = Profile::where('cluster_id', $bestCluster->id)
            ->where('id', '<>', $profile->id)
            ->get();

        // Rank by cosine similarity
        $ranked = $clusterUsers->map(function ($profile) use ($userVector) {
            $vector = $this->normalizeVector($this->profileToVector($profile));
            return [
                'profile' => $profile,
                'similarity' => $this->cosineSimilarity($userVector, $vector),
            ];
        })->sortByDesc('similarity')->take($limit);

        return $ranked->pluck('profile')->values()->all();
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
        $samples[$profile->id] = $this->normalizeVector($this->profileToVector($profile));
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
    $clusterMap = []; // Map cluster indices to actual database IDs
    foreach ($clusters as $clusterIndex => $clusterSamples) {
        $centroid = $this->computeCentroid($clusterSamples);

        // Insert centroid and get the auto-generated ID
        $clusterId = DB::table('clusters')->insertGetId([
            'vector' => json_encode($centroid),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clusterMap[$clusterIndex] = $clusterId;

        // Assign profiles to cluster
        foreach ($clusterSamples as $sample) {
            $profileId = array_search($sample, $samples);
            Profile::where('id', $profileId)->update(['cluster_id' => $clusterId]);
        }
    }
}

    /* ---------------------- Helpers ---------------------- */

    private function profileToVector(Profile $profile): array
    {
        $weights = [
            'age' => 0.2,
            'gender' => 0.1,
            'budget_min' => 0.2,
            'budget_max' => 0.2,
            'cleanliness' => 0.15,
            'schedule' => 0.1,
            'smokes' => 0.025,
            'pets_ok' => 0.025,
        ];

        $genderMap = ['male' => 0, 'female' => 1, 'other' => 2];
        $cleanlinessMap = ['very_clean' => 3, 'clean' => 2, 'average' => 1, 'messy' => 0];
        $scheduleMap = ['morning_person' => 2, 'flexible' => 1, 'night_owl' => 0];

        $vector = [
            $profile->age ?? 0,
            $genderMap[$profile->gender] ?? 1,
            $profile->budget_min ?? 0,
            $profile->budget_max ?? 0,
            $cleanlinessMap[$profile->cleanliness] ?? 1,
            $scheduleMap[$profile->schedule] ?? 1,
            $profile->smokes ? 1 : 0,
            $profile->pets_ok ? 1 : 0,
        ];

        // Apply weights
        $weighted = [];
        foreach ($vector as $i => $val) {
            $weighted[$i] = $val * array_values($weights)[$i];
        }

        return $weighted;
    }

    private function computeFeatureRanges($profiles): void
    {
        $vectors = array_map(fn($p) => $this->profileToVector($p), $profiles->all());
        $numFeatures = count($vectors[0]);
        $ranges = [];

        for ($i = 0; $i < $numFeatures; $i++) {
            $col = array_column($vectors, $i);
            $ranges[$i] = [
                'min' => min($col),
                'max' => max($col)
            ];
        }

        $this->featureRanges = $ranges;
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

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0; $normA = 0; $normB = 0;
        foreach ($a as $i => $val) {
            $dot += $val * ($b[$i] ?? 0);
            $normA += $val ** 2;
            $normB += ($b[$i] ?? 0) ** 2;
        }
        return ($normA && $normB) ? $dot / (sqrt($normA) * sqrt($normB)) : 0;
    }

    private function computeCentroid(array $samples): array
{
    $numSamples = count($samples);
    
    // Handle completely empty clusters
    if ($numSamples === 0) {
        return [];
    }
    
    // Filter out any empty or invalid samples
    $validSamples = array_filter($samples, function($sample) {
        return is_array($sample) && !empty($sample);
    });
    
    $numValidSamples = count($validSamples);
    
    if ($numValidSamples === 0) {
        return [];
    }
    
    // Use the first valid sample to determine feature count
    $firstSample = reset($validSamples);
    $numFeatures = count($firstSample);
    
    $centroid = array_fill(0, $numFeatures, 0);

    foreach ($validSamples as $sample) {
        // Ensure each sample has the same number of features
        if (count($sample) !== $numFeatures) {
            continue;
        }
        
        foreach ($sample as $i => $val) {
            $centroid[$i] += $val;
        }
    }

    return array_map(fn($v) => $v / $numValidSamples, $centroid);
}
}
