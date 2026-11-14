<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_safe_user_fields_are_sent_to_the_browser(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);

        $this->actingAs($user)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.email', $user->email)
            ->where('auth.user.has_password', true)
            ->missing('auth.user.password')
            ->missing('auth.user.google_id')
            ->missing('auth.user.remember_token')
            ->missing('user.google_id')
            ->missing('user.password'));
    }

    public function test_google_only_user_can_set_a_first_password(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user)->from('/profile')->put('/password', [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->refresh()->password));
    }

    public function test_google_only_user_can_delete_their_account_by_typing_delete(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user)->from('/profile')->delete('/profile', ['confirmation' => 'nope'])
            ->assertSessionHasErrors('confirmation');
        $this->assertNotNull($user->fresh());

        $this->actingAs($user)->delete('/profile', ['confirmation' => 'DELETE'])->assertRedirect('/');
        $this->assertNull($user->fresh());
    }

    public function test_password_user_still_needs_their_password_to_delete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->delete('/profile', ['confirmation' => 'DELETE'])
            ->assertSessionHasErrors('password');
        $this->assertNotNull($user->fresh());
    }
}
