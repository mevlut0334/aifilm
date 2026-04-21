<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository
{
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, $value, string $type = 'string'): void
    {
        Setting::set($key, $value, $type);
    }

    public function all()
    {
        return Setting::all();
    }
}
