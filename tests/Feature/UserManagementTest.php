<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('gestión de usuarios', function () {
    it('permite crear un usuario con rol mercadeo', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'mercadeo',
            ])
            ->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com', 'role' => 'mercadeo']);
    });

    it('permite editar el rol de un usuario', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'mercadeo']);

        $this->actingAs($admin)
            ->put('/users/' . $user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
                'role' => 'admin',
            ])
            ->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
    });
});
