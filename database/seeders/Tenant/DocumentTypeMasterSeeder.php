<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\DocumentTypeMaster;
use Illuminate\Database\Seeder;

class DocumentTypeMasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pan Card', 'Address Proof', 'Educational Certificates', 'Id Proof', 'Passport', 'Aadhar Card', 'Driving Licence', 'Experience Certificates', 'Passport Size Photo', 'Educational Qualification', 'Experience Letter', 'Graduation Final Degree Certificate', 'Post-Graduation Final Degree Certificate', 'Medical Certificates', 'Non-Disclosure Agreement', 'Offer Letter', '10th Standard Certificate', '12th Standard Certificate', 'Academic Qualification Certificates', 'Bank Account Details', 'Bank Salary Account Details', 'Other Documents'] as $type) {
            DocumentTypeMaster::create(['type' => $type]);
        }
    }
}
