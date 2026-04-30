<?php

namespace App\Livewire;

use App\Models\Partner;
use App\Repositories\PartnerRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Mitra/Cafe')]
class PartnerComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $cafe_name = '';
    public string $person_in_charge = '';
    public string $phone = '';
    public ?string $address = null;
    public ?string $cooperation_start_date = null;
    public string $status = 'aktif';
    public ?string $note = null;

    protected function rules(): array
    {
        return [
            'cafe_name' => 'required|string|max:120',
            'person_in_charge' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'cooperation_start_date' => 'nullable|date',
            'status' => 'required|in:aktif,tidak_aktif',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'cafe_name', 'person_in_charge', 'phone', 'address', 'cooperation_start_date', 'note']);
        $this->status = 'aktif';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $partner = Partner::find($id);
        if (! $partner) return;
        $this->resetValidation();
        $this->editingId = $partner->id;
        $this->cafe_name = $partner->cafe_name;
        $this->person_in_charge = $partner->person_in_charge;
        $this->phone = $partner->phone;
        $this->address = $partner->address;
        $this->cooperation_start_date = optional($partner->cooperation_start_date)->toDateString();
        $this->status = $partner->status;
        $this->note = $partner->note;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(PartnerRepository $repo): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            $repo->update(Partner::findOrFail($this->editingId), $data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Mitra berhasil diperbarui.']);
        } else {
            $repo->create($data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Mitra berhasil ditambahkan.']);
        }
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirm-delete', [
            'id' => $id, 'event' => 'delete-partner',
            'title' => 'Hapus Mitra?', 'text' => 'Data mitra dan transaksi terkait akan terdampak.',
        ]);
    }

    #[On('delete-partner')]
    public function delete(int $id, PartnerRepository $repo): void
    {
        $partner = Partner::find($id);
        if ($partner) {
            $repo->delete($partner);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Mitra dihapus.']);
        }
    }

    public function render(PartnerRepository $repo)
    {
        return view('livewire.partner-component', [
            'partners' => $repo->paginate($this->search ?: null, $this->statusFilter ?: null),
        ]);
    }
}
