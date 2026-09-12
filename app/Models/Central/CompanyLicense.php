<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class CompanyLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'have_leave',
        'have_payroll',
        'location_count',
        'company_count',
        'user_count',
        'expiry_date',
        'license_key',
    ];

    protected function casts(): array
    {
        return [
            'have_leave' => 'boolean',
            'have_payroll' => 'boolean',
            'expiry_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @param array<string, bool|int|string> $license */
    public static function generateKey(Company $company, array $license): string
    {
        return Crypt::encryptString(json_encode([
            'version' => 1,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'have_leave' => (bool) $license['have_leave'],
            'have_payroll' => (bool) $license['have_payroll'],
            'location_count' => (int) $license['location_count'],
            'company_count' => (int) $license['company_count'],
            'user_count' => (int) $license['user_count'],
            'expiry_date' => (string) $license['expiry_date'],
        ], JSON_THROW_ON_ERROR));
    }

    /** @return array<string, mixed>|null */
    public function keyPayload(): ?array
    {
        if (! $this->license_key) {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($this->license_key), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($payload) ? $payload : null;
    }
}
