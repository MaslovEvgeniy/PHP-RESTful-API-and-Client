<?php

namespace app\controllers;


use app\components\RESTInterface;
use app\models\User;
use Fuzz\HttpException\BadRequestHttpException;
use Fuzz\HttpException\MethodNotAllowedHttpException;
use Fuzz\HttpException\UnauthorizedHttpException;

class UserController implements RESTInterface
{

    public function actionCreate($params)
    {
        throw new MethodNotAllowedHttpException();
    }

    /**
     * @SWG\Get(
     *   path="/user",
     *   tags={"user"},
     *   summary="Get user personal data",
     *   description="Getting data of authenticated user",
     *   operationId="getUserData",
     *   produces={"application/json"},
     *   @SWG\Response(
     *       response=200,
     *       description="successful operation",
     *       @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(
     *       response="400",
     *       description="Invalid token supplied"
     *   ),
     *   @SWG\Response(
     *       response="404",
     *       description="User not found"
     *   ),
     *     security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     * Getting user data by token
     * @param $params
     * @return mixed
     */
    public function actionRead($params)
    {
        if(empty($params['auth'])) {
            throw new UnauthorizedHttpException();
        }

        $userId = User::jwtAuthenticate($params['auth']);
        if($userId === false) {
            throw new BadRequestHttpException('Wrong auth token');
        }
        return User::getUserData($userId);
    }

    public function actionUpdate($params)
    {
        throw new MethodNotAllowedHttpException();
    }

    public function actionDelete($params)
    {
        throw new MethodNotAllowedHttpException();
    }
}