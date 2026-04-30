<?php

namespace App\Livewire;

use App\Repositories\DashboardRepository;
use App\Repositories\RentalRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class DashboardComponent extends Component
{
    public function render(DashboardRepository $dashboard, RentalRepository $rentals)
    {
        return view('livewire.dashboard-component', [
            'summary' => $dashboard->summary(),
            'chart' => $dashboard->monthlyIncomeChart(12),
            'recent' => $rentals->recent(7),
        ]);
    }
}
