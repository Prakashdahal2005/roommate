<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomepageGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_random_users_to_guest()
    {
        // Create 50 profiles so there is enough data
        Profile::factory()->count(30)->create();

        // Guest (not logged in) visits homepage
        $response = $this->get('/');

        // Assert page loads correctly
        $response->assertStatus(200);

        // Assert the view receives "profiles"
        $response->assertViewHas('profiles');

        // Assert exactly 30 profiles are sent to the view
        $profiles = $response->viewData('profiles');
        $this->assertCount(30, $profiles);
    }
}
