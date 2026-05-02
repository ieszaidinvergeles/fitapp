<?php
use App\Models\Booking;
use App\Models\GymClass;
use Illuminate\Support\Carbon;
use App\Http\Resources\BookingResource;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(6);
$now = Carbon::now();

$query = Booking::with(['gymClass.activity', 'gymClass.room', 'gymClass.gym'])
    ->where('user_id', $user->id);

$query->whereHas('gymClass', function($q) use ($now) {
    $q->where('start_time', '>', $now);
})->where('status', 'active');

$query->orderBy(
    GymClass::select('start_time')
        ->whereColumn('id', 'bookings.class_id')
        ->limit(1),
    'desc'
);

$results = $query->paginate(10);
$resource = BookingResource::collection($results)->resolve();

echo json_encode($resource, JSON_PRETTY_PRINT);
