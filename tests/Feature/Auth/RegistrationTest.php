<?php

namespace Tests\Feature\Auth;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_student_profile(): void
    {
        $classroom = Classroom::create(['name' => 'XI MPLB 1', 'academic_year' => '2026/2027', 'is_active' => true]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'classroom_id' => $classroom->id,
            'absence_no' => 7,
            'nis' => 'BSDS-001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->studentProfile);
        $this->assertEquals($classroom->id, $user->studentProfile->classroom_id);
        $this->assertEquals(7, $user->studentProfile->absence_no);
        $this->assertEquals('BSDS-001', $user->studentProfile->nis);
        $this->assertTrue($user->hasRole('student'));
    }

    public function test_registration_requires_unique_nis(): void
    {
        $classroom = Classroom::create(['name' => 'XI MPLB 1', 'academic_year' => '2026/2027', 'is_active' => true]);
        $existing = User::factory()->create();
        $existing->studentProfile()->create(['classroom_id' => $classroom->id, 'nis' => 'BSDS-001', 'absence_no' => 1]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'classroom_id' => $classroom->id,
            'absence_no' => 8,
            'nis' => 'BSDS-001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('nis');
        $this->assertGuest();
    }
}
