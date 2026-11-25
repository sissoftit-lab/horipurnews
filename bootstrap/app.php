<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    dirname(__DIR__)
);

// Hosting layout: app lives in public/project, so the public path is its parent.
// Local layout: use the regular public/ folder.
$hostedInPublic = basename(dirname(__DIR__)) === 'project';

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

 $app->useEnvironmentPath(realpath(__DIR__.'/../vendor/markury/src/'));

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instancesd('contact');
| from the actual running of the application and sending responses.
|
*/

if ($hostedInPublic) {
    $app->usePublicPath(dirname(base_path()));
} else {
    $app->usePublicPath(base_path('public'));
}

return $app;
