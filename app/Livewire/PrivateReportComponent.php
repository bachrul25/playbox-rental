<?php

namespace App\Livewire;

use App\Exports\PrivateReportsExport;
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
#[Title('Laporan Pribadi')]
class PrivateReportComponent extends Component
{
    #[Url(history: true)]
    public string $period = 'bulanan';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        if ($period !== 'kustom') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
    }

    public function exportPdf(ReportRepository $repo): Response
    {
        $reports = $repo->privateQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null)->get();
        $summary = $repo->privateSummary($reports);
        $range = $repo->resolveRange($this->period, $this->dateFrom ?: null, $this->dateTo ?: null);

        $pdf = Pdf::loadView('exports.private-report-pdf', [
            'reports' => $reports,
            'summary' => $summary,
            'period_label' => $range['from']->translatedFormat('d M Y') . ' - ' . $range['to']->translatedFormat('d M Y'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'laporan-pribadi-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(ReportRepository $repo): BinaryFileResponse
    {
        $reports = $repo->privateQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null)->get();
        return Excel::download(new PrivateReportsExport($reports), 'laporan-pribadi-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function render(ReportRepository $repo)
    {
        $reports = $repo->privateQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null)->paginate(15);
        $allReports = $repo->privateQuery($this->period, $this->dateFrom ?: null, $this->dateTo ?: null)->get();
        $range = $repo->resolveRange($this->period, $this->dateFrom ?: null, $this->dateTo ?: null);

        return view('livewire.private-report-component', [
            'reports' => $reports,
            'summary' => $repo->privateSummary($allReports),
            'rangeFrom' => $range['from'],
            'rangeTo' => $range['to'],
        ]);
    }
}
