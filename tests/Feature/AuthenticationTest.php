<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji Tampilan Halaman Login
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk');
        $response->assertSee('owner@umkm.com');
    }

    /**
     * Uji Pengguna Dapat Melakukan Login dengan Kredensial Valid
     */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@umkm.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@umkm.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('seller.pos'));
        $response->assertSessionHas('success');
    }

    /**
     * Uji Login Ditolak Jika Kata Sandi Salah
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@umkm.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login'), [
            'email' => 'admin@umkm.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * Uji Pengguna Dapat Melakukan Logout
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    /**
     * Uji Rute Seller Dialihkan ke Login Jika Belum Masuk (Unauthenticated)
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('seller.pos'));
        $response->assertRedirect(route('login'));

        $responseScm = $this->get(route('seller.scm'));
        $responseScm->assertRedirect(route('login'));

        $responseErp = $this->get(route('seller.erp'));
        $responseErp->assertRedirect(route('login'));

        $responseCrm = $this->get(route('seller.crm'));
        $responseCrm->assertRedirect(route('login'));
    }

    /**
     * Uji Tampilan Halaman Pendaftaran & Registrasi Pengguna Baru
     */
    public function test_users_can_register(): void
    {
        $responseScreen = $this->get(route('register'));
        $responseScreen->assertStatus(200);

        $responseRegister = $this->post(route('register'), [
            'name' => 'Pemilik Baru',
            'email' => 'pemilik.baru@umkm.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'owner',
        ]);

        $this->assertAuthenticated();
        $responseRegister->assertRedirect(route('seller.pos'));

        $user = User::where('email', 'pemilik.baru@umkm.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Pemilik Baru', $user->name);
        $this->assertEquals('owner', $user->role);
    }
}
