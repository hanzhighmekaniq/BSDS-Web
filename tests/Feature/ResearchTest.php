<?php

namespace Tests\Feature;

use App\Exports\Sheets\ReflectionsSheet;
use App\Exports\SimulationResultExport;
use App\Models\CaseModel;
use App\Models\SimulationSession;
use App\Models\User;
use Database\Seeders\Case01Seeder;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $researcher;

    protected CaseModel $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndUserSeeder::class);
        $this->seed(Case01Seeder::class);

        $this->student = User::where('email', 'student@bsds.test')->first();
        $this->researcher = User::where('email', 'researcher@bsds.test')->first();
        $this->case = CaseModel::where('code', 'CASE-01')->first();
    }

    /**
     * Selesaikan satu sesi dengan urutan pilihan tertentu.
     */
    protected function completeSession(string $mode, array $labels, ?string $reflection = null): SimulationSession
    {
        $this->actingAs($this->student)->post(route('simulations.start', $this->case), ['mode' => $mode]);
        $session = $this->student->simulationSessions()->latest('id')->first();

        foreach ($labels as $label) {
            $session->refresh();
            $option = $session->currentScenario->options()->where('label', $label)->firstOrFail();
            $this->actingAs($this->student)->post(route('simulations.decision', $session), [
                'option_id' => $option->id,
                'confidence_level' => 'confident',
            ]);
        }

        if ($reflection !== null) {
            $this->actingAs($this->student)->post(route('simulations.reflection.store', $session), [
                'q1_reason' => $reflection,
                'q2_hardest_decision' => 'Keputusan kedua.',
                'q3_unexpected_consequence' => 'Pelanggan lain datang.',
                'q4_future_action' => 'Periksa stok dulu.',
            ]);
        }

        return $session->fresh();
    }

    public function test_only_researcher_and_admin_can_access_research_pages(): void
    {
        $this->get(route('research.index'))->assertRedirect(route('login'));

        $session = $this->completeSession('practice', ['B', 'B', 'B', 'B']);

        $this->actingAs($this->student)->get(route('research.index'))->assertForbidden();
        $this->actingAs($this->student)->get(route('research.export'))->assertForbidden();
        $this->actingAs($this->student)->get(route('research.show', $session))->assertForbidden();

        $this->actingAs($this->researcher)->get(route('research.index'))->assertOk();
        $this->actingAs(User::where('email', 'admin@gmail.com')->first())->get(route('research.index'))->assertOk();
    }

    public function test_index_lists_sessions_and_applies_filters(): void
    {
        $this->completeSession('practice', ['B', 'B', 'B', 'B']);
        $this->completeSession('assessment', ['A', 'A', 'A', 'A', 'C']);

        $this->actingAs($this->researcher)->get(route('research.index'))
            ->assertOk()
            ->assertSee('Siswa Pelatihan CS')
            ->assertSee('S1:B')
            ->assertSee('S1:A');

        $this->actingAs($this->researcher)->get(route('research.index', ['mode' => 'assessment']))
            ->assertOk()
            ->assertSee('S1:A')
            ->assertDontSee('S1:B');

        $this->actingAs($this->researcher)->get(route('research.index', ['mode' => 'bogus']))
            ->assertSessionHasErrors('mode');
    }

    public function test_show_displays_route_scores_and_reflection(): void
    {
        $session = $this->completeSession('practice', ['B', 'B', 'B', 'B'], 'Saya mendengarkan pelanggan.');

        $this->actingAs($this->researcher)->get(route('research.show', $session))
            ->assertOk()
            ->assertSee('Rute keputusan')
            ->assertSee('Skor indikator')
            ->assertSee('Saya mendengarkan pelanggan.');
    }

    public function test_export_downloads_xlsx_for_filtered_sessions(): void
    {
        $this->completeSession('practice', ['B', 'B', 'B', 'B'], 'Refleksi satu.');

        $response = $this->actingAs($this->researcher)->get(route('research.export', ['mode' => 'practice']));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_export_sheets_contain_expected_rows(): void
    {
        $good = $this->completeSession('practice', ['B', 'B', 'B', 'B'], 'Alasan saya jelas.');
        $bad = $this->completeSession('assessment', ['A', 'A', 'A', 'A', 'C']);

        $sessions = SimulationSession::with(SimulationResultExport::relations())->orderBy('id')->get();
        [$respondents, $decisions, $indicators, $reflections] = (new SimulationResultExport($sessions))->sheets();

        $this->assertEquals(['Responden', 'Keputusan', 'Indikator', 'Refleksi'], [
            $respondents->title(), $decisions->title(), $indicators->title(), $reflections->title(),
        ]);

        $rows = $respondents->collection();
        $this->assertCount(2, $rows);
        $this->assertCount(count($respondents->headings()), $rows[0]);

        $headings = $respondents->headings();
        $this->assertEquals('S1:B > S2:B > S3:B > S4:B', $rows[0][array_search('rute', $headings)]);
        $this->assertEquals('BSDS-001', $rows[0][array_search('nis', $headings)]);
        $this->assertEquals('practice', $rows[0][array_search('mode', $headings)]);
        $this->assertEquals('assessment', $rows[1][array_search('mode', $headings)]);
        $this->assertEquals('baik', $rows[0][array_search('hasil_akhir', $headings)]);

        // Satu baris per keputusan: 4 + 5 (sesi buruk melewati cabang remedial)
        $this->assertCount($good->decisionLogs()->count() + $bad->decisionLogs()->count(), $decisions->collection());
        $this->assertGreaterThan(0, $indicators->collection()->count());

        $reflectionRows = $reflections->collection();
        $this->assertCount(1, $reflectionRows);
        $this->assertEquals('Alasan saya jelas.', $reflectionRows[0][3]);
    }

    public function test_reflection_cells_are_protected_against_formula_injection(): void
    {
        $this->assertEquals("'=HYPERLINK(\"x\")", ReflectionsSheet::safe('=HYPERLINK("x")'));
        $this->assertEquals("'-1+1", ReflectionsSheet::safe('-1+1'));
        $this->assertEquals('Biasa saja', ReflectionsSheet::safe('Biasa saja'));
        $this->assertNull(ReflectionsSheet::safe(null));
    }
}
