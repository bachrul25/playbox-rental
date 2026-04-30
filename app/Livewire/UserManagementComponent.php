<?php

namespace App\Livewire;

use App\Models\User;
use App\Repositories\PartnerRepository;
use App\Repositories\UserRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen User')]
class UserManagementComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $roleFilter = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'owner';
    public ?int $partner_id = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $this->editingId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|in:admin,owner,mitra',
            'partner_id' => 'nullable|required_if:role,mitra|exists:partners,id',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRoleFilter(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'name', 'email', 'password', 'partner_id']);
        $this->role = 'owner';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::find($id);
        if (! $user) return;
        $this->resetValidation();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->partner_id = $user->partner_id;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(UserRepository $repo): void
    {
        $data = $this->validate();
        if ($data['role'] !== 'mitra') {
            $data['partner_id'] = null;
        }
        if ($this->editingId) {
            $repo->update(User::findOrFail($this->editingId), $data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'User diperbarui.']);
        } else {
            $repo->create($data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'User ditambahkan.']);
        }
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Tidak dapat menghapus akun Anda sendiri.']);
            return;
        }
        $this->dispatch('confirm-delete', [
            'id' => $id, 'event' => 'delete-user',
            'title' => 'Hapus User?', 'text' => 'Akun ini akan dihapus permanen.',
        ]);
    }

    #[On('delete-user')]
    public function delete(int $id, UserRepository $repo): void
    {
        if ($id === auth()->id()) return;
        $user = User::find($id);
        if ($user) {
            $repo->delete($user);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'User dihapus.']);
        }
    }

    public function render(UserRepository $repo, PartnerRepository $partners)
    {
        return view('livewire.user-management-component', [
            'users' => $repo->paginate($this->search ?: null, $this->roleFilter ?: null),
            'partners' => $partners->options(),
        ]);
    }
}
