<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_update_their_own_profile()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Old bio',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('profiles.update'), [
            'bio' => 'New bio',
        ]);

        $response->assertRedirect(route('profiles.show', $profile))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'bio' => 'New bio',
        ]);
    }
}
