<?php

namespace App\Models;

use Sushi\Sushi;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\SushiBase;
use App\Models\Membership\UserApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emr extends SushiBase
{
    use HasApiTokens, HasFactory, Sushi;

    protected $rows = [
        [
            'name' => 'Home Health',
            'key' => 'home-health',
            'application_id' => UserApplication::APP_HOME_HEALTH,
        ],
        [
            'name' => 'Home Care',
            'key' => 'home-care',
            'application_id' => UserApplication::APP_HOME_CARE,
        ],
        [
            'name' => 'Hospice',
            'key' => 'hospice',
            'application_id' => UserApplication::APP_HOSPICE,
        ],
    ];
}
