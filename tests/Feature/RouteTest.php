<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_loads()
    {
        $this->get('/')->assertOk();
    }

    /** @test */
    public function about_page_loads()
    {
        $this->get('/about')->assertStatus(200);
    }

    /** @test */
    public function contact_page_loads()
    {
        $this->get('/contact')->assertStatus(200);
    }

    /** @test */
    // public function guest_can_view_public_profile_show()
    // {
    //     $profile = Profile::factory()->create();

    //     $this->get('/profiles/' . $profile->id)
    //          ->assertStatus(200);
    // }
}
