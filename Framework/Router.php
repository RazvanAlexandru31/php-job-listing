<?php

namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorize;

class Router
{
    protected $routes = [];

    /**
     * Add a new route
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param array $middleware
     * @return void
     */

    public function registerRoutes($method, $uri, $action, $middleware = [])
    {
        list($controller, $controllerMethod) = explode('@', $action);
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'controllerMethod' => $controllerMethod,
            'middleware' => $middleware
        ];
    }

    /**
     * Add a GET route
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

    public function get($uri, $controller, $middleware = [])
    {
        $this->registerRoutes('GET', $uri, $controller, $middleware);
    }

    /**
     * Add a POST route
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

    public function post($uri, $controller, $middleware = [])
    {
        $this->registerRoutes('POST', $uri, $controller, $middleware);
    }

    /**
     * Add a PUT route
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

    public function put($uri, $controller, $middleware = [])
    {
        $this->registerRoutes('PUT', $uri, $controller, $middleware);
    }

    /**
     * Add a DELETE route
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

    public function delete($uri, $controller, $middleware = [])
    {
        $this->registerRoutes('DELETE', $uri, $controller, $middleware);
    }


    /**
     * Route to the requested page
     * @param string $uri
     * @param string $method
     * @return void
     */

    public function route($uri)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // check for method input
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        };


        foreach ($this->routes as $route) {

            // Split the current URI into segments
            $uriSegment = explode('/', trim($uri, '/'));

            // Split the route URI into segments
            $routeSegments = explode('/', trim($route['uri'], '/'));

            $match = true;

            // Check if the number of segments matches
            if (count($uriSegment) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)) {
                $params = [];
                $match = true;

                for ($i = 0; $i < count($uriSegment); $i++) {
                    // If the URI do not match and there is no param
                    if ($routeSegments[$i] !== $uriSegment[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])) {
                        $match = false;
                        break;
                    }

                    // Check for the param and add to params array
                    if (preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)) {
                        $params[$matches[1]] = $uriSegment[$i];
                    }
                }


                if ($match) {
                    foreach ($route['middleware'] as $middleware) {
                        (new Authorize())->handle($middleware);
                    }

                    // Extract controller and controller method
                    $controller = 'App\\Controllers\\' . $route['controller'];
                    $controllerMethod = $route['controllerMethod'];

                    // Instatiate the controller and call the method
                    $controllerInstance = new $controller();
                    $controllerInstance->$controllerMethod($params);

                    return;
                }
            }
        }
        ErrorController::notFound();
    }
}
