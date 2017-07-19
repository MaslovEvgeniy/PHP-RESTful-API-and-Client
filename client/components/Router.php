<?php
namespace app\components;

use Error;

class Router
{
    private $routes;

    /**
     * Router constructor
     */
    public function __construct()
    {
        $routesPath = ROOT . '/config/routes.php';
        $this->routes = include($routesPath);
    }

    /**
     * Getting requested URI
     * @return string requested URI
     */
    private function getURI()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'], '/');
        }
    }

    /**
     * Router run
     */
    public function run()
    {
        $uri = $this->getURI();
        $length = strlen(URL);
        $uri = substr($uri, $length);

        foreach ($this->routes as $pattern => $path) {
            if(preg_match("~$pattern~", $uri)) {
                $route = preg_replace("~$pattern~", $path, $uri);
                $segments = explode('/', $route);

                $controller = ucfirst(array_shift($segments) . "Controller");

                $controllerFile = ROOT . '/controllers/' . $controller . '.php';

                if(!file_exists($controllerFile)) {
                    echo $controllerFile;
                    throw new Error();
                }

                $action = "action" . ucfirst(array_shift($segments));
                $parameters = $segments;

                $controllerClass = "\app\controllers\\" . $controller;
                $controllerObject = new $controllerClass;

                $result = call_user_func_array([$controllerObject, $action], $parameters);

                if($result) {
                    return;
                }
            }
        }
    }

}