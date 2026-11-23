<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function account(): User
    {
        return User::create([
            'username' => 'john', 'full_name' => 'John Santos',
            'email' => 'john@example.com', 'password' => 'Password123!',
        ]);
    }

    public function test_all_resource_actions_redirect_guests_to_login(): void
    {
        $this->account();
        Customer::create(['full_name' => 'Ana Cruz', 'email' => 'ana@example.com']);
        foreach (['customers', 'users'] as $resource) {
            foreach (['', '/create', '/1', '/1/edit'] as $suffix) {
                $this->get('/'.$resource.$suffix)->assertRedirect('/login');
            }
            $this->post('/'.$resource, [])->assertRedirect('/login');
            $this->put('/'.$resource.'/1', [])->assertRedirect('/login');
            $this->patch('/'.$resource.'/1', [])->assertRedirect('/login');
            $this->delete('/'.$resource.'/1')->assertRedirect('/login');
        }
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_returns_to_the_requested_page(): void
    {
        $user = $this->account();
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/login')->assertOk();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])
            ->assertRedirect('/customers')->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $this->get('/customers')->assertOk();
        $this->get('/users')->assertOk();
    }

    public function test_wrong_password_cannot_log_in(): void
    {
        $user = $this->account();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_repeated_login_failures_are_throttled(): void
    {
        $user = $this->account();
        $key = $user->email.'|127.0.0.1';
        RateLimiter::clear($key);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        RateLimiter::clear($key);
    }

    public function test_registration_hashes_password_and_logs_in(): void
    {
        $this->get('/register')->assertOk();
        $this->post('/register', [
            'username' => 'anna', 'full_name' => 'Anna Cruz', 'email' => 'anna@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect('/dashboard');
        $user = User::where('username', 'anna')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_registration_rejects_duplicates_and_mismatched_passwords(): void
    {
        $user = $this->account();
        $this->post('/register', [
            'username' => $user->username, 'full_name' => 'Other John', 'email' => $user->email,
            'password' => 'Password123!', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['username', 'email', 'password']);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_logout_ends_access_to_protected_pages(): void
    {
        $user = $this->account();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])
            ->assertSessionHasNoErrors();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_reach_resource_pages(): void
    {
        $user = $this->account();
        $customer = Customer::create(['full_name' => 'Ana Cruz', 'email' => 'ana@example.com']);
        $this->actingAs($user);
        foreach (['/customers', '/customers/create', "/customers/{$customer->id}", "/customers/{$customer->id}/edit",
            '/users', '/users/create', "/users/{$user->id}", "/users/{$user->id}/edit"] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_customer_delete_removes_only_the_selected_record(): void
    {
        $this->actingAs($this->account());
        $customer = Customer::create(['full_name' => 'Ana Cruz', 'email' => 'ana@example.com']);
        $other = Customer::create(['full_name' => 'Ben Cruz', 'email' => 'ben@example.com']);
        $this->delete("/customers/{$customer->id}")->assertRedirect('/customers');
        $this->assertModelMissing($customer);
        $this->assertModelExists($other);
    }

    public function test_user_delete_removes_the_avatar_but_self_delete_is_blocked(): void
    {
        Storage::fake('public');
        $admin = $this->account();
        $this->actingAs($admin);
        Storage::disk('public')->put('avatars/old.jpg', 'picture');
        $user = User::create(['username' => 'ben', 'full_name' => 'Ben Cruz', 'avatar' => 'avatars/old.jpg']);
        $this->delete("/users/{$user->id}")->assertRedirect('/users');
        $this->assertModelMissing($user);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
        $this->from('/users')->delete("/users/{$admin->id}")
            ->assertRedirect('/users')->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_managed_user_passwords_are_hashed_and_blank_edits_keep_them(): void
    {
        $this->actingAs($this->account());
        $this->post('/users', [
            'username' => 'ben', 'full_name' => 'Ben Cruz', 'email' => 'ben@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect('/users');
        $user = User::where('username', 'ben')->firstOrFail();
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $oldHash = $user->password;
        $this->put("/users/{$user->id}", [
            'username' => 'ben', 'full_name' => 'Ben Reyes', 'email' => 'ben@example.com',
            'password' => '', 'password_confirmation' => '',
        ])->assertRedirect('/users');
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_password_reset_notification_and_token_work(): void
    {
        Notification::fake();
        $user = $this->account();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token, 'email' => $user->email,
                'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
            ])->assertRedirect('/login');
            return true;
        });
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }
}
