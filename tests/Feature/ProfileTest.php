<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'username' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->username);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});


test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'username' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertTrue($user->fresh()->trashed());
    $this->assertNull(User::find($user->id));
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('viewing a deleted user returns the deleted view instead of a 404', function () {
    $user = User::factory()->create([
        'username' => 'deleteduser',
    ]);
    
    $user->delete();
    
    $response = $this->get('/users/deleteduser');
    
    $response->assertStatus(200);
    $response->assertViewIs('users.deleted');
});

test('viewing a deleted user sub-pages returns the deleted view instead of a 404', function () {
    $user = User::factory()->create([
        'username' => 'deleteduser',
    ]);
    
    $user->delete();
    
    $subpages = ['followers', 'following', 'playlists', 'reviews', 'posts'];
    
    foreach ($subpages as $subpage) {
        $response = $this->get("/users/deleteduser/{$subpage}");
        $response->assertStatus(200);
        $response->assertViewIs('users.deleted');
    }
});
