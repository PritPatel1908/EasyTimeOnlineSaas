<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\GeneralConfiguration;
use Illuminate\Support\Facades\URL;

final class TenantCompanyBranding
{
    /**
     * @var list<string>
     */
    public const IMAGE_KEYS = [
        'company_white_logo',
        'company_dark_logo',
        'company_white_mini_logo',
        'company_dark_mini_logo',
        'company_favicon',
        'company_apple_icon',
    ];

    /**
     * @return array<string, string|null>
     */
    public function imageUrls(?string $tenant): array
    {
        $request = request();
        $attribute = 'tenant_company_image_urls:'.($tenant ?? '');
        $cachedImageUrls = $request->attributes->get($attribute);

        if (is_array($cachedImageUrls)) {
            /** @var array<string, string|null> $cachedImageUrls */
            return $cachedImageUrls;
        }

        $imageUrls = array_fill_keys(self::IMAGE_KEYS, null);

        if ($tenant !== null && $tenant !== '') {
            $imagePaths = GeneralConfiguration::query()
                ->whereIn('key', self::IMAGE_KEYS)
                ->pluck('value', 'key');

            foreach (self::IMAGE_KEYS as $key) {
                $imagePath = $imagePaths->get($key);
                if (is_string($imagePath) && str_starts_with($imagePath, 'company-images/')) {
                    $imageUrls[$key] = URL::to('/company-images/'.$key);
                }
            }
        }

        $request->attributes->set($attribute, $imageUrls);

        return $imageUrls;
    }
}
