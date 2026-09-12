<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\FinancialYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinancialYearSeeder extends Seeder
{
    public function run(): void
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;
        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
        $startDate = Carbon::createFromDate($startYear, 4, 1);
        $endDate = Carbon::createFromDate($startYear + 1, 3, 31);

        FinancialYear::create([
            'code' => 'FY'.$startDate->format('y').'-'.$endDate->format('y'),
            'name' => 'Financial Year '.$startDate->format('y').'-'.$endDate->format('y'),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
    }
}
