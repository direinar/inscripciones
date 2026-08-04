<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('autenticación y roles', function () {
    it('inicia sesión con credenciales válidas y redirige al dashboard', function () {
        User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'admin@example.com')->first());
    });

    it('permite al administrador acceder al panel administrativo', function () {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    });

    it('bloquea a un usuario de mercadeo el acceso al panel administrativo', function () {
        $mercadeo = User::factory()->create([
            'role' => 'mercadeo',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($mercadeo)
            ->get('/admin')
            ->assertForbidden();
    });
});
