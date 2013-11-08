<?php

namespace MongoAR;

use MongoDB;
use MongoId;
use ArrayAccess;

class ActiveRecord implements ArrayAccess
{
    protected static $database;
    protected $table;
    protected $id;
    protected $document;
    protected $error;

    public static function setDatabase(MongoDB $db)
    {
        self::$database = $db;
    }

    public static function getDatabase()
    {
        return self::$database;
    }

    public static function getTableName()
    {
        return get_called_class();
    }

    public static function __callStatic($name, $arguments)
    {
        $class = get_called_class();
        $instance = QueryBuilder::createInstanceFromAR(new $class);
        return call_user_func_array([
                $instance,
                $name
            ], $arguments
        );
    }

    public function __construct(array $document = array())
    {
        $database = self::$database;
        if (empty($database)) {
            throw new Exception('Active database has not been set', 500);
        }

        $this->table = $database->selectCollection(self::getTableName());
        $this->document = $document;
        $this->fetchId($this->document);
    }

    protected function fetchId(array &$document)
    {
        if (isset($document['_id']) && $document['_id'] instanceof MongoId) {
            $this->id = $document['_id'];
            $document['_id'] = null;
            unset($document['_id']);
        }
    }

    public function save($validate = true, $throw = false)
    {
        if ($this->id) {
            $this->document['_id'] = $this->id;
        }
        $response = $this->table->save($this->document);
        $this->fetchId($this->document);
        return $this->checkResponse($response, $throw);
    }

    protected function checkResponse($response, $throw)
    {
        if ($response['ok']) {
            return true;
        }

        if ($response['err']) {

            $this->error = new Exception($response['errmsg'], $response['code']);
            if ($throw) {
                throw $this->error;
            }
            return false;
        }
    }

    public function setAttribute($name, $value)
    {
        $setter = 'set' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $setter)) {
            return call_user_func_array([$this, $name], [$value]);
        }
        $this->document[$name] = $value;
        return $this;
    }

    public function getAttribute($name)
    {
        $getter = 'get' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $getter)) {
            return call_user_func_array([$this, $name], []);
        }
        return isset($this->document[$name]) ? $this->document[$name] : null;
    }

    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __isset($name)
    {
        return isset($this->document[$name]);
    }

    public function __unset($name)
    {
        unset($this->document[$name]);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function offsetExists($offset)
    {
        return isset($this->document[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->document[$offset]);
    }
}