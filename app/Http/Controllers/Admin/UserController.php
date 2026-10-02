<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Пользователи. Разделы «Курьеры» и «Операторы» — это этот же список с фильтром по роли.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('role'));
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->with('city')
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($role === UserRole::Courier, fn ($q) => $q->withCount([
                'deliveries as active_deliveries' => fn ($d) => $d->whereIn('status', [DeliveryStatus::Assigned, DeliveryStatus::InTransit]),
                'deliveries as completed_deliveries' => fn ($d) => $d->where('status', DeliveryStatus::Delivered),
            ]))
            ->when($search, function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like));
            })
            ->when($request->query('status') === 'blocked', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'search'));
    }

    public function create(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('role')) ?? UserRole::Courier;

        return view('admin.users.form', [
            'user' => new User(['role' => $role, 'is_active' => true]),
            'cities' => City::active()->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()->route('admin.users.index', ['role' => $user->role->value])
            ->with('success', $user->role->label().' '.$user->name.' добавлен.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'cities' => City::active()->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Администратор не может лишить себя прав или заблокировать себя
        if ($user->is($request->user())) {
            unset($data['role']);
            $data['is_active'] = true;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index', ['role' => $user->role->value])
            ->with('success', 'Данные пользователя '.$user->name.' сохранены.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Нельзя заблокировать самого себя.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'Пользователь '.$user->name.' разблокирован.' : 'Пользователь '.$user->name.' заблокирован.');
    }
}
