<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $classrooms = Classroom::where('is_active', true)->orderBy('name')->get();

        return view('auth.register', compact('classrooms'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'absence_no' => ['required', 'integer', 'min:1', 'max:200'],
            'nis' => ['required', 'string', 'max:50', 'unique:student_profiles,nis'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']));

        StudentProfile::create([
            'user_id' => $user->id,
            'classroom_id' => $request->integer('classroom_id'),
            'nis' => $request->string('nis'),
            'absence_no' => $request->integer('absence_no'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
