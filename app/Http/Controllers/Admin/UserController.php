<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentsRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Imports\StudentsImport;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $role = $request->query('role');

        $users = User::query()
            ->with(['roles', 'studentProfile.classroom'])
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', fn ($p) => $p->where('nis', 'like', "%{$search}%"));
            }))
            ->when(in_array($role, ['student', 'researcher', 'admin'], true), fn ($q) => $q->role($role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]);
            // Akun dibuat admin: tandai terverifikasi agar bisa langsung masuk ke dashboard.
            $user->forceFill(['email_verified_at' => now()])->save();

            $this->syncRoleAndProfile($user, $request);
        });

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user->load(['roles', 'studentProfile']),
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->is($request->user()) && $request->validated('role') !== 'admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri dari admin.'])->withInput();
        }

        DB::transaction(function () use ($request, $user) {
            $user->fill($request->safe()->only(['name', 'email']));

            if ($request->filled('password')) {
                $user->password = $request->validated('password');
            }

            $user->save();
            $this->syncRoleAndProfile($user, $request);
        });

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->simulationSessions()->exists()) {
            return back()->withErrors(['delete' => 'Pengguna ini memiliki data simulasi sehingga tidak dapat dihapus (menjaga integritas data penelitian).']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil dihapus.');
    }

    public function importForm(): View
    {
        return view('admin.users.import');
    }

    /**
     * Impor massal siswa dari .csv/.xlsx (kolom: name, email, nis, classroom, absence_no, password).
     */
    public function import(ImportStudentsRequest $request): RedirectResponse
    {
        $rows = Excel::toArray(new StudentsImport, $request->file('file'))[0] ?? [];

        $created = [];
        $skipped = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // baris 1 = heading
            $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

            if (collect($row)->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'nis' => ['nullable', 'max:50', 'unique:student_profiles,nis'],
                'absence_no' => ['nullable', 'integer', 'min:1', 'max:200'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                $skipped[] = ['line' => $line, 'name' => $row['name'] ?? '-', 'reason' => $validator->errors()->first()];

                continue;
            }

            $password = ($row['password'] ?? null) ?: Str::random(10);

            DB::transaction(function () use ($row, $password) {
                $classroom = null;
                if (! empty($row['classroom'])) {
                    $classroom = Classroom::firstOrCreate(
                        ['name' => $row['classroom'], 'academic_year' => $this->academicYear()],
                        ['is_active' => true]
                    );
                }

                $user = User::create(['name' => strip_tags($row['name']), 'email' => $row['email'], 'password' => $password]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole('student');
                $user->studentProfile()->create([
                    'classroom_id' => $classroom?->id,
                    'nis' => ($row['nis'] ?? null) ? (string) $row['nis'] : null,
                    'absence_no' => ($row['absence_no'] ?? null) ?: null,
                ]);
            });

            $created[] = ['name' => $row['name'], 'email' => $row['email'], 'nis' => (string) ($row['nis'] ?? ''), 'password' => $password];
        }

        // Kata sandi hanya ditampilkan sekali (flash), tidak disimpan dalam bentuk teks biasa.
        return redirect()->route('admin.users.import.form')->with('import_report', compact('created', 'skipped'));
    }

    protected function syncRoleAndProfile(User $user, UserRequest $request): void
    {
        $user->syncRoles([$request->validated('role')]);

        if ($request->validated('role') === 'student') {
            $user->studentProfile()->updateOrCreate([], [
                'nis' => $request->validated('nis'),
                'classroom_id' => $request->validated('classroom_id'),
                'absence_no' => $request->validated('absence_no'),
            ]);
        } else {
            $user->studentProfile()->delete();
        }
    }

    protected function academicYear(): string
    {
        $year = (int) now()->format('Y');
        $start = (int) now()->format('n') >= 7 ? $year : $year - 1;

        return $start.'/'.($start + 1);
    }
}
