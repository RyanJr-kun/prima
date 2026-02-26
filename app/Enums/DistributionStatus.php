<?php

namespace App\Enums;

enum DistributionStatus: string
{
    case DRAFT = 'draft';
    case VERIFIED_KAPRODI = 'verified_kaprodi';
    case VERIFIED_WADIR1 = 'verified_wadir1';
    case VERIFIED_WADIR2 = 'verified_wadir2';
    case VERIFIED_WADIR3 = 'verified_wadir3';
    case APPROVED_DIREKTUR = 'approved_direktur';
    case REJECTED = 'rejected';

    // Helper untuk label warna di view
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::VERIFIED_KAPRODI => 'info',
            self::VERIFIED_WADIR1 => 'primary',
            self::VERIFIED_WADIR2 => 'primary',
            self::VERIFIED_WADIR3 => 'primary',
            self::APPROVED_DIREKTUR => 'success',
            self::REJECTED => 'danger',
        };
    }
}
