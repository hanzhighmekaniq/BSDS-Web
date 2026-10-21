<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Classroom;
use App\Models\Indicator;
use App\Models\Scenario;
use App\Models\User;
use Database\Seeders\Case01Seeder;
use Database\Seeders\RoleAndUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected User $researcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndUserSeeder::class);
        $this->seed(Case01Seeder::class);

        $this->admin = User::where('email', 'admin@gmail.com')->first();
        $this->student = User::where('email', 'student@bsds.test')->first();
        $this->researcher = User::where('email', 'researcher@bsds.test')->first();
    }

    public function test_only_admin_can_access_admin_pages(): void
    {
        $routes = ['admin.users.index', 'admin.users.create', 'admin.users.import.form', 'admin.classrooms.index', 'admin.cases.index', 'admin.indicators.index'];

        foreach ($routes as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }

        foreach ([$this->student, $this->researcher] as $user) {
            foreach ($routes as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }

        foreach ($routes as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_create_student_who_can_log_in_and_reach_dashboard(): void
    {
        $classroom = Classroom::first();

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Budi <b>Santoso</b>',
            'email' => 'budi@bsds.test',
            'password' => 'rahasia123',
            'role' => 'student',
            'nis' => 'BSDS-777',
            'classroom_id' => $classroom->id,
            'absence_no' => 7,
        ])->assertRedirect(route('admin.users.index'));

        $budi = User::where('email', 'budi@bsds.test')->first();
        $this->assertEquals('Budi Santoso', $budi->name);
        $this->assertTrue($budi->hasRole('student'));
        $this->assertNotNull($budi->email_verified_at);
        $this->assertEquals('BSDS-777', $budi->studentProfile->nis);

        $this->actingAs($budi)->get(route('dashboard'))->assertOk();
        $this->actingAs($budi)->get(route('simulations.index'))->assertOk();
    }

    public function test_student_nis_optional_but_email_unique(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Tanpa NIS', 'email' => 'student@bsds.test', 'password' => 'rahasia123', 'role' => 'student',
        ])->assertSessionHasErrors('email')->assertSessionDoesntHaveErrors('nis');

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Tanpa NIS', 'email' => 'baru@bsds.test', 'password' => 'rahasia123', 'role' => 'student',
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_can_update_user_role_and_profile(): void
    {
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->student), [
            'name' => 'Siswa Diubah',
            'email' => $this->student->email,
            'role' => 'researcher',
        ])->assertRedirect(route('admin.users.index'));

        $this->student->refresh();
        $this->assertTrue($this->student->hasRole('researcher'));
        $this->assertFalse($this->student->hasRole('student'));
        $this->assertNull($this->student->studentProfile);
    }

    public function test_admin_cannot_demote_or_delete_self(): void
    {
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => 'Admin', 'email' => $this->admin->email, 'role' => 'student', 'nis' => 'X-1',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($this->admin->fresh()->hasRole('admin'));

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin))->assertSessionHasErrors('delete');
        $this->assertNotNull(User::find($this->admin->id));
    }

    public function test_user_with_simulation_data_cannot_be_deleted(): void
    {
        $case = CaseModel::first();
        $this->actingAs($this->student)->post(route('simulations.start', $case));

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->student))->assertSessionHasErrors('delete');
        $this->assertNotNull(User::find($this->student->id));

        $fresh = User::factory()->create();
        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $fresh))->assertRedirect(route('admin.users.index'));
        $this->assertNull(User::find($fresh->id));
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $csv = "name,email,nis,classroom,absence_no,password\n"
            ."Ani Lestari,ani@bsds.test,BSDS-101,XI MPLB 2,1,passwordku1\n"
            ."Dedi Kurnia,dedi@bsds.test,BSDS-102,XI MPLB 2,2,\n"
            ."Dobel Email,student@bsds.test,BSDS-103,XI MPLB 2,3,\n"
            ."Dobel NIS,baru@bsds.test,BSDS-101,XI MPLB 2,4,\n";

        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.users.import'), ['file' => $file]);
        $response->assertRedirect(route('admin.users.import.form'));

        $report = session('import_report');
        $this->assertCount(2, $report['created']);
        $this->assertCount(2, $report['skipped']);

        $ani = User::where('email', 'ani@bsds.test')->first();
        $this->assertTrue($ani->hasRole('student'));
        $this->assertEquals('BSDS-101', $ani->studentProfile->nis);
        $this->assertEquals('XI MPLB 2', $ani->studentProfile->classroom->name);
        $this->assertNotNull($ani->email_verified_at);

        // Password kosong -> dibuat acak (>= 8 karakter) dan bisa dipakai login
        $dedi = collect($report['created'])->firstWhere('email', 'dedi@bsds.test');
        $this->assertGreaterThanOrEqual(8, strlen($dedi['password']));
        $this->assertTrue(Hash::check($dedi['password'], User::where('email', 'dedi@bsds.test')->first()->password));

        $this->actingAs($this->admin)->get(route('admin.users.import.form'))->assertOk();
    }

    public function test_import_rejects_invalid_file_types(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.import'), [
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }

    public function test_classroom_crud_and_delete_protection(): void
    {
        $this->actingAs($this->admin)->post(route('admin.classrooms.store'), [
            'name' => 'XII MPLB 1', 'academic_year' => '2026/2027', 'is_active' => 1,
        ])->assertRedirect(route('admin.classrooms.index'));

        $new = Classroom::where('name', 'XII MPLB 1')->first();
        $this->assertTrue($new->is_active);

        $this->actingAs($this->admin)->put(route('admin.classrooms.update', $new), [
            'name' => 'XII MPLB 1A', 'academic_year' => '2026/2027',
        ])->assertRedirect();
        $this->assertFalse($new->fresh()->is_active);

        $withStudents = Classroom::where('name', 'XI MPLB 1')->first();
        $this->actingAs($this->admin)->delete(route('admin.classrooms.destroy', $withStudents))->assertSessionHasErrors('delete');
        $this->assertNotNull(Classroom::find($withStudents->id));

        $this->actingAs($this->admin)->delete(route('admin.classrooms.destroy', $new))->assertRedirect(route('admin.classrooms.index'));
        $this->assertNull(Classroom::find($new->id));
    }

    public function test_case_metadata_can_be_updated_but_invalid_graph_cannot_be_activated(): void
    {
        $case = CaseModel::where('code', 'CASE-01')->first();
        $payload = [
            'title' => 'Judul Baru', 'description' => 'Deskripsi baru', 'level' => 'developing', 'estimated_time' => 20, 'is_active' => 1,
        ];

        $this->actingAs($this->admin)->get(route('admin.cases.edit', $case))->assertOk()->assertSee('Graf skenario valid');
        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), $payload)->assertRedirect(route('admin.cases.index'));
        $this->assertEquals('Judul Baru', $case->fresh()->title);
        $this->assertEquals('developing', $case->fresh()->level);

        // Rusak graf: opsi tanpa tujuan
        Scenario::where('step_code', 'S1')->first()->options()->first()->update(['next_scenario_id' => null]);
        $case->update(['is_active' => false]);

        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), $payload)->assertSessionHasErrors('is_active');
        $this->assertFalse($case->fresh()->is_active);

        // Menonaktifkan tetap boleh
        $this->actingAs($this->admin)->put(route('admin.cases.update', $case), array_merge($payload, ['is_active' => 0]))->assertRedirect();
    }

    public function test_indicator_can_be_edited_but_code_is_immutable(): void
    {
        $indicator = Indicator::where('code', 'IDN')->first();

        $this->actingAs($this->admin)->put(route('admin.indicators.update', $indicator), [
            'name' => 'Identifikasi masalah (revisi)',
            'code' => 'HACK',
            'sort_order' => 1,
            'is_active' => 1,
            'feedback' => ['baik' => ['feedback_text' => 'Umpan balik baru', 'recommendation_text' => 'Saran baru']],
        ])->assertRedirect(route('admin.indicators.index'));

        $indicator->refresh();
        $this->assertEquals('IDN', $indicator->code);
        $this->assertEquals('Identifikasi masalah (revisi)', $indicator->name);
        $this->assertEquals('Umpan balik baru', $indicator->feedbackTemplates()->where('category', 'baik')->first()->feedback_text);
        $this->assertEquals(3, $indicator->feedbackTemplates()->count());
    }
}
