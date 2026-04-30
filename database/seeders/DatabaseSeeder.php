<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Partner;
use App\Models\Playbox;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\RentalRepository;
use App\Support\RentalCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Partner::query()->delete();
        Playbox::query()->delete();
        Rental::query()->delete();
        User::query()->delete();
        Expense::query()->delete();

        $tataKrama = Partner::create([
            'cafe_name' => 'Cafe Tata Krama',
            'person_in_charge' => 'Bapak Roni',
            'phone' => '081234567890',
            'address' => 'Jl. Diponegoro No. 12, Surabaya',
            'cooperation_start_date' => now()->subMonths(3)->toDateString(),
            'status' => 'aktif',
            'note' => 'Kerjasama awal 3 bulan, perpanjangan otomatis.',
        ]);

        $bahagia = Partner::create([
            'cafe_name' => 'Cafe Bahagia',
            'person_in_charge' => 'Ibu Linda',
            'phone' => '081298765432',
            'address' => 'Jl. Pemuda No. 45, Sidoarjo',
            'cooperation_start_date' => now()->subMonths(1)->toDateString(),
            'status' => 'aktif',
            'note' => 'Outlet baru, ramai weekend.',
        ]);

        Partner::create([
            'cafe_name' => 'Cafe Sore',
            'person_in_charge' => 'Mas Doni',
            'phone' => '082345678910',
            'address' => 'Jl. Kenangan, Malang',
            'cooperation_start_date' => now()->subWeeks(2)->toDateString(),
            'status' => 'tidak_aktif',
            'note' => 'Sementara dihentikan, evaluasi.',
        ]);

        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@playbox.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@playbox.com',
            'password' => 'password',
            'role' => 'owner',
        ]);
        User::create([
            'name' => 'Mitra Tata Krama',
            'email' => 'mitra@playbox.com',
            'password' => 'password',
            'role' => 'mitra',
            'partner_id' => $tataKrama->id,
        ]);

        $playboxes = collect([
            ['code' => 'PBX001', 'name' => 'PlayBox Pribadi 1', 'ownership_type' => 'pribadi', 'partner_id' => null, 'location' => 'Rumah Owner', 'status' => 'tersedia'],
            ['code' => 'PBX002', 'name' => 'PlayBox Pribadi 2', 'ownership_type' => 'pribadi', 'partner_id' => null, 'location' => 'Rumah Owner', 'status' => 'tersedia'],
            ['code' => 'PBX003', 'name' => 'PlayBox Cafe Tata Krama', 'ownership_type' => 'kerjasama', 'partner_id' => $tataKrama->id, 'location' => 'Cafe Tata Krama', 'status' => 'tersedia'],
            ['code' => 'PBX004', 'name' => 'PlayBox Cafe Bahagia', 'ownership_type' => 'kerjasama', 'partner_id' => $bahagia->id, 'location' => 'Cafe Bahagia', 'status' => 'maintenance'],
        ])->map(fn ($p) => Playbox::create($p));

        $repo = app(RentalRepository::class);

        $repo->createWithReport([
            'playbox_id' => $playboxes[0]->id,
            'partner_id' => null,
            'user_id' => $admin->id,
            'rental_type' => 'pribadi',
            'rental_date' => now()->subDays(2)->toDateString(),
            'start_time' => now()->subDays(2)->setTime(18, 0)->format('Y-m-d H:i'),
            'end_time' => now()->subDays(2)->setTime(22, 0)->format('Y-m-d H:i'),
            'price_per_hour' => 250000,
            'payment_method' => 'cash',
            'payment_status' => 'lunas',
            'note' => 'Sewa 4 jam, Rp1.000.000',
        ]);

        $repo->createWithReport([
            'playbox_id' => $playboxes[1]->id,
            'partner_id' => null,
            'user_id' => $admin->id,
            'rental_type' => 'pribadi',
            'rental_date' => now()->subDay()->toDateString(),
            'start_time' => now()->subDay()->setTime(15, 0)->format('Y-m-d H:i'),
            'end_time' => now()->subDay()->setTime(18, 0)->format('Y-m-d H:i'),
            'price_per_hour' => 200000,
            'payment_method' => 'transfer',
            'payment_status' => 'lunas',
            'note' => 'Booking weekend',
        ]);

        $repo->createWithReport([
            'playbox_id' => $playboxes[2]->id,
            'partner_id' => $tataKrama->id,
            'user_id' => $admin->id,
            'rental_type' => 'kerjasama',
            'rental_date' => now()->subDays(3)->toDateString(),
            'start_time' => now()->subDays(3)->setTime(14, 0)->format('Y-m-d H:i'),
            'end_time' => now()->subDays(3)->setTime(20, 0)->format('Y-m-d H:i'),
            'price_per_hour' => 150000,
            'payment_method' => 'qris',
            'payment_status' => 'lunas',
            'note' => 'Total Rp900.000 (defisit biaya staff)',
        ]);

        $repo->createWithReport([
            'playbox_id' => $playboxes[2]->id,
            'partner_id' => $tataKrama->id,
            'user_id' => $admin->id,
            'rental_type' => 'kerjasama',
            'rental_date' => now()->toDateString(),
            'start_time' => now()->setTime(10, 0)->format('Y-m-d H:i'),
            'end_time' => now()->setTime(20, 0)->format('Y-m-d H:i'),
            'price_per_hour' => 500000,
            'payment_method' => 'transfer',
            'payment_status' => 'lunas',
            'note' => 'Total Rp5.000.000, sesuai contoh.',
        ]);

        Expense::create([
            'playbox_id' => $playboxes[0]->id,
            'expense_date' => now()->subDays(2)->toDateString(),
            'type' => 'maintenance',
            'amount' => 200000,
            'description' => 'Bersih-bersih dan ganti thermal paste.',
        ]);
        Expense::create([
            'partner_id' => $tataKrama->id,
            'expense_date' => now()->subDays(3)->toDateString(),
            'type' => 'staff',
            'amount' => RentalCalculator::STAFF_COST,
            'description' => 'Gaji staff penunggu Cafe Tata Krama.',
        ]);
        Expense::create([
            'playbox_id' => $playboxes[3]->id,
            'expense_date' => now()->subDays(5)->toDateString(),
            'type' => 'kerusakan',
            'amount' => 350000,
            'description' => 'Perbaikan stick PlayBox Cafe Bahagia.',
        ]);
    }
}
