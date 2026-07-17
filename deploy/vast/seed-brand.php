<?php

use Fleetbase\Models\File;
use Fleetbase\Models\Setting;
use Illuminate\Support\Facades\Storage;

$companyUuid = '967490f8-9bbb-4b26-9167-566d1f4dda28';

$assets = [
    'icon' => ['/tmp/brand/icon.png', 'branding/ifs-icon.png', 'image/png'],
    'logo' => ['/tmp/brand/logo.svg', 'branding/ifs-logo.svg', 'image/svg+xml'],
];

foreach ($assets as $kind => [$src, $dest, $mime]) {
    $contents = file_get_contents($src);
    Storage::disk('public')->put($dest, $contents);

    $file = File::firstOrCreate(
        ['disk' => 'public', 'path' => $dest],
        [
            'company_uuid'      => $companyUuid,
            'original_filename' => basename($dest),
            'type'              => 'image',
            'content_type'      => $mime,
            'file_size'         => strlen($contents),
            'folder'            => 'branding',
        ]
    );

    Setting::configure("branding.{$kind}_uuid", $file->uuid);
    echo strtoupper($kind) . ": {$file->uuid} -> {$file->url}\n";
}

Setting::configure('branding.default_theme', 'dark');
print_r(Setting::getBranding());
