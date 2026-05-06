<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\User;

$user = User::find(6); // client1
$query = Notification::leftJoin('notification_delivery_logs', function($join) use ($user) {
    $join->on('notifications.id', '=', 'notification_delivery_logs.notification_id')
         ->where('notification_delivery_logs.recipient_id', '=', $user->id);
})
->select('notifications.*', 'notification_delivery_logs.read_at')
->where(function($q) use ($user) {
    $q->where('target_audience', 'global');
    if ($user->current_gym_id) {
        $q->orWhere(function($sq) use ($user) {
            $sq->where('target_audience', 'specific_gym')
               ->where('related_gym_id', $user->current_gym_id);
        });
    }
    $q->orWhere('notification_delivery_logs.recipient_id', $user->id);
})->orderBy('notifications.created_at', 'desc');

$results = $query->get();
echo "Count: " . $results->count() . "\n";
foreach ($results as $n) {
    echo "ID: {$n->id}, Title: {$n->title}, Read At: " . ($n->read_at ?: 'NULL') . "\n";
}
