<?php

namespace App\Livewire;

use App\Exports\PartnershipReportsExport;
use App\Models\Partner;
use App\Repositories\PartnerRepository;
use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

#[Layout('layouts.app')]
#[Title('Laporan Kerjasama')]
class PartnershipReportComponent extends Component
{
    #[Url(history: true)]
    public string $period = 'bulanan';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    #[Url(history: true)]
    public string $partnerId = '';

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && $user->isMitra() && $user->partner_id) {
            $this->partnerId = (string) $user->partner_id;
        }
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        if ($period !== 'kustom') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
    }

    private function resolvedPartnerId(): ?int
    {
        $user = auth()->user();
        if ($user && $user->isMitra()) {
            return $user->partner_id;
        }
        return $this->partnerId !== '' ? (int) $this->partnerId : null;
    }

    public function exportPdf(ReportRepository $repo): Response
    {
        $reports = $repo->partnershipQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null, $this->resolvedPartnerId())->get();
        $summary = $repo->partnershipSummary($reports);
        $range = $repo->resolveRange($this->period, $this->dateFrom ?: null, $this->dateTo ?: null);
        $partner = $this->resolvedPartnerId() ? Partner::find($this->resolvedPartnerId()) : null;

        $pdf = Pdf::loadView('exports.partnership-report-pdf', [
            'reports' => $reports,
            'summary' => $summary,
            'period_label' => $range['from']->translatedFormat('d M Y') . ' - ' . $range['to']->translatedFormat('d M Y'),
            'partner_label' => $partner?->cafe_name ?? 'Semua Mitra',
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'laporan-kerjasama-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(ReportRepository $repo): BinaryFileResponse
    {
        $reports = $repo->partnershipQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null, $this->resolvedPartnerId())->get();
        return Excel::download(new PartnershipReportsExport($reports), 'laporan-kerjasama-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function render(ReportRepository $repo, PartnerRepository $partners)
    {
        $partnerId = $this->resolvedPartnerId();
        $reports = $repo->partnershipQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null, $partnerId)->paginate(15);
        $allReports = $repo->partnershipQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null, $partnerId)->get();
        $range = $repo->resolveRange($this->period, $this->dateFrom ?: null, $this->dateTo ?: null);

        return view('livewire.partnership-report-component', [
            'reports' => $reports,
            'summary' => $repo->partnershipSummary($allReports),
            'partners' => $partners->options(),
            'rangeFrom' => $range['from'],
            'rangeTo' => $range['to'],
            'isMitra' => auth()->user()?->isMitra() ?? false,
        ]);
    }
}
