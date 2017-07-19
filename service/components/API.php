<?php

namespace app\components;

use Fuzz\HttpException\HttpException;

/**
 * Class API
 * @package app\components
 */
class API
{
    protected $statusMessage = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        426 => 'Upgrade required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    protected $method;
    protected $contentType = 'application/json';

    /**
     * Incoming request processing
     */
    public function processRequest()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With");
        header("Content-Type: application/json");


        $this->method = $_SERVER['REQUEST_METHOD'];//detecting request type

        $uri = $this->getURI();
        $segments = explode('/', $uri);

        $controller = ucfirst(array_shift($segments) . "Controller");
        $controllerClass = "\app\controllers\\" . $controller;

        if (!class_exists($controllerClass)) {
            $this->sendResponse(404);
        }

        $controllerObject = new $controllerClass;
        if (!($controllerObject instanceof RESTInterface)) {
            if ($controller === 'AuthController' && $this->method === 'POST') {
                try {
                    $response = call_user_func([$controllerObject, 'actionAuth'], $this->prepareParams($segments));
                    $this->sendResponse(200, $response);
                } catch (HttpException $e) {
                    $this->sendResponse($e->getStatusCode(), $e->getMessage());
                }
            }
            $this->sendResponse(404);
        }

        $parameters = $this->prepareParams($segments);

        $response = null;
        try {
            $response = call_user_func([$controllerObject, $this->getAction()], $parameters);
            $code = isset($response) && array_key_exists('link', $response) ? 201 : 200;
            $this->sendResponse($code, $response);
        } catch (HttpException $e) {
            $this->sendResponse($e->getStatusCode(), $e->getMessage());
        }
    }

    /**
     * Sending response to client
     * @param int $statusCode status code to send
     * @param mixed $response data to send
     */
    public function sendResponse($statusCode, $response = null)
    {
        header('HTTP/1.1 ' . $statusCode . ' ' . $this->statusMessage[$statusCode]);

        if (!empty($response)) {
            header('Content-Type:' . $this->contentType);
            if (is_string($response)) {
                $response = explode("\n", $response);
            }
            echo json_encode($response);

            // TODO add another Accept types
        }
        exit;
    }

    /**
     * Preparing parameters before send to Controller
     * @param $arguments
     * @return array
     */
    protected function prepareParams($arguments)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $query = [];
        parse_str($_SERVER['QUERY_STRING'], $query);

        $auth = $_SERVER['HTTP_AUTHORIZATION'];

        return [
            'arguments' => $arguments,
            'data' => $data,
            'query' => $query,
            'auth' => $auth
        ];
    }

    /**
     * Getting URI without query string
     * @return string
     */
    protected function getURI()
    {
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $length = strlen(PATH);
        $uri = substr($uri, $length);
        return strtok($uri, '?');
    }

    /**
     * Choosing action for Controller
     * @return string
     */
    protected function getAction()
    {
        switch ($this->method) {
            case "POST":
                return 'actionCreate';
                break;
            case "GET":
                return 'actionRead';
                break;
            case "PUT":
                return 'actionUpdate';
                break;
            case "DELETE":
                return 'actionDelete';
                break;
            default:
                $this->sendResponse(405);
        }
    }

}