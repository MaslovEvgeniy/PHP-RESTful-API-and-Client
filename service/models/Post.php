<?php

namespace app\models;

use app\components\Helper;
use app\components\ModelResponse;
use app\components\PDOConnection;
use PDO;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

/**
 * @SWG\Definition(required={"title", "content"}, type="object")
 *
 * Class Post
 * @package app\models
 */
class Post
{
    private $id;

    /**
     * @SWG\Property()
     * @var string
     */
    private $title;

    /**
     * @SWG\Property()
     * @var string
     */
    private $content;

    /**
     * Post constructor.
     * @param $title
     * @param $content
     * @param null $id
     */
    public function __construct($title, $content, $id = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->id = $id;
    }

    /**
     * Retrieving post data by post id
     * @param $id post id
     * @param $options
     * @return ModelResponse
     */
    public static function findOne($id, $options)
    {
        if (self::checkId($id) === false) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Incorrect post id');
        }

        try {
            $query = self::prepareSelectQuery(true, $options);
        } catch (\Exception $e) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, $e->getMessage());
        }

        $db = PDOConnection::getConnection();

        $result = $db->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();

        return new ModelResponse(ModelResponse::TYPE_RESPONSE, $result->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Retrieving all posts
     * @param $options
     * @return ModelResponse
     */
    public static function findAll($options)
    {
        try {
            $query = self::prepareSelectQuery(false, $options);
        } catch (\Exception $e) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, $e->getMessage());
        }

        $db = PDOConnection::getConnection();

        $result = $db->prepare($query);
        $result->execute();

        return new ModelResponse(ModelResponse::TYPE_RESPONSE, $result->fetchAll(PDO::FETCH_ASSOC));

    }

    /**
     * Getting post object by post id
     * @param $id
     * @return ModelResponse|Post
     */
    public static function getOne($id)
    {
        if (self::checkId($id) === false) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Incorrect post id');
        }

        $query = 'SELECT title, content FROM post '
            . 'WHERE id = :id '
            . 'LIMIT 1';

        $db = PDOConnection::getConnection();

        $result = $db->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();

        $post = $result->fetch(PDO::FETCH_ASSOC);

        if(!empty($post)) {
            return new Post($post['title'], $post['content'], $id);
        } else {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Post not found');
        }
    }

    /**
     * Updating currently existing post
     * @param $data
     * @param $authorId
     * @return ModelResponse
     */
    public function update($data, $authorId)
    {

        if (empty($data)) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Passed data is empty or incorrect');
        }

        if(self::checkAuthor($this->id, $authorId) === false) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, "You don't have permisson to edit this post");
        }

        if(!empty($data['title'])) {
            $this->title = Helper::safeInput($data['title']);
        }

        if(!empty($data['content'])) {
            $this->content = Helper::safeInput($data['content']);
        }

        $result = $this->validate();
        if ($result !== true) {//if errors occurred during the validation
            $errors = $result;
            return new ModelResponse(ModelResponse::TYPE_ERROR, implode("\n",$errors));
        }

        $query = 'UPDATE post SET '
            . 'title = :title, content = :content '
            . 'WHERE id = :id';

        $db = PDOConnection::getConnection();
        $result = $db->prepare($query);
        $result->bindParam(':title', $this->title, PDO::PARAM_STR);
        $result->bindParam(':content', $this->content, PDO::PARAM_STR);
        $result->bindParam(':id', $this->id, PDO::PARAM_INT);
        if ($result->execute()) {
            return new ModelResponse(ModelResponse::TYPE_OK);
        } else {
            return new ModelResponse(ModelResponse::TYPE_ERROR, false);
        }
    }

    /**
     * Removing post
     * @param $id
     * @param $authorId
     * @return ModelResponse
     */
    public static function deleteOne($id, $authorId)
    {
        if (self::checkId($id) === false) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, 'Incorrect post id');
        }
        if(self::checkAuthor($id, $authorId) === false) {
            return new ModelResponse(ModelResponse::TYPE_ERROR, "You don't have permission to delete this post");
        }

        $db = PDOConnection::getConnection();

        $query = 'DELETE FROM post WHERE id = :id';
        $result = $db->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        if ($result->execute()) {
            return new ModelResponse(ModelResponse::TYPE_OK);
        } else {
            return new ModelResponse(ModelResponse::TYPE_ERROR, false);
        }
    }

    /**
     * Checking post author
     * @param $postId
     * @param $authorId
     * @return bool
     */
    private static function checkAuthor($postId, $authorId)
    {
        $db = PDOConnection::getConnection();

        $query = 'SELECT author_id FROM post '
            .'WHERE id = :id';
        $result = $db->prepare($query);
        $result->bindParam(':id', $postId, PDO::PARAM_INT);
        $result->execute();
        $post = $result->fetch();

        return $post['author_id'] === $authorId ? true : false;
    }

    /**
     * Post id validation
     * @param $id
     * @return bool
     */
    private static function checkId($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }
    }

    /**
     * Saving new post into DB
     * @param $authorId
     * @return ModelResponse
     */
    public function save($authorId)
    {
        $result = $this->validate();
        if ($result !== true) {//if errors occurred during the validation
            $errors = $result;
            return new ModelResponse(ModelResponse::TYPE_ERROR, implode("\n",$errors));
        }
        $db = PDOConnection::getConnection();

        $sql = 'INSERT INTO post (title, content, author_id, creation_date) '
            . 'VALUES(:title, :content, :author_id, :creation_date)';

        $result = $db->prepare($sql);

        $creationDate = time();//getting current timestamp
        $result->bindParam(':title', $this->title, PDO::PARAM_STR);
        $result->bindParam(':content', $this->content, PDO::PARAM_STR);
        $result->bindParam(':author_id', $authorId, PDO::PARAM_INT);
        $result->bindParam(':creation_date', $creationDate, PDO::PARAM_INT);
        if ($result->execute()) {
            return new ModelResponse(ModelResponse::TYPE_LINK, $db->lastInsertId());
        } else {
            return new ModelResponse(ModelResponse::TYPE_ERROR, false);
        }

    }

    /**
     * Validating post data
     * @return array|bool
     */
    private function validate()
    {
        $titleValidator = v::alnum()->length(2, 100)->setName("Title");
        $contentValidator = v::alnum()->length(3)->setName("Content");

        $errors = [];

        if (!empty($this->title)) {
            try {
                $titleValidator->check($this->title);
            } catch (ValidationException $e) {
                $errors[] = $e->getMainMessage();
            }
        }

        if (!empty($this->content)) {
            try {
                $contentValidator->check($this->content);
            } catch (ValidationException $e) {
                $errors[] = $e->getMainMessage();
            }
        }

        return empty($errors) ? true : $errors;

    }

    /**
     * Preparing select query
     * @param bool $findOne
     * @param $options
     * @return string
     * @throws \Exception
     */
    private static function prepareSelectQuery($findOne, $options)
    {
        $query = 'SELECT ';
        if (isset($options['fields'])) {
            $fields = explode(',', $options['fields']);
            $result = self::checkFields($fields);
            if ($result !== true) {
                throw new \Exception("Incorrect fields: " . implode(',', $result));
            }

            $query .= implode(', ', $fields) . ' FROM post ';
        } else {
            $query .= '* FROM post ';
        }

        if ($findOne)
            $query .= 'WHERE id = :id ';

        $query .= 'ORDER BY id DESC';

        // TODO search

        return $query;

    }

    /**
     * Checking fields passed in query string
     * @param $fields
     * @return array|bool
     */
    private static function checkFields($fields)
    {
        $db = PDOConnection::getConnection();
        $q = $db->prepare("DESCRIBE post");
        $q->execute();
        $tableFields = $q->fetchAll(PDO::FETCH_COLUMN);

        $diff = array_diff($fields, $tableFields);
        return empty($diff) ? true : $diff;

    }
}