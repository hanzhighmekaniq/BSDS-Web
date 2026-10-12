<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Indicator;
use App\Models\Scenario;
use App\Models\User;
use App\Services\ScenarioGraphValidator;
use Database\Seeders\Case01Seeder;
use Database\Seeders\IndicatorSeeder;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScenarioBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected User $researcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndUserSeeder::class);
        $this->seed(IndicatorSeeder::class);

        $this->admin = User::where('email', 'admin@gmail.com')->first();
        $this->student = User::where('email', 'student@bsds.test')->first();
        $this->researcher = User::where('email', 'researcher@bsds.test')->first();
    }

    protected function createCase(array $overrides = []): CaseModel
    {
        $payload = array_merge([
            'code' => 'CASE-TEST',
            'title' => 'Uji Coba Builder',
            'description' => 'Deskripsi uji coba.',
            'level' => 'foundation',
            'estimated_time' => 10,
        ], $overrides);

        $this->actingAs($this->admin)->post(route('admin.cases.store'), $payload);

        return CaseModel::where('code', $payload['code'])->firstOrFail();
    }

    protected function createScenario(CaseModel $case, array $overrides = []): Scenario
    {
        $payload = array_merge([
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => 'Skenario Uji',
            'story_text' => 'Cerita uji coba skenario.',
            'max_investigations' => 2,
        ], $overrides);

        $this->actingAs($this->admin)->post(route('admin.cases.scenarios.store', $case), $payload);

        return $case->scenarios()->where('step_code', $payload['step_code'])->firstOrFail();
    }

    public function test_only_admin_can_access_scenario_builder_routes(): void
    {
        $case = $this->createCase();
        $scenario = $this->createScenario($case, ['step_code' => 'S1']);
        auth()->guard()->logout();

        $getRoutes = [
            route('admin.cases.create'),
            route('admin.cases.scenarios.create', $case),
            route('admin.cases.scenarios.edit', [$case, $scenario]),
        ];

        foreach ($getRoutes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        foreach ([$this->student, $this->researcher] as $user) {
            foreach ($getRoutes as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        foreach ($getRoutes as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
        auth()->guard()->logout();

        $this->post(route('admin.cases.scenarios.store', $case), ['step_code' => 'X'])->assertRedirect(route('login'));
        $this->actingAs($this->student)->post(route('admin.cases.scenarios.store', $case), ['step_code' => 'X'])->assertForbidden();
        auth()->guard()->logout();

        $this->delete(route('admin.cases.scenarios.destroy', [$case, $scenario]))->assertRedirect(route('login'));
        $this->actingAs($this->researcher)->delete(route('admin.cases.scenarios.destroy', [$case, $scenario]))->assertForbidden();
    }

    public function test_admin_can_create_case_without_code_mutation_later(): void
    {
        $this->actingAs($this->admin)->post(route('admin.cases.store'), [
            'code' => 'case-05',
            'title' => 'Kasus Baru',
            'description' => 'Deskripsi kasus baru.',
            'level' => 'foundation',
            'estimated_time' => 12,
            'is_active' => 1, // mencoba spoof is_active lewat body, harus diabaikan
        ])->assertRedirect();

        $case = CaseModel::where('code', 'CASE-05')->firstOrFail();
        $this->assertFalse($case->is_active);
        $this->assertEquals('CASE-05', $case->code);
    }

    public function test_case_code_cannot_be_changed_via_update(): void
    {
        $case = $this->createCase(['code' => 'CASE-IMMUTABLE']);

        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), [
            'code' => 'HACKED',
            'title' => 'Judul Baru',
            'description' => 'Deskripsi baru.',
            'level' => 'developing',
            'estimated_time' => 20,
        ])->assertRedirect(route('admin.cases.index'));

        $this->assertEquals('CASE-IMMUTABLE', $case->fresh()->code);
    }

    public function test_admin_can_build_a_fully_playable_case_from_scratch(): void
    {
        $case = $this->createCase(['code' => 'CASE-BUILD']);

        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'is_start' => 1,
            'title' => 'Situasi Awal',
            'story_text' => 'Pelanggan datang dengan keluhan.',
            'max_investigations' => 0,
        ]);

        $end = $this->createScenario($case, [
            'step_code' => 'END',
            'scenario_type' => 'ending',
            'is_ending' => 1,
            'title' => 'Penyelesaian',
            'story_text' => 'Kasus selesai.',
            'max_investigations' => 0,
        ]);

        $indicator = Indicator::where('code', 'IDN')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.cases.scenarios.update', [$case, $s1]), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'is_start' => 1,
            'title' => 'Situasi Awal',
            'story_text' => 'Pelanggan datang dengan keluhan.',
            'max_investigations' => 0,
            'options' => [
                [
                    'label' => 'A',
                    'option_text' => 'Tangani dengan baik.',
                    'consequence_text' => 'Pelanggan puas.',
                    'consequence_type' => 'positive',
                    'next_scenario_id' => $end->id,
                    'scores' => [$indicator->id => '2'],
                ],
            ],
        ])->assertRedirect(route('admin.cases.edit', $case));

        $graphErrors = app(ScenarioGraphValidator::class)->validate($case);
        $this->assertSame([], $graphErrors);

        $this->actingAs($this->admin)->get(route('admin.cases.edit', $case))
            ->assertOk()
            ->assertSee('Graf skenario valid');

        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), [
            'title' => $case->title,
            'description' => $case->description,
            'level' => $case->level,
            'estimated_time' => $case->estimated_time,
            'is_active' => 1,
        ])->assertRedirect(route('admin.cases.index'));

        $this->assertTrue($case->fresh()->is_active);

        $this->actingAs($this->student)->post(route('simulations.start', $case))->assertRedirect();
        $session = $this->student->simulationSessions()->latest('id')->first();

        $this->actingAs($this->student)->get(route('simulations.show', $session))
            ->assertOk()
            ->assertSee('Situasi Awal');

        $option = $s1->fresh()->options()->where('label', 'A')->firstOrFail();

        $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $option->id,
            'confidence_level' => 'confident',
        ])->assertRedirect(route('simulations.consequence', $session));

        $this->assertEquals('completed', $session->fresh()->status);
    }

    public function test_duplicate_step_code_within_same_case_is_rejected(): void
    {
        $case = $this->createCase();
        $this->createScenario($case, ['step_code' => 'S1']);

        $this->actingAs($this->admin)->post(route('admin.cases.scenarios.store', $case), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => 'Duplikat',
            'story_text' => 'Cerita duplikat.',
            'max_investigations' => 2,
        ])->assertSessionHasErrors('step_code');

        $this->assertEquals(1, $case->scenarios()->where('step_code', 'S1')->count());
    }

    public function test_same_step_code_allowed_across_different_cases(): void
    {
        $caseA = $this->createCase(['code' => 'CASE-A']);
        $caseB = $this->createCase(['code' => 'CASE-B']);

        $this->createScenario($caseA, ['step_code' => 'S1']);

        $this->actingAs($this->admin)->post(route('admin.cases.scenarios.store', $caseB), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => 'Skenario B',
            'story_text' => 'Cerita B.',
            'max_investigations' => 2,
        ])->assertSessionDoesntHaveErrors();

        $this->assertEquals(1, $caseB->scenarios()->where('step_code', 'S1')->count());
    }

    public function test_option_next_scenario_from_another_case_is_rejected(): void
    {
        $caseA = $this->createCase(['code' => 'CASE-A']);
        $caseB = $this->createCase(['code' => 'CASE-B']);

        $s1A = $this->createScenario($caseA, ['step_code' => 'S1']);
        $s1B = $this->createScenario($caseB, ['step_code' => 'S1']);

        $this->actingAs($this->admin)->put(route('admin.cases.scenarios.update', [$caseA, $s1A]), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => $s1A->title,
            'story_text' => $s1A->story_text,
            'max_investigations' => 2,
            'options' => [
                [
                    'label' => 'A',
                    'option_text' => 'Opsi lintas kasus.',
                    'next_scenario_id' => $s1B->id,
                ],
            ],
        ])->assertSessionHasErrors('options.0.next_scenario_id');

        $this->assertEquals(0, $s1A->fresh()->options()->count());
    }

    public function test_saving_scenario_as_start_unsets_other_start_flags(): void
    {
        $case = $this->createCase();
        $s1 = $this->createScenario($case, ['step_code' => 'S1', 'is_start' => 1]);

        $this->assertTrue($s1->fresh()->is_start);

        $this->createScenario($case, ['step_code' => 'S2', 'is_start' => 1]);

        $this->assertFalse($s1->fresh()->is_start);
        $this->assertTrue($case->scenarios()->where('step_code', 'S2')->first()->is_start);
    }

    public function test_marking_scenario_as_ending_clears_its_options(): void
    {
        $case = $this->createCase();
        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'options' => [['label' => 'A', 'option_text' => 'Opsi awal.']],
        ]);

        $this->assertEquals(1, $s1->fresh()->options()->count());

        $this->actingAs($this->admin)->put(route('admin.cases.scenarios.update', [$case, $s1]), [
            'step_code' => 'S1',
            'scenario_type' => 'ending',
            'is_ending' => 1,
            'title' => $s1->title,
            'story_text' => $s1->story_text,
            'max_investigations' => 2,
            'options' => [['label' => 'A', 'option_text' => 'Opsi yang seharusnya diabaikan.']],
        ])->assertRedirect(route('admin.cases.edit', $case));

        $this->assertEquals(0, $s1->fresh()->options()->count());
        $this->assertTrue($s1->fresh()->is_ending);
    }

    public function test_editing_scenario_scoped_to_wrong_case_returns_404(): void
    {
        $caseA = $this->createCase(['code' => 'CASE-A']);
        $caseB = $this->createCase(['code' => 'CASE-B']);
        $scenarioOfA = $this->createScenario($caseA, ['step_code' => 'S1']);

        $this->actingAs($this->admin)->get(route('admin.cases.scenarios.edit', [$caseB, $scenarioOfA]))->assertNotFound();

        $this->actingAs($this->admin)->put(route('admin.cases.scenarios.update', [$caseB, $scenarioOfA]), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => 'Hijack',
            'story_text' => 'Hijack cerita.',
            'max_investigations' => 2,
        ])->assertNotFound();

        $this->actingAs($this->admin)->delete(route('admin.cases.scenarios.destroy', [$caseB, $scenarioOfA]))->assertNotFound();

        $this->assertEquals('S1', $scenarioOfA->fresh()->step_code);
    }

    public function test_option_id_from_another_scenario_cannot_be_hijacked_via_update(): void
    {
        $case = $this->createCase();
        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'options' => [['label' => 'A', 'option_text' => 'Opsi S1.']],
        ]);
        $s2 = $this->createScenario($case, ['step_code' => 'S2']);

        $optionOfS1 = $s1->fresh()->options()->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.cases.scenarios.update', [$case, $s2]), [
            'step_code' => 'S2',
            'scenario_type' => 'main',
            'title' => $s2->title,
            'story_text' => $s2->story_text,
            'max_investigations' => 2,
            'options' => [
                [
                    'id' => $optionOfS1->id,
                    'label' => 'A',
                    'option_text' => 'Mencoba mencuri opsi S1.',
                ],
            ],
        ])->assertNotFound();

        $this->assertEquals('Opsi S1.', $optionOfS1->fresh()->option_text);
        $this->assertEquals($s1->id, $optionOfS1->fresh()->scenario_id);
    }

    public function test_cannot_delete_scenario_with_decision_logs(): void
    {
        $this->seed(Case01Seeder::class);
        $case = CaseModel::where('code', 'CASE-01')->first();
        $s1 = Scenario::where('step_code', 'S1')->first();
        $option = $s1->options()->first();

        $this->actingAs($this->student)->post(route('simulations.start', $case));
        $session = $this->student->simulationSessions()->latest('id')->first();

        $this->actingAs($this->student)->post(route('simulations.decision', $session), [
            'option_id' => $option->id,
            'confidence_level' => 'confident',
        ]);

        $this->assertEquals(1, $s1->decisionLogs()->count());

        $this->actingAs($this->admin)->delete(route('admin.cases.scenarios.destroy', [$case, $s1]))
            ->assertSessionHasErrors('delete');

        $this->assertNotNull(Scenario::find($s1->id));
    }

    public function test_deleting_scenario_nulls_next_scenario_id_on_referencing_options(): void
    {
        $case = $this->createCase();
        $end = $this->createScenario($case, ['step_code' => 'END', 'scenario_type' => 'ending', 'is_ending' => 1]);
        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'options' => [['label' => 'A', 'option_text' => 'Menuju END.', 'next_scenario_id' => $end->id]],
        ]);

        $option = $s1->fresh()->options()->firstOrFail();
        $this->assertEquals($end->id, $option->next_scenario_id);

        $this->actingAs($this->admin)->delete(route('admin.cases.scenarios.destroy', [$case, $end]))
            ->assertRedirect(route('admin.cases.edit', $case));

        $this->assertDatabaseHas('scenario_options', ['id' => $option->id, 'next_scenario_id' => null]);

        $errors = app(ScenarioGraphValidator::class)->validate($case);
        $this->assertNotEmpty($errors);
    }

    public function test_inf_indicator_is_never_offered_as_a_scoreable_option_score(): void
    {
        $case = $this->createCase();
        $scenario = $this->createScenario($case, ['step_code' => 'S1']);

        $response = $this->actingAs($this->admin)->get(route('admin.cases.scenarios.edit', [$case, $scenario]));

        $response->assertOk();
        $response->assertDontSee('INF');

        $this->assertTrue(Indicator::where('code', 'INF')->exists());
    }

    public function test_case_cannot_be_activated_while_graph_invalid_still_works_for_newly_created_case(): void
    {
        $case = $this->createCase(['code' => 'CASE-EMPTY']);

        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), [
            'title' => $case->title,
            'description' => $case->description,
            'level' => $case->level,
            'estimated_time' => $case->estimated_time,
            'is_active' => 1,
        ])->assertSessionHasErrors('is_active');

        $this->assertFalse($case->fresh()->is_active);
    }

    public function test_admin_can_delete_case_without_sessions(): void
    {
        $case = $this->createCase(['code' => 'CASE-DEL']);
        $this->createScenario($case, ['step_code' => 'S1']);

        $this->actingAs($this->admin)->delete(route('admin.cases.destroy', $case))
            ->assertRedirect(route('admin.cases.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
        $this->assertDatabaseMissing('scenarios', ['case_id' => $case->id]);
    }

    public function test_admin_cannot_delete_case_with_simulation_sessions(): void
    {
        $this->seed(Case01Seeder::class);
        $case = CaseModel::where('code', 'CASE-01')->firstOrFail();

        $this->actingAs($this->student)->post(route('simulations.start', $case));

        $this->actingAs($this->admin)->delete(route('admin.cases.destroy', $case))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }

    public function test_admin_can_duplicate_case(): void
    {
        $case = $this->createCase(['code' => 'CASE-SRC']);
        $end = $this->createScenario($case, ['step_code' => 'END', 'is_ending' => 1, 'scenario_type' => 'ending']);
        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'options' => [
                ['label' => 'A', 'option_text' => 'Teks A', 'consequence_text' => 'Hasil A', 'next_scenario_id' => $end->id]
            ],
            'investigation_items' => [
                ['title' => 'SOP', 'content' => 'Info penting', 'type' => 'sop', 'is_relevant' => 1]
            ],
            'hints' => [
                ['level' => 1, 'hint_text' => 'Petunjuk 1']
            ]
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.cases.duplicate', $case));
        $response->assertRedirect();

        $cloned = CaseModel::where('code', 'CASE-SRC-COPY')->firstOrFail();
        $this->assertFalse($cloned->is_active);
        $this->assertEquals(2, $cloned->scenarios()->count());

        $clonedS1 = $cloned->scenarios()->where('step_code', 'S1')->firstOrFail();
        $clonedEnd = $cloned->scenarios()->where('step_code', 'END')->firstOrFail();
        $clonedOption = $clonedS1->options()->firstOrFail();

        $this->assertEquals($clonedEnd->id, $clonedOption->next_scenario_id);
        $this->assertEquals(1, $clonedS1->investigationItems()->count());
        $this->assertEquals(1, $clonedS1->hints()->count());
    }

    public function test_partial_repeater_items_are_reported_in_status_message(): void
    {
        $case = $this->createCase(['code' => 'CASE-WARN']);

        $response = $this->actingAs($this->admin)->post(route('admin.cases.scenarios.store', $case), [
            'step_code' => 'S1',
            'scenario_type' => 'main',
            'title' => 'Skenario Uji',
            'story_text' => 'Cerita uji skenario.',
            'max_investigations' => 2,
            'options' => [
                ['label' => 'A', 'option_text' => ''], // partial -> skipped
            ],
            'investigation_items' => [
                ['title' => 'Item Tanpa Konten', 'content' => ''], // partial -> skipped
            ],
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('diabaikan karena belum lengkap', session('status'));
    }

    public function test_admin_can_export_and_import_case_via_json(): void
    {
        $case = $this->createCase(['code' => 'CASE-EXP']);
        $end = $this->createScenario($case, ['step_code' => 'END', 'is_ending' => 1, 'scenario_type' => 'ending']);
        $s1 = $this->createScenario($case, [
            'step_code' => 'S1',
            'options' => [
                ['label' => 'A', 'option_text' => 'Teks A', 'consequence_text' => 'Hasil A', 'next_scenario_id' => $end->id]
            ],
        ]);

        $exportResponse = $this->actingAs($this->admin)->get(route('admin.cases.export', $case));
        $exportResponse->assertOk();

        // Simulate import
        $json = json_encode([
            'case' => [
                'code' => 'CASE-IMPTEST',
                'title' => 'Kasus Impor',
                'description' => 'Kasus hasil impor JSON.',
                'level' => 'foundation',
                'estimated_time' => 20,
            ],
            'scenarios' => [
                [
                    'step_code' => 'S1',
                    'title' => 'Step 1',
                    'story_text' => 'Cerita 1',
                    'is_start' => true,
                    'options' => [
                        ['label' => 'A', 'option_text' => 'Opsi A', 'consequence_text' => 'Kons A', 'next_step_code' => 'END']
                    ]
                ],
                [
                    'step_code' => 'END',
                    'title' => 'Ending',
                    'story_text' => 'Tamat',
                    'is_ending' => true,
                ]
            ]
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('case.json', $json);

        $importResponse = $this->actingAs($this->admin)->post(route('admin.cases.import'), [
            'json_file' => $file,
        ]);

        $importResponse->assertRedirect();
        $this->assertDatabaseHas('cases', ['code' => 'CASE-IMPTEST', 'title' => 'Kasus Impor']);

        $importedCase = CaseModel::where('code', 'CASE-IMPTEST')->firstOrFail();
        $this->assertEquals(2, $importedCase->scenarios()->count());
        $importedS1 = $importedCase->scenarios()->where('step_code', 'S1')->firstOrFail();
        $importedEnd = $importedCase->scenarios()->where('step_code', 'END')->firstOrFail();
        $importedOpt = $importedS1->options()->firstOrFail();
        $this->assertEquals($importedEnd->id, $importedOpt->next_scenario_id);
    }
}
