<?php

namespace App\Livewire;

use App\Models\Playbox;
use App\Models\Rental;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use App\Repositories\RentalRepository;
use App\Support\RentalCalculator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Transaksi Rental')]
class RentalComponent extends Component
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
    public string $rental_type = 'pribadi';
    public ?int $partner_id = null;
    public string $rental_date;
    public string $start_time = '';
    public string $end_time = '';
    public float $price_per_hour = 25000;
    public string $payment_method = 'cash';
    public string $payment_status = 'lunas';
    public ?string $note = null;

    public function mount(): void
    {
        $this->rental_date = now()->toDateString();
        $this->start_time = now()->format('Y-m-d H:i');
        $this->end_time = now()->addHour()->format('Y-m-d H:i');
    }

    protected function rules(): array
    {
        return [
            'playbox_id' => ['required', Rule::exists('playboxes', 'id')],
            'rental_type' => 'required|in:pribadi,kerjasama',
            'partner_id' => 'nullable|required_if:rental_type,kerjasama|exists:partners,id',
            'rental_date' => 'required|date',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'price_per_hour' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,qris',
            'payment_status' => 'required|in:lunas,belum_lunas',
            'note' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'end_time.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        'partner_id.required_if' => 'Mitra wajib dipilih untuk transaksi kerjasama.',
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function getDurationProperty(): float
    {
        try {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            return $end->lt($start) ? 0 : round($start->floatDiffInRealHours($end), 2);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getTotalIncomeProperty(): float
    {
        return round($this->duration * (float) $this->price_per_hour, 2);
    }

    public function getBreakdownProperty(): array
    {
        if ($this->rental_type === 'pribadi') {
            return ['mode' => 'pribadi'] + RentalCalculator::privateBreakdown($this->totalIncome);
        }
        return ['mode' => 'kerjasama'] + RentalCalculator::partnershipBreakdown($this->totalIncome);
    }

    public function updatedRentalType(string $value): void
    {
        if ($value === 'pribadi') {
            $this->partner_id = null;
        } else {
            $playbox = $this->playbox_id ? Playbox::find($this->playbox_id) : null;
            if ($playbox && $playbox->ownership_type === 'kerjasama') {
                $this->partner_id = $playbox->partner_id;
            }
        }
    }

    public function updatedPlayboxId($value): void
    {
        $playbox = $value ? Playbox::find($value) : null;
        if (! $playbox) return;
        $this->rental_type = $playbox->ownership_type;
        if ($this->rental_type === 'kerjasama') {
            $this->partner_id = $playbox->partner_id;
        } else {
            $this->partner_id = null;
        }
    }

    public function openCreate(PlayboxRepository $playboxes): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'playbox_id', 'partner_id', 'note']);
        $this->rental_type = 'pribadi';
        $this->payment_method = 'cash';
        $this->payment_status = 'lunas';
        $this->price_per_hour = 25000;
        $this->rental_date = now()->toDateString();
        $this->start_time = now()->format('Y-m-d H:i');
        $this->end_time = now()->addHour()->format('Y-m-d H:i');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $rental = Rental::find($id);
        if (! $rental) return;
        $this->resetValidation();
        $this->editingId = $rental->id;
        $this->playbox_id = $rental->playbox_id;
        $this->partner_id = $rental->partner_id;
        $this->rental_type = $rental->rental_type;
        $this->rental_date = $rental->rental_date->toDateString();
        $this->start_time = $rental->start_time->format('Y-m-d H:i');
        $this->end_time = $rental->end_time->format('Y-m-d H:i');
        $this->price_per_hour = (float) $rental->price_per_hour;
        $this->payment_method = $rental->payment_method;
        $this->payment_status = $rental->payment_status;
        $this->note = $rental->note;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(RentalRepository $repo): void
    {
        $data = $this->validate();
        $data['user_id'] = auth()->id();

        $playbox = Playbox::findOrFail($data['playbox_id']);
        if ($playbox->status === 'tidak_aktif') {
            $this->addError('playbox_id', 'PlayBox sedang tidak aktif.');
            return;
        }

        if ($this->editingId) {
            $rental = Rental::findOrFail($this->editingId);
            $repo->updateWithReport($rental, $data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Transaksi diperbarui.']);
        } else {
            $repo->createWithReport($data);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Transaksi berhasil disimpan.']);
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirm-delete', [
            'id' => $id, 'event' => 'delete-rental',
            'title' => 'Hapus Transaksi?', 'text' => 'Transaksi dan laporan terkait akan dihapus.',
        ]);
    }

    #[On('delete-rental')]
    public function delete(int $id, RentalRepository $repo): void
    {
        $rental = Rental::find($id);
        if ($rental) {
            $repo->delete($rental);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Transaksi dihapus.']);
        }
    }

    public function render(RentalRepository $repo, PlayboxRepository $playboxes, PartnerRepository $partners)
    {
        return view('livewire.rental-component', [
            'rentals' => $repo->paginate(
                $this->search ?: null,
                $this->typeFilter ?: null,
                $this->dateFrom ?: null,
                $this->dateTo ?: null,
            ),
            'playboxOptions' => Playbox::orderBy('code')->get(),
            'partnerOptions' => $partners->options(),
            'duration' => $this->duration,
            'totalIncome' => $this->totalIncome,
            'breakdown' => $this->breakdown,
        ]);
    }
}
