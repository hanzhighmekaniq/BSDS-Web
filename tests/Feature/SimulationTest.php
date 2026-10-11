<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\User;
use Database\Seeders\Case01Seeder;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected CaseModel $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndUserSeeder::class);
        $this->seed(Case01Seeder::class);

        $this->student = User::where('email', 'student@bsds.test')->first();
        $this->case = CaseModel::where('code', 'CASE-01')->first();
    }

    public function test_student_can_view_simulation_catalog(): void
    {
        $response = $this->actingAs($this->student)->get(route('simulations.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Complaint: Salah Barang & Stok Terbatas');
    }

    public function test_student_can_start_simulation(): void
    {
        $response = $this->actingAs($this->student)->post(route('simulations.start', $this->case));

        $response->assertRedirect();
        $this->assertDatabaseHas('simulation_sessions', [
            'user_id' => $this->student->id,
            'case_id' => $this->case->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_full_branching_decision_and_reflection_journey(): void
    {
        // 1. Start simulation
        $this->actingAs($this->student)->post(route('simulations.start', $this->case));
        $session = $this->student->simulationSessions()->latest()->first();

        // 2. View S1
        $response = $this->actingAs($this->student)->get(route('simulations.show', $session));
        $response->assertStatus(200);
        $response->assertSee('S1');
        $response->assertSee('SOP Penanganan Keluhan Pelanggan');

        // 3. Submit Option B at S1 (Listen with empathy -> leads to S2-B)
        $optionB = $session->currentScenario->options()->where('label', 'B')->first();
        $response = $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $optionB->id,
            'confidence_level' => 'confident',
            'investigations_viewed' => ['SOP Penanganan Keluhan Pelanggan', 'Cek Struk Pembelian & Invoice'],
        ]);

        $session->refresh();
        $this->assertEquals('S2-B', $session->currentScenario->step_code);
        $this->assertDatabaseHas('decision_logs', [
            'session_id' => $session->id,
            'option_id' => $optionB->id,
            'confidence_level' => 'confident',
        ]);

        // 4. Submit Option A at S2-B (Alternative variant + voucher -> leads to S3-WIN)
        $optionA = $session->currentScenario->options()->where('label', 'A')->first();
        $response = $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $optionA->id,
            'confidence_level' => 'very_confident',
            'investigations_viewed' => ['Cek Sistem Stok & Alternatif Produk'],
        ]);

        $session->refresh();
        $this->assertEquals('S3-WIN', $session->currentScenario->step_code);
        $this->assertTrue($session->currentScenario->is_ending);

        // 5. Submit reflection
        $response = $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), [
            'q1_reason' => 'Saya memprioritaskan mendengarkan pelanggan dan memberikan opsi proaktif.',
            'q2_unexpected_consequence' => 'Pelanggan langsung menerima opsi pengiriman cabang dengan senang hati.',
            'q3_future_action' => 'Saya akan selalu memeriksa stok cabang sebelum menawarkan refund.',
        ]);

        $response->assertRedirect(route('simulations.journey', $session));

        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertDatabaseHas('reflections', [
            'session_id' => $session->id,
        ]);

        // 6. View decision journey
        $journeyResponse = $this->actingAs($this->student)->get(route('simulations.journey', $session));
        $journeyResponse->assertStatus(200);
        $journeyResponse->assertSee('Linimasa Jejak Keputusan');
        $journeyResponse->assertSee('S3-WIN');
    }
}
