<?php
use App\Models\Booking;
use App\Models\GymClass;
use Illuminate\Support\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(6);
$now = Carbon::now();
echo "Now: " . $now->toDateTimeString() . "\n";

$query = Booking::with(['gymClass.activity', 'gymClass.room', 'gymClass.gym'])
    ->where('user_id', $user->id);

// Default filter
$query2 = clone $query;
$query2->whereHas('gymClass', function($q) use ($now) {
    $q->where('start_time', '>', $now);
})->where('status', 'active');

$results = $query2->get();
echo "Future Active Bookings: " . $results->count() . "\n";
foreach($results as $r) {
    echo " - ID: " . $r->id . " | Start: " . $r->gymClass->start_time . "\n";
}

// All bookings
$resultsAll = $query->get();
echo "Total Bookings: " . $resultsAll->count() . "\n";
