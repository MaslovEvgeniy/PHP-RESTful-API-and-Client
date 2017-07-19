<?php

namespace app\controllers;

use app\components\Helper;
use app\components\ModelResponse;
use app\models\User;
use Fuzz\HttpException\BadRequestHttpException;
use Fuzz\HttpException\InternalServerErrorHttpException;

class AuthController
{
    /**
     * @SWG\SecurityScheme(
     *   securityDefinition="Bearer",
     *   type="apiKey",
     *   in="header",
     *   name="Authorization"
     * )
     */

    /**
     * @SWG\Post(
     *   path="/auth/register",
     *   tags={"auth"},
     *   summary="Registers new user",
     *   operationId="userRegister",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="User data",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/User"),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Authentication token"
     *   ),
     *   @SWG\Response(
     *      response="400",
     *      description="Validation errors"
     *   ),
     * )
     */
     /**
     *   @SWG\Post(path="/auth/login",
     *   tags={"auth"},
     *   summary="Logs user into the system",
     *   description="",
     *   operationId="loginUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="header",
     *     name="login and password",
     *     description="User login and password divided by ':'",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Authentication token",
     *   ),
     *   @SWG\Response(
     *      response="400",
     *      description="Invalid username/password"
     *   ),
     * )
     */
    public function actionAuth($params)
    {
        if (empty($params['arguments'][0])) {
            throw new BadRequestHttpException();
        }
        if ($params['arguments'][0] === 'login') {
            if (!empty($params['auth'])) {
                if (preg_match('/Basic\s+(.*)$/i', $params['auth'], $auth)) {
                    list($authLogin, $authPassword) = explode(':', base64_decode($auth[1]));
                    return $this->parseModelResponse(User::login($authLogin, $authPassword));
                }
            } else {
                return new BadRequestHttpException('Wrong authorization data');
            }
        } else {
            if ($params['arguments'][0] === 'register') {
                if (isset($params['data'])) {
                    $email = Helper::safeInput($params['data']['email']);
                    $username = Helper::safeInput($params['data']['username']);
                    $name = Helper::safeInput($params['data']['name']);
                    $password = Helper::safeInput($params['data']['password']);
                    $birth = Helper::safeInput($params['data']['birth']);

                    //creating new user
                    $user = new User($email, $username, $name, $password, $birth);
                    return $this->parseModelResponse($user->save());
                }
            } else {
                throw new BadRequestHttpException();
            }
        }
    }

    /**
     * Analysis of the model's response
     * @param ModelResponse $result
     * @return null
     */
    private function parseModelResponse(ModelResponse $result)
    {
        $type = $result->getType();
        $response = $result->getResponse();

        if ($type === ModelResponse::TYPE_ERROR) {
            throw new BadRequestHttpException($response);
        } else {
            if ($type === ModelResponse::TYPE_ERROR && $response === false) {
                throw new InternalServerErrorHttpException();
            } else {
                return $response;
            }
        }
    }

}