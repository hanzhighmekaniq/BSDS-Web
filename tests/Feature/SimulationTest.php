<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Scenario;
use App\Models\SimulationSession;
use App\Models\User;
use App\Services\ScenarioGraphValidator;
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

    protected function startSession(string $mode = 'practice', ?User $user = null): SimulationSession
    {
        $user ??= $this->student;
        $this->actingAs($user)->post(route('simulations.start', $this->case), ['mode' => $mode]);

        return $user->simulationSessions()->latest('id')->first();
    }

    protected function choose(SimulationSession $session, string $label, ?User $user = null)
    {
        $user ??= $this->student;
        $session->refresh();
        $option = $session->currentScenario->options()->where('label', $label)->firstOrFail();

        return $this->actingAs($user)->post(route('simulations.decision', $session), [
            'option_id' => $option->id,
            'confidence_level' => 'confident',
        ]);
    }

    public function test_student_can_view_simulation_catalog(): void
    {
        $this->actingAs($this->student)->get(route('simulations.index'))
            ->assertOk()
            ->assertSee('Customer Complaint: Menangani Keluhan Pelanggan');
    }

    public function test_only_students_can_access_simulations(): void
    {
        $researcher = User::where('email', 'researcher@bsds.test')->first();

        $this->actingAs($researcher)->get(route('simulations.index'))->assertForbidden();
    }

    public function test_student_can_start_practice_and_assessment_sessions(): void
    {
        $session = $this->startSession('assessment');

        $this->assertDatabaseHas('simulation_sessions', [
            'id' => $session->id,
            'user_id' => $this->student->id,
            'mode' => 'assessment',
            'status' => 'in_progress',
            'attempt_no' => 1,
        ]);
        $this->assertEquals('S1', $session->currentScenario->step_code);
    }

    public function test_starting_again_resumes_the_active_session(): void
    {
        $first = $this->startSession();
        $this->actingAs($this->student)->post(route('simulations.start', $this->case))
            ->assertRedirect(route('simulations.show', $first));

        $this->assertEquals(1, SimulationSession::count());
    }

    public function test_best_route_completes_with_good_ending_and_scores(): void
    {
        $session = $this->startSession();

        $this->actingAs($this->student)->get(route('simulations.show', $session))->assertOk()->assertSee('Pelanggan Datang dengan Keluhan');

        // Investigasi S1 (2 item relevan)
        foreach ($session->currentScenario->investigationItems as $item) {
            $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $item]))->assertOk();
        }

        $this->choose($session, 'B')->assertRedirect(route('simulations.consequence', $session));
        $session->refresh();
        $this->assertEquals('S2', $session->currentScenario->step_code);

        foreach ($session->currentScenario->investigationItems->where('is_relevant', true)->take(2) as $item) {
            $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $item]))->assertOk();
        }

        $this->choose($session, 'B');
        $this->assertEquals('S3', $session->fresh()->currentScenario->step_code);

        $this->choose($session, 'B');
        $this->assertEquals('S4', $session->fresh()->currentScenario->step_code);

        $this->choose($session, 'B');
        $session->refresh();

        $this->assertEquals('END-GOOD', $session->currentScenario->step_code);
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertEquals('baik', $session->final_outcome);
        $this->assertEquals(4, $session->decisionLogs()->count());
        $this->assertEquals([1, 2, 3, 4], $session->decisionLogs()->orderBy('step_order')->pluck('step_order')->all());
        $this->assertGreaterThan(0, $session->indicatorResults()->count());

        $this->actingAs($this->student)->get(route('simulations.journey', $session))
            ->assertOk()
            ->assertSee('Linimasa Jejak Keputusan')
            ->assertSee('Profil Performa')
            ->assertSee('What if you chose differently?')
            ->assertSee('Isi Refleksi');
    }

    public function test_escalation_route_goes_through_remedial_scenarios_and_ends_badly(): void
    {
        $session = $this->startSession();

        $this->choose($session, 'A');           // S1 -> S1-R
        $this->assertEquals('S1-R', $session->fresh()->currentScenario->step_code);

        $this->choose($session, 'A');           // S1-R -> S2
        $this->choose($session, 'A');           // S2 -> S2-H (hidden consequence)
        $this->assertEquals('S2-H', $session->fresh()->currentScenario->step_code);

        $this->choose($session, 'A');           // S2-H -> S3
        $this->choose($session, 'C');           // S3 -> END-BAD
        $session->refresh();

        $this->assertEquals('END-BAD', $session->currentScenario->step_code);
        $this->assertEquals('completed', $session->status);
        $this->assertEquals('perlu_latihan', $session->final_outcome);
        $this->assertEquals('negative', $session->decisionLogs()->orderBy('step_order')->first()->consequence_type);
    }

    public function test_decision_with_option_from_another_scenario_is_rejected(): void
    {
        $session = $this->startSession();
        $foreign = Scenario::where('step_code', 'S3')->first()->options()->first();

        $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $foreign->id,
            'confidence_level' => 'confident',
        ])->assertNotFound();

        $this->assertEquals(0, $session->decisionLogs()->count());
        $this->assertEquals('S1', $session->fresh()->currentScenario->step_code);
    }

    public function test_other_students_cannot_access_a_session(): void
    {
        $session = $this->startSession();
        $other = User::factory()->create();
        $other->assignRole('student');

        $this->actingAs($other)->get(route('simulations.show', $session))->assertForbidden();
        $this->actingAs($other)->post(route('simulations.hint', $session))->assertForbidden();
        $this->actingAs($other)->get(route('simulations.journey', $session))->assertForbidden();
    }

    public function test_completed_session_is_locked(): void
    {
        $session = $this->startSession();
        $this->choose($session, 'A');
        $this->choose($session, 'A');
        $this->choose($session, 'A');
        $this->choose($session, 'A');
        $this->choose($session, 'C');
        $session->refresh();
        $this->assertEquals('completed', $session->status);

        $count = $session->decisionLogs()->count();
        $option = Scenario::where('step_code', 'S1')->first()->options()->first();

        $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $option->id,
            'confidence_level' => 'confident',
        ])->assertStatus(400);

        $this->assertEquals($count, $session->decisionLogs()->count());
    }

    public function test_investigation_limit_and_scenario_scope_are_enforced(): void
    {
        $session = $this->startSession();
        $this->choose($session, 'B');                         // -> S2 (batas 2 dari 4 item)
        $items = $session->fresh()->currentScenario->investigationItems;

        $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $items[0]]))->assertOk();
        $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $items[1]]))->assertOk();
        $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $items[2]]))->assertStatus(422);
        // Membuka ulang item yang sama tidak dihitung dua kali
        $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $items[0]]))->assertOk();

        $this->assertEquals(2, $session->investigationViews()->count());

        $otherScenarioItem = Scenario::where('step_code', 'S4')->first()->investigationItems()->first();
        $this->actingAs($this->student)->postJson(route('simulations.investigate', [$session, $otherScenarioItem]))->assertForbidden();
    }

    public function test_hints_are_sequential_and_disabled_in_assessment_mode(): void
    {
        $practice = $this->startSession('practice');

        $this->actingAs($this->student)->postJson(route('simulations.hint', $practice))->assertOk()->assertJson(['level' => 1]);
        $this->actingAs($this->student)->postJson(route('simulations.hint', $practice))->assertOk()->assertJson(['level' => 2]);
        $this->actingAs($this->student)->postJson(route('simulations.hint', $practice))->assertNotFound();
        $this->assertEquals(2, $practice->hintUsages()->count());

        $this->actingAs($this->student)->post(route('simulations.decision', $practice), []);   // memastikan validasi tidak merusak sesi
        $assessmentStudent = User::factory()->create();
        $assessmentStudent->assignRole('student');
        $assessment = $this->startSession('assessment', $assessmentStudent);

        $this->actingAs($assessmentStudent)->postJson(route('simulations.hint', $assessment))->assertForbidden();
    }

    public function test_reflection_requires_completed_session_and_is_sanitized_and_single_use(): void
    {
        $session = $this->startSession();

        $payload = [
            'q1_reason' => '<script>alert(1)</script>Saya mendengarkan pelanggan.',
            'q2_hardest_decision' => 'Keputusan kedua tentang stok.',
            'q3_unexpected_consequence' => 'Pelanggan lain datang.',
            'q4_future_action' => 'Saya akan memeriksa stok dahulu.',
        ];

        // Sesi belum selesai
        $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), $payload)->assertForbidden();

        $this->choose($session, 'B');
        $this->choose($session, 'B');
        $this->choose($session, 'B');
        $this->choose($session, 'B');

        // Terlalu pendek / terlalu panjang ditolak
        $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), array_merge($payload, ['q2_hardest_decision' => 'x']))
            ->assertSessionHasErrors('q2_hardest_decision');
        $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), array_merge($payload, ['q4_future_action' => str_repeat('a', 2001)]))
            ->assertSessionHasErrors('q4_future_action');

        $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), $payload)
            ->assertRedirect(route('simulations.feedback', $session));

        $this->assertStringNotContainsString('<script>', $session->reflection()->first()->q1_reason);

        // Kirim ulang tidak membuat refleksi kedua
        $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), $payload)
            ->assertRedirect(route('simulations.feedback', $session));
        $this->assertEquals(1, $session->reflection()->count());

        $this->actingAs($this->student)->get(route('simulations.feedback', $session))
            ->assertOk()
            ->assertSee('ANALYZE')
            ->assertSee('DECIDE')
            ->assertSee('ADAPT');

        $this->actingAs($this->student)->get(route('simulations.journey', $session))
            ->assertOk()
            ->assertSee('Refleksi Anda');
    }

    public function test_case_graph_is_valid(): void
    {
        $this->assertSame([], app(ScenarioGraphValidator::class)->validate($this->case));
        $this->artisan('bsds:validate-case', ['code' => 'CASE-01'])->assertSuccessful();
    }

    public function test_graph_validator_detects_cycle_and_dead_end(): void
    {
        $s1 = Scenario::where('step_code', 'S1')->first();
        $s2 = Scenario::where('step_code', 'S2')->first();

        // S2 -> S1 membentuk siklus
        $s2->options()->first()->update(['next_scenario_id' => $s1->id]);

        $errors = app(ScenarioGraphValidator::class)->validate($this->case);
        $this->assertTrue(collect($errors)->contains(fn ($e) => str_contains($e, 'siklus')));

        // Opsi tanpa tujuan
        $s1->options()->first()->update(['next_scenario_id' => null]);
        $errors = app(ScenarioGraphValidator::class)->validate($this->case);
        $this->assertTrue(collect($errors)->contains(fn ($e) => str_contains($e, 'tidak punya skenario tujuan')));
    }
}
