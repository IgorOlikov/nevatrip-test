<?php

use App\Controller\OrderController;
use DI\ContainerBuilder;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use Invoker\Invoker;
use GuzzleHttp\Psr7\Response;
use App\Error\Error;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../vendor/autoload.php';


$request = ServerRequest::fromGlobals();

$response = (new HttpFactory())->createResponse(500)->withHeader('Content-Type', 'application/json');


$containerBuilder = new ContainerBuilder();

$containerBuilder->useAutowiring(true);

$containerBuilder->addDefinitions(require __DIR__ . '/../config/definitions.php');

$container = $containerBuilder->build();

$container->set(Request::class, $request);
$container->set(Response::class, $response);



$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('POST', '/order', [OrderController::class, 'store']);
});


$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode(['error' => 'route not found']));

        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode(['error' => 'method not allowed' . $request->getMethod()]));

        break;
    case FastRoute\Dispatcher::FOUND:
        $callable = $routeInfo[1];
        $routeParams = $routeInfo[2];


        $routeParams['request'] = $container->get(Request::class);
        $routeParams['response'] = $container->get(Response::class);

        $callableInvoker = new Invoker(null, $container);

        try {
            $response = $callableInvoker->call($callable, $routeParams);
        } catch (Throwable $exception) {
            $response = (new Error($container->get(LoggerInterface::class)))->handle($exception, $container->get(Response::class));
        }
}



// Emit the status line
http_response_code($response->getStatusCode());
// Emit the headers
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
// Emit the body
echo $response->getBody();