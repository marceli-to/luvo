<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\Support\Qa;
use Tests\TestCase;

class AuthTest extends TestCase
{
    #[Qa('auth-guest')]
    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/administration', '/administration/home', '/administration/team/member/edit/1', '/administration/foo'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    #[Qa('auth-api')]
    public function test_api_returns_401_json_for_guests(): void
    {
        foreach (['/api/home', '/api/team/members', '/api/files'] as $url) {
            $this->getJson($url)->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
        }
        $this->putJson('/api/home/1', [])->assertUnauthorized();
        $this->postJson('/api/image/upload')->assertUnauthorized();
    }

    #[Qa('auth-login', 'setup-users')]
    public function test_login(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $admin->email, 'password' => 'qa-password'])->assertRedirect('/administration/home');
        $this->assertAuthenticatedAs($admin);
    }

    #[Qa('auth-role', 'setup-users')]
    public function test_non_admin_cannot_open_the_admin(): void
    {
        $user = $this->user(['role' => 'editor']);

        $this->post('/login', ['email' => $user->email, 'password' => 'qa-password'])->assertRedirect('/home');
        $this->get('/administration')->assertForbidden();
        $this->get('/administration/team/member/edit/1')->assertForbidden();
    }

    #[Qa('auth-role', 'auth-login')]
    public function test_non_admin_login_lands_on_a_page(): void
    {
        $user = $this->user(['role' => 'editor']);
        $this->teams();
        $this->home();

        $this->post('/login', ['email' => $user->email, 'password' => 'qa-password'])->assertRedirect('/home');
        $this->knownFinding('F17', 'a non-admin login redirects to /home, which is a 404', fn () => $this->get('/home')->assertOk());
    }

    #[Qa('auth-role', 'setup-users')]
    public function test_unverified_admin_cannot_open_the_admin(): void
    {
        $user = $this->user(['email_verified_at' => null]);

        $this->actingAs($user)->get('/administration')->assertRedirect('/email/verify');
    }

    #[Qa('auth-role')]
    public function test_non_admin_cannot_use_the_api(): void
    {
        $this->actingAs($this->user(['role' => 'editor']));

        $this->knownFinding('F3', 'the API has no role check', fn () => $this->getJson('/api/home')->assertForbidden());
    }

    #[Qa('auth-role')]
    public function test_unverified_admin_cannot_use_the_api(): void
    {
        $this->actingAs($this->user(['email_verified_at' => null]));

        $this->knownFinding('F3', 'the API has no verified check', fn () => $this->getJson('/api/home')->assertForbidden());
    }

    #[Qa('auth-disabled')]
    public function test_registration_and_password_reset_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['email' => 'qa@luvo.test'])->assertNotFound();
        $this->post('/password/email', ['email' => 'qa@luvo.test'])->assertNotFound();

        // /password/reset is routed to the home page: no form
        $this->home();
        $this->teams();
        $this->get('/password/reset')->assertOk()->assertDontSee('name="email"', false)->assertDontSee('name="password"', false);
    }

    #[Qa('auth-logout')]
    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->admin());

        $this->get('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/administration')->assertRedirect('/login');
    }

    #[Qa('auth-password')]
    public function test_password_change_validates_and_saves(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->postJson('/api/user/password', ['password' => 'qa-new-password', 'password_confirm' => 'something-else'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.password_confirm.0.field', 'password_confirm');

        $this->postJson('/api/user/password', ['password' => 'short', 'password_confirm' => 'short'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0.field', 'password');

        $this->postJson('/api/user/password', ['password' => 'qa-new-password', 'password_confirm' => 'qa-new-password'])->assertOk();
        $this->assertTrue(Hash::check('qa-new-password', $admin->fresh()->password));
    }

    #[Qa('sh-dashboard')]
    public function test_user_endpoint_returns_the_name(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->getJson('/api/user')->assertExactJson(['firstname' => 'QA', 'name' => 'Admin', 'id' => $admin->id]);
    }
}
