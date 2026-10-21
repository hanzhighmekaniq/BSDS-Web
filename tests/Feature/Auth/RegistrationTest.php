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
        $this->get('/register')->assertOk()->assertDontSee('NIS')->assertDontSee('absence_no');
    }

    public function test_new_users_can_register_without_class_nis_or_absence(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->studentProfile);
        $this->assertNull($user->studentProfile->classroom_id);
        $this->assertTrue($user->hasRole('student'));
    }

    public function test_student_picks_classroom_after_registering(): void
    {
        $classroom = Classroom::create(['name' => 'XI MPLB 1', 'academic_year' => '2026/2027', 'is_active' => true]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->get('/dashboard')->assertSee('Pilih kelas kamu');

        $this->patch('/profile/classroom', ['classroom_id' => $classroom->id])->assertRedirect('/profile');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals($classroom->id, $user->studentProfile->classroom_id);
        $this->actingAs($user)->get('/dashboard')->assertDontSee('Pilih kelas kamu');
    }
}
