<?php

namespace app\models;

use app\components\ModelResponse;
use app\components\PDOConnection;
use Exception;
use PDO;
use Respect\Validation\Rules\Type;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;
use Firebase\JWT\JWT;
use function Sodium\compare;

/**
 * @SWG\Definition(required={"email", "username", "name", "password", "birth"}, type="object")
 *
 * Class User
 * @package app\models
 */
class User
{
    const TOKEN_TIME = 86400;
    /**
     * @SWG\Property()
     * @var string
     */
    private $email;

    /**
     * @SWG\Property()
     * @var string
     */
    private $username;

    /**
     * @SWG\Property()
     * @var string
     */
    private $name;

    /**
     * @SWG\Property()
     * @var string
     */
    private $password;

    /**
     * @SWG\Property()
     * @var string
     */
    private $birth;

    /**
     * User constructor.
     * @param $email
     * @param $username
     * @param $name
     * @param $password
     * @param $birth
     * @param $countryId
     */
    public function __construct($email, $username, $name, $password, $birth)
    {
        $this->email = $email;
        $this->username = $username;
        $this->name = $name;
        $this->password = $password;
        $this->birth = $birth;
    }

    /**
     * User login
     * @param $login user login
     * @param $password user password
     * @return mixed login result
     */
    public static function login($login, $password)
    {
        $db = PDOConnection::getConnection();

        $sql = 'SELECT id, username, password FROM user WHERE username = :login OR email = :login';

        $result = $db->prepare($sql);
        $result->bindParam(":login", $login, PDO::PARAM_STR);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        $result->execute();
        $user = $result->fetch();
        if (empty($user)) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Incorrect username or password');
        }

        $dbPassword = $user['password'];

        //comparing passwords
        if (password_verify($password, $dbPassword)) {
            $jwt = self::jwtToken($user['id']);
            return new ModelResponse(ModelResponse::TYPE_RESPONSE, ['token' => $jwt, 'expire' => static::TOKEN_TIME]);
        } else {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Incorrect username or password');
        }

    }

    /**
     * Preparing JSON Web Token
     * @param $userId
     * @return mixed|string
     */
    private static function jwtToken($userId)
    {
        $config = require(dirname(__FILE__) . '/../config/config.php');
        $tokenId = base64_encode($config['secretKey']);
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $notBefore + 86400;
        $serverName = $_SERVER['HTTP_HOST']; /// set your domain name

        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => $serverName,       // Issuer
            'nbf' => $notBefore,        // Not before
            'exp' => $expire,           // Expire
            'data' => [                  // Data related to the logged user you can set your required data
                'id' => $userId, // id from the users table
            ]
        ];

        $jwt = JWT::encode(
            $data, //Data to be encoded in the JWT
            $config['secretKey'], // The signing key
            $config['algorithm']
        );

        return $jwt;
    }

    /**
     * Checking JSON Web Token
     * @param null $authHeader
     * @return bool
     */
    public static function jwtAuthenticate($authHeader = null)
    {
        try {
            $config = require(dirname(__FILE__) . '/../config/config.php');
            $auth = explode(' ', $authHeader);
            $result = [];
            if ($auth[0] === 'Bearer' && !empty($auth[1])) {
                $result = JWT::decode($auth[1], $config['secretKey'], array($config['algorithm']));
            } else {
                throw new Exception();
            }

            $id = $result->data->id;
            return $id;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Saving new user into DB
     * @return mixed result of saving
     */
    public function save()
    {
        $result = $this->validate();
        if ($result !== true) {//if errors occurred during the validation
            $errors = $result;
            return new ModelResponse(ModelResponse::TYPE_ERROR, implode("\n", $errors));
        } else {
            $db = PDOConnection::getConnection();

            $sql = 'INSERT INTO user (username, email, password, name, birth_date, registration_date) '
                . 'VALUES(:username, :email, :password, :name, :birth_date, :registration_date)';

            $result = $db->prepare($sql);

            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);//getting password hash
            $registrationDate = time();//getting current timestamp

            $result->bindParam(':username', $this->username, PDO::PARAM_STR);
            $result->bindParam(':email', $this->email, PDO::PARAM_STR);
            $result->bindParam(':password', $passwordHash, PDO::PARAM_STR);
            $result->bindParam(':name', $this->name, PDO::PARAM_STR);
            $result->bindParam(':birth_date', $this->birth);
            $result->bindParam(':registration_date', $registrationDate);
            if ($result->execute()) {
                return new ModelResponse(ModelResponse::TYPE_RESPONSE,
                    ['token' => self::jwtToken($db->lastInsertId()), 'expire' => static::TOKEN_TIME]);
            } else {
                return new ModelResponse(ModelResponse::TYPE_ERROR, false);
            }
        }

    }

    /**
     * Fields validation before saving to DB
     * @return array|bool
     */
    private function validate()
    {
        $emailValidator = v::email()->setName("Email");
        $usernameValidator = v::alnum()->noWhitespace()->length(3, 70)->setName("Username");
        $nameValidator = v::notEmpty()->setName("Full Name");
        $passwordValidator = v::alnum()->noWhitespace()->length(3)->setName("Password");
        $birthValidator = v::optional(v::date('Y-m-d')->setName("Date Of Birth"));

        $errors = [];

        //checking email
        try {
            $emailValidator->check($this->email);
        } catch (ValidationException $e) {
            $errors[] = $e->getMainMessage();
        }

        if (!$this->checkEmailUnique()) {
            $errors[] = 'This email is already in use';
            return $errors;
        }

        //checking username
        try {
            $usernameValidator->check($this->username);
        } catch (ValidationException $e) {
            $errors[] = $e->getMainMessage();
        }

        if (!$this->checkUsernameUnique()) {
            $errors[] = 'This username is already in use';
            return $errors;
        }

        //checking user full name
        try {
            $nameValidator->check($this->name);
        } catch (ValidationException $e) {
            $errors[] = $e->getMainMessage();
        }

        //checking password
        try {
            $passwordValidator->check($this->password);
        } catch (ValidationException $e) {
            $errors[] = $e->getMainMessage();
        }

        //checking user date of birth
        try {
            $birthValidator->check($this->birth);
        } catch (ValidationException $e) {
            $errors[] = $e->getMainMessage();
        }


        if (empty($errors)) {
            return true;//successful validation
        } else {
            return $errors;//If errors are found
        }

    }

    /**
     * Getting user data by user id
     * @param $id
     * @return mixed
     */
    public static function getUserData($id)
    {
        $query = 'SELECT id, username FROM user '
            . 'WHERE id = :id';
        $db = PDOConnection::getConnection();
        $result = $db->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Checking username uniqueness
     * @return bool result of checking
     */
    private function checkUsernameUnique()
    {
        $db = PDOConnection::getConnection();

        $sql = 'SELECT * FROM user WHERE username = :username';

        $result = $db->prepare($sql);
        $result->bindParam(":username", $this->username);
        $result->execute();
        return $result->fetch() ? false : true;
    }

    /**
     * Checking email uniqueness
     * @return bool result of checking
     */
    private function checkEmailUnique()
    {
        $db = PDOConnection::getConnection();

        $sql = 'SELECT * FROM user WHERE email = :email';

        $result = $db->prepare($sql);
        $result->bindParam(":email", $this->email);
        $result->execute();
        return $result->fetch() ? false : true;
    }
}