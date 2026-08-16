<?php

use App\Providers\AliasServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\BroadcastServiceProvider;
use App\Providers\FeatureServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\ViewComposerServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AliasServiceProvider::class,
    AppServiceProvider::class,
    AuthServiceProvider::class,
    BroadcastServiceProvider::class,
    FeatureServiceProvider::class,
    PaymentServiceProvider::class,
    ViewComposerServiceProvider::class,
    PermissionServiceProvider::class,
];
