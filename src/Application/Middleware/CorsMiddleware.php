<?php


namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/**
 * CORS middleware.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * Invoke Cors middleware
     *
     * Source: http://www.slimframework.com/docs/v4/cookbook/enable-cors.html
     * https://odan.github.io/2019/11/24/slim4-cors.html
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        $methods = $routingResults->getAllowedMethods();
        $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

        $response = $handler->handle($request);

        $response = $response->withHeader('Access-Control-Allow-Origin', 'http://dev.time-sheet-frontend');
        $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $methods));
        $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders ?: '*');

        // Allow Ajax CORS requests with Authorization header
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');


        return $response;
    }
}