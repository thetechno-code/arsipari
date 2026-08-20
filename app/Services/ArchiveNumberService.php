<?php

namespace App\Services;

use App\Models\Archive;

class ArchiveNumberService
{
    /**
     * Generate unique sequential archive number for a given year.
     * Format: ARSIP-{YEAR}-{SEQUENCE_6_DIGITS} (e.g. ARSIP-2026-000001)
     */
    public function generate(int $year): string
    {
        $prefix = "ARSIP-{$year}-";

        $latest = Archive::withTrashed()
            ->where('year', $year)
            ->where('archive_number', 'like', "{$prefix}%")
            ->orderBy('archive_number', 'desc')
            ->first();

        if (! $latest) {
            return sprintf('ARSIP-%d-000001', $year);
        }

        $parts = explode('-', $latest->archive_number);
        $sequence = (int) end($parts);
        $nextSequence = $sequence + 1;

        return sprintf('ARSIP-%d-%06d', $year, $nextSequence);
    }
}
