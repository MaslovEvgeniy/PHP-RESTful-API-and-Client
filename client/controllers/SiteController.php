<?php

namespace app\controllers;

class SiteController
{
    /**
     * Action for index page
     * @return bool
     */
    public function actionIndex()
    {
        $posts = $this->getPosts();

        $isGuest = isset($_COOKIE["API-token"]) ? false : true;

        $userData = [];
        if(!$isGuest) {
            $userData = $this->getCurrentUserData();
        }

        require_once ROOT . '/views/site/index.php';
        return true;
    }

    /**
     * Action for login page
     * @return bool
     */
    public function actionLogin()
    {
        if(!empty($_COOKIE['API-token'])) {
            header("location: " . URL ."/");
        }

        $error = '';

        //if login form was submitted
        if(isset($_POST['signin'])) {
            $login = $_POST['login'];
            $password = $_POST['password'];

            $serviceUrl = REST . '/auth/login';
            $curl = curl_init($serviceUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $result = json_decode($response);

            if($httpcode !== 200) {
                $error = implode('<br/>', $result);
            } else {
                setcookie('API-token', $result->token, time() + $result->expire);
                header("Location:" . URL . "/");
            }


        }

        require_once ROOT . '/views/site/login.php';
        return true;
    }

    /**
     * User logout action
     * @return bool
     */
    public function actionLogout()
    {
        if (isset($_COOKIE['API-token'])) {
            unset($_COOKIE['API-token']);
            setcookie('API-token', '', time() - 3600);
        }

        //redirect user to the home page
        header("Location:" . URL . "/");
        return true;
    }

    /**
     * Action for register page
     * @return bool
     */
    public function actionRegister()
    {
        $errors = [];

        //if register form was submitted
        if(isset($_POST['register'])) {
            $requestBody = [];
            $requestBody['email'] = $_POST['email'];
            $requestBody['username'] = $_POST['username'];
            $requestBody['name'] = $_POST['name'];
            $requestBody['password'] = $_POST['password'];
            $requestBody['birth'] = $_POST['birth'];

            $serviceUrl = REST . '/auth/register';
            $curl = curl_init($serviceUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $result = json_decode($response);

            if($httpcode === 400) {
                $errors = $result;
            } else {
                setcookie('API-token', $result->token, time() + $result->expire);
                header("Location:" . URL . "/");
            }

        }

        require_once ROOT . '/views/site/register.php';
        return true;
    }

    /**
     * Post create action
     * @return bool
     */
    public function actionCreate()
    {
        if(empty($_COOKIE['API-token'])) {
            header("location: " . URL ."/login");
        }

        $isGuest = false;
        $errors = [];
        if(isset($_POST['create'])) {
            $requestBody = [];
            $requestBody['title'] = $_POST['title'];
            $requestBody['content'] = $_POST['content'];

            $serviceUrl = REST . '/post';
            $curl = curl_init($serviceUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $_COOKIE["API-token"]
            ]);

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $result = json_decode($response);

            if($httpcode === 201) {
                header("Location:" . URL . "/");
            } else {
                $errors = $result;
            }

        }

        require_once ROOT . '/views/site/form.php';
        return true;

    }

    /**
     * Post edit action
     * @return bool
     */
    public function actionEdit($id = 0)
    {
        //if user not authorized
        if(empty($_COOKIE['API-token'])) {
            header("location: " . URL ."/login");
        }

        //sending GET request to obtain data from one post
        $serviceUrl = REST . '/post/' . $id;
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $post = json_decode($response, true);

        $isGuest = false;
        $errors = [];

        //checking if post was edited
        if(isset($_POST['edit'])) {
            $requestBody = [];
            $requestBody['title'] = $_POST['title'];
            $requestBody['content'] = $_POST['content'];

            //sending PUT request to update post data
            $curl = curl_init($serviceUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $_COOKIE["API-token"]
            ]);
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $result = json_decode($response);

            if($httpcode === 200) {
                header("Location:" . URL . "/");
            } else {
                $errors = $result;
            }
        }

        require_once ROOT . '/views/site/form.php';
        return true;
    }

    /**
     * Post removing action
     * @return bool
     */
    public function actionRemove($id = 0)
    {
        //if user not authorized
        if (empty($_COOKIE['API-token'])) {
            header("location: " . URL . "/login");
        }

        //sending DELETE request to remove one post
        $serviceUrl = REST . '/post/' . $id;
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_COOKIE["API-token"]
        ]);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode === 200) {
            header("Location:" . URL . "/");
        } else {
            $errors = json_decode($response);
            $isGuest = false;
            $userData = $this->getCurrentUserData();
            $posts = $this->getPosts();
            require_once ROOT . '/views/site/index.php';
        }

        return true;

    }

    /**
     * Get all posts
     * @return array
     */
    private function getPosts()
    {
        //Sending GET request to obtain all posts
        $serviceUrl = REST . '/post';
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);

       return json_decode($response, true);
    }

    /**
     * Get data of currently authorized user
     * @return array
     */
    private function getCurrentUserData()
    {
        //Sending GET request to obtain current authorized user data
        $serviceUrl = REST . '/user/';
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_COOKIE["API-token"]
        ]);
        $response = curl_exec($curl);
        return json_decode($response, true);
    }
}