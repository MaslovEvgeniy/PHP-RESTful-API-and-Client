<?php

namespace app\controllers;

use app\components\ModelResponse;
use app\components\RESTInterface;
use app\models\Post;
use app\models\User;
use Fuzz\HttpException\BadRequestHttpException;
use Fuzz\HttpException\InternalServerErrorHttpException;
use Fuzz\HttpException\NotFoundHttpException;
use Fuzz\HttpException\UnauthorizedHttpException;

class PostController implements RESTInterface
{
    /**
     * @SWG\Post(
     *     path="/post",
     *     tags={"post"},
     *     summary="Adding new post",
     *     operationId="addPost",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="postData",
     *         in="body",
     *         description="Post object that needs to be added to the DB",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Post")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     *
     */
    public function actionCreate($params)
    {

        $userId = $this->checkAuth($params);//check if client has valid token

        if (empty($params['data']['title']) || empty($params['data']['content'])) {
            throw new BadRequestHttpException('Post title and content must be specified');
        }

        $post = new Post($params['data']['title'], $params['data']['content']);//creating new post
        return $this->parseModelResponse($post->save($userId));

    }

    /**
     * @SWG\Get(
     *     path="/post",
     *     tags={"post"},
     *     summary="Find all posts",
     *     description="Returns all posts",
     *     operationId="getPosts",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="Posts fields to return",
     *         in="query",
     *         name="fields",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Post")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Post not found"
     *     ),
     * )
     *
     * @SWG\Get(
     *     path="/post/{postId}",
     *     tags={"post"},
     *     summary="Find one post",
     *     description="Returns one post",
     *     operationId="getPost",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of post",
     *         in="path",
     *         name="postId",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Parameter(
     *         description="Post fields to return",
     *         in="query",
     *         name="fields",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/Post")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Post not found"
     *     ),
     * )
     *
     *
     * Getting all posts or only one
     * @param $params
     * @return array|null
     */
    public function actionRead($params)
    {
        $queryString = isset($params['query']) ? $params['query'] : null;

        if (!empty($params['arguments'][0])) {
            return $this->parseModelResponse(Post::findOne($params['arguments'][0], $queryString));
        } else {
            return $this->parseModelResponse(Post::findAll($queryString));
        }
    }

    /**
     * @SWG\Put(
     *     path="/post/{postId}",
     *     tags={"post"},
     *     operationId="updatePost",
     *     summary="Update an existing post",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="postData",
     *         in="body",
     *         description="Post title and/or content",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Post")
     *     ),
     *     @SWG\Parameter(
     *         description="ID of post",
     *         in="path",
     *         name="postId",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied or validation exception",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Post not found",
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     * Updating post by id
     * @param $params
     * @return array|null
     */
    public function actionUpdate($params)
    {
        $userId = $this->checkAuth($params);

        if (empty($params['arguments'][0])) {
            throw new BadRequestHttpException('Post ID is not specified');
        } else {
            if (empty($params['data'])) {
                throw new BadRequestHttpException('Post data is empty');
            }
        }

        //updating post
        return $this->parseModelResponse(Post::getOne($params['arguments'][0])->update($params['data'], $userId));
    }

    /**
     * @SWG\Delete(
     *     path="/post/{postId}",
     *     summary="Deletes a post",
     *     operationId="deletePost",
     *     produces={"application/json"},
     *     tags={"post"},
     *     @SWG\Parameter(
     *         description="Post id to delete",
     *         in="path",
     *         name="postId",
     *         required=true,
     *         type="integer",
     *         format="int64"
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     * Deleting post by id
     * @param $params
     * @return array|null
     */
    public function actionDelete($params)
    {
        $userId = $this->checkAuth($params);

        if (empty($params['arguments'][0])) {
            throw new BadRequestHttpException('Unable to delete all posts');
        }
        return $this->parseModelResponse(Post::deleteOne($params['arguments'][0], $userId));
    }

    /**
     * Analysis of the model's response
     * @param ModelResponse $result
     * @return array|null
     */
    private function parseModelResponse(ModelResponse $result)
    {
        $type = $result->getType();
        $response = $result->getResponse();

        if ($type === ModelResponse::TYPE_ERROR) {
            throw new BadRequestHttpException($response);
        } else {
            if ($type === ModelResponse::TYPE_RESPONSE && empty($response)) {
                throw new NotFoundHttpException();
            } elseif ($type === ModelResponse::TYPE_ERROR && $response === false) {
                throw new InternalServerErrorHttpException();
            } elseif ($type === ModelResponse::TYPE_NOT_AUTH) {
                throw new UnauthorizedHttpException();
            } elseif ($type === ModelResponse::TYPE_LINK) {
                return ['link' => '/post/' . $response];
            } elseif ($type === ModelResponse::TYPE_OK) {
                return null;
            } else {
                return $response;
            }
        }
    }

    /**
     * Checking client Json Web Token
     * @param $params
     * @return bool
     */
    private
    function checkAuth(
        $params
    ) {
        if (empty($params['auth'])) {
            throw new UnauthorizedHttpException();
        }

        $userId = User::jwtAuthenticate($params['auth']);
        if ($userId === false) {
            throw new BadRequestHttpException('Wrong auth token');
        }
        return $userId;
    }
}