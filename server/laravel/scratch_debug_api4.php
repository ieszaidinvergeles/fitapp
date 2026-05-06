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

$results = BookingResource::collection($query->paginate(10)->withQueryString());

$response = response()->json(['result' => $results, 'message' => ['general' => 'OK']]);
$json = json_decode($response->getContent(), true);

echo "Result keys: " . implode(', ', array_keys($json['result'])) . "\n";
if (isset($json['result']['data'])) {
    echo "Data count: " . count($json['result']['data']) . "\n";
    echo "First item keys: " . implode(', ', array_keys($json['result']['data'][0])) . "\n";
}
