<?php
require __DIR__ . '/server/laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/server/laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

$user = User::find(6);
$request = Request::create('/api/v1/me/notifications', 'GET');
$request->setUserResolver(fn() => $user);

$controller = app(NotificationController::class);
$response = $controller->clientIndex($request);

echo json_encode($response->getData(), JSON_PRETTY_PRINT);
