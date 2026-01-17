<?php

namespace App\Enums;

enum DistributionStatus: string
{
    case DRAFT = 'draft';
    case VERIFIED_KAPRODI = 'verified_kaprodi';
    case VERIFIED_WADIR1 = 'verified_wadir1';
    case APPROVED_DIREKTUR = 'approved_direktur';
    case REJECTED = 'rejected';

    // Helper untuk label warna di tampilan nanti
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::VERIFIED_KAPRODI => 'info',
            self::VERIFIED_WADIR1 => 'primary',
            self::APPROVED_DIREKTUR => 'success',
            self::REJECTED => 'danger',
        };
    }
}
