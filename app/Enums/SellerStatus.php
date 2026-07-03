<?php

namespace App\Enums;

enum SellerStatus: string
{
    case PENDING_ONBOARDING = 'pending_onboarding';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case SUSPENDED = 'suspended';
}
