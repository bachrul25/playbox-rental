<?php

namespace App\Livewire;

use App\Models\Playbox;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data PlayBox')]
class PlayboxComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public string $ownershipFilter = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $code = '';
    public string $name = '';
    public string $ownership_type = 'pribadi';
    public ?int $partner_id = null;
    public ?string $location = null;
    public string $status = 'tersedia';
    public ?string $condition_note = null;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:playboxes,code,' . ($this->editingId ?? 'NULL'),
            'name' => 'required|string|max:120',
            'ownership_type' => 'required|in:pribadi,kerjasama',
            'partner_id' => 'nullable|required_if:ownership_type,kerjasama|exists:partners,id',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:tersedia,disewa,maintenance,tidak_aktif',
            'condition_note' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'partner_id.required_if' => 'Mitra wajib dipilih untuk PlayBox kerjasama.',
        'code.unique' => 'Kode PlayBox sudah digunakan.',
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedOwnershipFilter(): void { $this->resetPage(); }

    public function openCreate(PlayboxRepository $repo): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'name', 'partner_id', 'location', 'condition_note']);
        $this->code = $repo->generateCode();
        $this->ownership_type = 'pribadi';
        $this->status = 'tersedia';
        $this->showModal = true;
    }

    public function openEdit(int $id, PlayboxRepository $repo): void
    {
        $playbox = $repo->find($id);
        if (! $playbox) return;
        $this->resetValidation();
        $this->editingId = $playbox->id;
        $this->code = $playbox->code;
        $this->name = $playbox->name;
        $this->ownership_type = $playbox->ownership_type;
        $this->partner_id = $playbox->partner_id;
        $this->location = $playbox->location;
        $this->status = $playbox->status;
        $this->condition_note = $playbox->condition_note;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(PlayboxRepository $repo): void
    {
        $data = $this->validate();
        if ($data['ownership_type'] === 'pribadi') {
            $data['partner_id'] = null;
        }

        if ($this->editingId) {
            $repo->update(Playbox::findOrFail($this->editingId), $data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'PlayBox berhasil diperbarui.']);
        } else {
            $repo->create($data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'PlayBox berhasil ditambahkan.']);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'partner_id', 'location', 'condition_note']);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'event' => 'delete-playbox',
            'title' => 'Hapus PlayBox?',
            'text' => 'Data PlayBox dan transaksi terkait akan dihapus.',
        ]);
    }

    #[\Livewire\Attributes\On('delete-playbox')]
    public function delete(int $id, PlayboxRepository $repo): void
    {
        $playbox = $repo->find($id);
        if ($playbox) {
            $repo->delete($playbox);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'PlayBox dihapus.']);
        }
    }

    public function render(PlayboxRepository $repo, PartnerRepository $partners)
    {
        return view('livewire.playbox-component', [
            'playboxes' => $repo->paginate($this->search ?: null, $this->statusFilter ?: null, $this->ownershipFilter ?: null, 10),
            'partners' => $partners->options(),
        ]);
    }
}
