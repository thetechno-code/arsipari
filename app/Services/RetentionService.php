<?php

namespace App\Services;

use App\Models\RetentionPolicy;
use Carbon\Carbon;

class RetentionService
{
    /**
     * Calculate retention_until date based on document_date and RetentionPolicy.
     */
    public function calculateUntil(?string $documentDate, ?RetentionPolicy $policy): ?string
    {
        if (! $documentDate || ! $policy || $policy->is_permanent || ! $policy->duration_years) {
            return null;
        }

        $date = Carbon::parse($documentDate);
        return $date->addYears($policy->duration_years)->format('Y-m-d');
    }
}
