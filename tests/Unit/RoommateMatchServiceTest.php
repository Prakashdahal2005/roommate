<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Profile;
use App\Services\RoommateMatchService;

class RoommateMatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_matches_returns_empty_when_no_profiles()
    {
        $service = new RoommateMatchService();
        $profile = Profile::factory()->make(); // Not saved, so DB is empty

        $matches = $service->findMatches($profile);

        $this->assertEmpty($matches, 'Expected no matches when no profiles exist.');
    }

    public function test_profile_to_vector_fills_defaults()
    {
        // Create the service without any profiles in DB
        $service = new RoommateMatchService();

        $profile = Profile::factory()->make([
            'age' => null,
            'gender' => null,
            'budget_min' => null,
            'budget_max' => null,
            'cleanliness' => null,
            'schedule' => null,
            'smokes' => null,
            'pets_ok' => null,
        ]);

        $vector = $this->invokePrivateMethod($service, 'profileToVector', [$profile, true]);

        $this->assertIsArray($vector);
        $this->assertCount(8, $vector);
        $this->assertNotNull($vector[0]); // age default
        $this->assertNotNull($vector[1]); // gender default
    }

    public function test_apply_feature_weights_and_normalize_vector()
    {
        $service = new RoommateMatchService();

        // Fake some feature ranges
        $this->invokePrivateProperty($service, 'featureRanges', [
            ['min' => 0, 'max' => 100], // age
            ['min' => 0, 'max' => 2],   // gender
            ['min' => 0, 'max' => 1000], // budget_min
            ['min' => 0, 'max' => 2000], // budget_max
            ['min' => 0, 'max' => 3], // cleanliness
            ['min' => 0, 'max' => 2], // schedule
            ['min' => 0, 'max' => 1], // smokes
            ['min' => 0, 'max' => 1], // pets_ok
        ]);

        $vector = [25, 1, 500, 1500, 2, 1, 0, 1];

        $normalized = $this->invokePrivateMethod($service, 'normalizeVector', [$vector]);
        $weighted = $this->invokePrivateMethod($service, 'applyFeatureWeights', [$normalized]);

        $this->assertIsArray($normalized);
        $this->assertIsArray($weighted);
        $this->assertCount(8, $weighted);
    }

    public function test_compute_centroid()
    {
        $service = new RoommateMatchService();

        $samples = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ];

        $centroid = $this->invokePrivateMethod($service, 'computeCentroid', [$samples]);

        $this->assertEquals([4, 5, 6], $centroid);
    }

    public function test_euclidean_distance_calculation()
    {
        $service = new RoommateMatchService();

        $a = [1, 2, 3];
        $b = [4, 5, 6];

        $distance = $this->invokePrivateMethod($service, 'euclideanDistance', [$a, $b]);

        $this->assertEqualsWithDelta(5.196, $distance, 0.001);
    }

    /* -------------------- Helper Methods -------------------- */
    private function invokePrivateMethod($object, string $method, array $params = [])
    {
        $reflection = new \ReflectionClass($object);
        $methodRef = $reflection->getMethod($method);
        $methodRef->setAccessible(true);

        return $methodRef->invokeArgs($object, $params);
    }

    private function invokePrivateProperty($object, string $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $propRef = $reflection->getProperty($property);
        $propRef->setAccessible(true);
        $propRef->setValue($object, $value);
    }
}
