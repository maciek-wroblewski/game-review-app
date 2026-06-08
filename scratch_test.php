<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$ref = new ReflectionMethod($kernel, 'bootstrap');
$ref->setAccessible(true);
$ref->invoke($kernel);

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::bind('user', function (string $value, $route) {
    echo "allowsTrashedBindings: " . ($route->allowsTrashedBindings() ? 'Yes' : 'No') . "\n";
    $query = User::query();
    if ($route->allowsTrashedBindings()) {
        $query->withTrashed();
    }
    return $query->where('id', $value)->orWhere('username', $value)->firstOrFail();
});

$user = User::factory()->create([
    'username' => 'testsoftdelete',
]);
$user->delete();

$request = Request::create('/users/testsoftdelete', 'GET');
$response = $kernel->handle($request);
echo "HTTP Response Status: " . $response->getStatusCode() . "\n";

$user->forceDelete();
