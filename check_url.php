<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Central\Tenant::find('indinf');
tenancy()->initialize($tenant);

$user = \App\Models\Tenant\User::find(1);
if (!$user) {
    echo "User 1 not found, trying all users\n";
    $user = \App\Models\Tenant\User::first();
}

echo "User: " . ($user?->email ?? 'none') . PHP_EOL;

$notifications = $user?->unreadNotifications()->limit(5)->get();
echo "Count: " . ($notifications?->count() ?? 0) . PHP_EOL;

$items = ($notifications ?? collect())->map(fn($n) => [
    'title'        => data_get($n->data, 'title', 'Notification'),
    'message'      => data_get($n->data, 'message', ''),
    'url'          => data_get($n->data, 'url', '#'),
    'download_url' => data_get($n->data, 'download_url'),
])->values()->all();

// This is what the poll endpoint returns
$json = json_encode(['count' => count($items), 'items' => $items], JSON_UNESCAPED_SLASHES);
echo "Poll JSON:\n" . $json . PHP_EOL;
echo "\ndownload_url in JSON: ";
foreach ($items as $item) {
    echo $item['download_url'] ?? 'null';
}
echo PHP_EOL;
