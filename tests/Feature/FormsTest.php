<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'username' => 'tester', 'full_name' => 'Test User',
            'email' => 'tester@example.com', 'password' => 'Password123!',
        ]));
    }


    public function test_customers_can_be_created_and_edited(): void
    {
        $this->get('/customers/create')->assertOk();
        $this->post('/customers', [
            'full_name' => 'Anna Cruz', 'email' => 'anna@example.com', 'phone' => '09171234567',
        ])->assertRedirect('/customers')->assertSessionHas('success');

        $customer = Customer::firstOrFail();
        $this->get("/customers/{$customer->id}/edit")->assertOk()->assertSee('Anna Cruz')->assertSee('anna@example.com');
        $this->put("/customers/{$customer->id}", [
            'full_name' => 'Anna Reyes', 'email' => 'reyes@example.com', 'phone' => '',
        ])->assertRedirect('/customers');
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Anna Reyes', 'phone' => null]);
    }

    public function test_customer_errors_keep_old_input(): void
    {
        $this->from('/customers/create')->post('/customers', [
            'full_name' => 'Anna Cruz', 'email' => 'bad-email', 'phone' => '09171234567',
        ])->assertRedirect('/customers/create')->assertSessionHasErrors('email')
            ->assertSessionHasInput('full_name', 'Anna Cruz')->assertSessionHasInput('phone', '09171234567');
        $this->get('/customers/create')->assertSee('Anna Cruz')->assertSee('bad-email');
        $this->assertDatabaseCount('customers', 0);
        $this->post('/customers', [])->assertSessionHasErrors(['full_name', 'email']);
    }

    public function test_invalid_customer_updates_leave_the_record_unchanged(): void
    {
        $customer = Customer::create(['full_name' => 'Anna Cruz', 'email' => 'anna@example.com']);
        $this->put("/customers/{$customer->id}", [
            'full_name' => '', 'email' => 'wrong',
        ])->assertSessionHasErrors(['full_name', 'email']);
        $this->assertSame('Anna Cruz', $customer->fresh()->full_name);
    }

    public function test_users_can_be_created_and_keep_their_username_when_edited(): void
    {
        $this->get('/users/create')->assertOk();
        $this->post('/users', ['username' => 'anna', 'full_name' => 'Anna Cruz',
            'email' => 'anna@example.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])
            ->assertRedirect('/users')->assertSessionHas('success');
        $user = User::where('username', 'anna')->firstOrFail();
        $this->get("/users/{$user->id}/edit")->assertOk()->assertSee('Anna Cruz');
        $this->put("/users/{$user->id}", ['username' => 'anna', 'full_name' => 'Anna Reyes'])
            ->assertRedirect('/users');
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['username' => 'anna', 'full_name' => 'Anna Reyes']);
    }

    public function test_required_and_duplicate_usernames_are_rejected(): void
    {
        $first = User::create(['username' => 'anna', 'full_name' => 'Anna Cruz']);
        $second = User::create(['username' => 'ben', 'full_name' => 'Ben Cruz']);
        $this->post('/users', [])->assertSessionHasErrors(['username', 'full_name']);
        $this->from('/users/create')->post('/users', ['username' => 'anna', 'full_name' => 'Another Anna'])
            ->assertRedirect('/users/create')->assertSessionHasErrors('username')
            ->assertSessionHasInput('full_name', 'Another Anna');
        $this->put("/users/{$second->id}", ['username' => $first->username, 'full_name' => 'Ben Cruz'])
            ->assertSessionHasErrors('username');
        $this->assertSame('ben', $second->fresh()->username);
        $this->assertDatabaseCount('users', 3);
    }

    public function test_uploaded_avatar_is_resized_stored_and_displayed(): void
    {
        Storage::fake('public');
        $user = User::create(['username' => 'anna', 'full_name' => 'Anna Cruz']);
        $this->put("/users/{$user->id}", [
            'username' => 'anna', 'full_name' => 'Anna Cruz',
            'avatar' => UploadedFile::fake()->image('portrait.png', 600, 400),
        ])->assertRedirect('/users');

        $path = $user->fresh()->avatar;
        $this->assertStringStartsWith('avatars/', $path);
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
        $size = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(160, $size[0]);
        $this->assertSame(160, $size[1]);
        $this->assertSame('image/jpeg', $size['mime']);
        $this->get('/users')->assertOk()->assertSee(Storage::disk('public')->url($path));
    }

    public function test_replacement_removes_old_avatar_and_empty_upload_keeps_it(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old picture');
        $user = User::create(['username' => 'anna', 'full_name' => 'Anna Cruz', 'avatar' => 'avatars/old.jpg']);
        $data = ['username' => 'anna', 'full_name' => 'Anna Reyes'];
        $this->put("/users/{$user->id}", $data)->assertRedirect('/users');
        $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        Storage::disk('public')->assertExists('avatars/old.jpg');

        $this->put("/users/{$user->id}", $data + ['avatar' => UploadedFile::fake()->image('new.jpg')])
            ->assertRedirect('/users');
        Storage::disk('public')->assertMissing('avatars/old.jpg');
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_invalid_uploads_do_not_change_the_user_or_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old picture');
        $user = User::create(['username' => 'anna', 'full_name' => 'Anna Cruz', 'avatar' => 'avatars/old.jpg']);
        $files = [
            UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            UploadedFile::fake()->image('large.jpg')->size(2049),
            UploadedFile::fake()->createWithContent('fake.jpg', 'Not an image'),
            UploadedFile::fake()->createWithContent('picture.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
        ];
        foreach ($files as $file) {
            $this->put("/users/{$user->id}", [
                'username' => 'anna', 'full_name' => 'Changed Name', 'avatar' => $file,
            ])->assertSessionHasErrors('avatar');
            $this->assertSame('Anna Cruz', $user->fresh()->full_name);
            $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        }
        Storage::disk('public')->assertExists('avatars/old.jpg');
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_users_without_avatars_display_the_placeholder(): void
    {
        User::create(['username' => 'anna', 'full_name' => 'Anna Cruz']);
        $this->get('/users')->assertOk()->assertSee('images/avatar-placeholder.svg');
    }

    public function test_missing_records_return_404(): void
    {
        $this->get('/customers/999/edit')->assertNotFound();
        $this->get('/users/999/edit')->assertNotFound();
        $this->put('/customers/999', [])->assertNotFound();
        $this->put('/users/999', [])->assertNotFound();
    }
}
