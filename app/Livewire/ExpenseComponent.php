<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Repositories\ExpenseRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Biaya')]
class ExpenseComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $typeFilter = '';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $playbox_id = null;
    public ?int $partner_id = null;
    public string $expense_date = '';
    public string $type = 'maintenance';
    public float $amount = 0;
    public ?string $description = null;

    public function mount(): void
    {
        $this->expense_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'playbox_id' => 'nullable|exists:playboxes,id',
            'partner_id' => 'nullable|exists:partners,id',
            'expense_date' => 'required|date',
            'type' => 'required|in:maintenance,perawatan,kerusakan,staff,lainnya',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'playbox_id', 'partner_id', 'description']);
        $this->expense_date = now()->toDateString();
        $this->type = 'maintenance';
        $this->amount = 0;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $expense = Expense::find($id);
        if (! $expense) return;
        $this->resetValidation();
        $this->editingId = $expense->id;
        $this->playbox_id = $expense->playbox_id;
        $this->partner_id = $expense->partner_id;
        $this->expense_date = $expense->expense_date->toDateString();
        $this->type = $expense->type;
        $this->amount = (float) $expense->amount;
        $this->description = $expense->description;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(ExpenseRepository $repo): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            $repo->update(Expense::findOrFail($this->editingId), $data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Biaya diperbarui.']);
        } else {
            $repo->create($data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Biaya disimpan.']);
        }
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirm-delete', [
            'id' => $id, 'event' => 'delete-expense',
            'title' => 'Hapus Biaya?', 'text' => 'Data biaya akan dihapus permanen.',
        ]);
    }

    #[On('delete-expense')]
    public function delete(int $id, ExpenseRepository $repo): void
    {
        $expense = Expense::find($id);
        if ($expense) {
            $repo->delete($expense);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Biaya dihapus.']);
        }
    }

    public function render(ExpenseRepository $repo, PlayboxRepository $playboxRepo, PartnerRepository $partnerRepo)
    {
        return view('livewire.expense-component', [
            'expenses' => $repo->paginate(
                $this->search ?: null,
                $this->typeFilter ?: null,
                $this->dateFrom ?: null,
                $this->dateTo ?: null,
            ),
            'playboxOptions' => $playboxRepo->paginate(perPage: 200)->items(),
            'partnerOptions' => $partnerRepo->options(),
            'totals' => $repo->totalByType($this->dateFrom ?: null, $this->dateTo ?: null),
        ]);
    }
}
