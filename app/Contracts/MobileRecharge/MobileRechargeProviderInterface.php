<?php

namespace App\Contracts\MobileRecharge;

use App\Models\MobileRecharge;
use App\Services\MobileRecharge\MobileRechargeProviderResult;

interface MobileRechargeProviderInterface
{
    public function recharge(MobileRecharge $recharge): MobileRechargeProviderResult;
}
