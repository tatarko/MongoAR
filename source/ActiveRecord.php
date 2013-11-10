<?php

namespace MongoAR;

use MongoDB;
use MongoId;
use ArrayAccess;

/**
 * ActiveRecord above MongoDB
 * @since 0.1
 * @author Tomas Tatarko <tomas@tatarko.sk>
 * @license http://choosealicense.com/licenses/mit/ The MIT License
 * @link https://github.com/tatarko/MongoAR Official github repo
 */
class ActiveRecord implements ArrayAccess
{
    /**
     * Instance of Mongo database that all tables are into
     * @var \MongoDB
     */
    protected static $database;

    /**
     * Instance of Mongo table that all records are into
     * @var \MongoCollection
     */
    protected $table;

    /**
     * Unique identifier of record
     * @var \MongoId
     */
    protected $id;

    /**
     * Variable of document to store
     * @var array
     */
    protected $document;

    /**
     * An error that occured by the process of saving
     * @var \MongoAR\Expcetion
     */
    protected $error;

    /**
     * Setting interal pointer to Mongo database
     * @param \MongoDB $db Instance of Mongo database
     * @since 0.1
     */
    public static function setDatabase(MongoDB $db)
    {
        self::$database = $db;
    }

    /**
     * Getting current pointer to active MongoDB
     * @return \MongoDB
     * @since 0.1
     */
    public static function getDatabase()
    {
        return self::$database;
    }

    /**
     * Getting name of active table name
     * @return string
     * @since 0.1
     */
    public static function getTableName()
    {
        return get_called_class();
    }

    /**
     * Getting instance of QueryBuilder
     * @param string $name Method to call on new instance
     * @param type $arguments Arguments to pass to method calling
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
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

    /**
     * Creating new instance of record
     * @param array $document Array of values for that record
     * @throws \MongoAR\Exception If no active database has been set
     * @since 0.1
     */
    public function __construct(array $document = array())
    {
        $database = self::$database;
        if (empty($database)) {
            throw new Exception('Active database has not been set', 500);
        }

        $this->table = $database->selectCollection(self::getTableName());
        $this->document = $this->fetchId($document);
    }

    /**
     * Moving record identifier from document to the separate property
     * @param array $document Document of active record
     * @return array Document without an index of record identifier
     * @since 0.1
     */
    protected function fetchId(array $document)
    {
        if (isset($document['_id']) && $document['_id'] instanceof MongoId) {
            $this->id = $document['_id'];
            $document['_id'] = null;
            unset($document['_id']);
        }
        return $document;
    }

    /**
     * Saving record to the database
     * @param boolean $validate Validate record before saving
     * @param boolean $throw Throw an expcetion if an error occured
     * @return boolean Operation success mark
     * @throws \MongoAR\Exception If an error occured and $throw is set to TRUE
     * @since 0.1
     */
    public function save($validate = true, $throw = false)
    {
        if ($this->id) {
            $this->document['_id'] = $this->id;
        }
        $response = $this->table->save($this->document);
        $this->fetchId($this->document);
        return $this->checkResponse($response, $throw);
    }

    /**
     * Parse info from operation response and return correct value
     * @param array $response Response value from saving operation
     * @param boolean $throw Throw an exception in case of error
     * @return boolean Operation success mark
     * @throws \MongoAR\Exception If an error occured and $throw is set to TRUE
     * @since 0.1
     */
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

    /**
     * Setting value for specific attribute
     * @param string $name Name attribute to change
     * @param mixed $value New value for that attribute
     * @return \MongoAR\ActiveRecord Instance of self or return value of setter method
     * @since 0.1
     */
    public function setAttribute($name, $value)
    {
        $setter = 'set' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $setter)) {
            return call_user_func_array([$this, $name], [$value]);
        }
        $this->document[$name] = $value;
        return $this;
    }

    /**
     * Getting value of specific attribute
     * @param string $name Name attribute to get
     * @return mixed Current value of that attribute
     * @since 0.1
     */
    public function getAttribute($name)
    {
        $getter = 'get' . ucfirst($name) . 'Attribute';
        if (method_exists($this, $getter)) {
            return call_user_func_array([$this, $name], []);
        }
        return isset($this->document[$name]) ? $this->document[$name] : null;
    }

    /**
     * Setting value for specific attribute
     *
     * This method is only a wrapper for
     *  {@link \MongoAR\ActiveRecord::setAttribute()} allowing
     * straight setting of properties above object.
     *
     * @param string $name Name of the attribute to change
     * @param mixed $value New value for that attribute
     * @return \MongoAR\ActiveRecord Instance of self or return value of setter method
     * @see \MongoAR\ActiveRecord::setAttribute()
     * @since 0.1
     */
    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    /**
     * Getting value of specific attribute
     *
     * This method is only a wrapper for
     *  {@link \MongoAR\ActiveRecord::getAttribute()} allowing
     * straight getting of properties above object.
     *
     * @param string $name Name of the attribute to get
     * @return mixed Current value of that attribute
     * @see \MongoAR\ActiveRecord::getAttribute()
     * @since 0.1
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Checking if specific attribute exists in document
     * @param string $name Name of attribute to check
     * @return boolean
     * @since 0.1
     */
    public function __isset($name)
    {
        return isset($this->document[$name]);
    }

    /**
     * Unsetting specific attribute
     * @param string $name Name of the atttribute to unset
     * @since 0.1
     */
    public function __unset($name)
    {
        unset($this->document[$name]);
    }

    /**
     * Getting instance of active table in Mongo database
     * @return \MongoCollection
     * @since 0.1
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Checking if specific key exists in document
     * @param string $offset Name of the key to check
     * @return boolean
     * @since 0.1
     */
    public function offsetExists($offset)
    {
        return isset($this->document[$offset]);
    }

    /**
     * Getting value of specific key
     *
     * This method is only a wrapper for
     *  {@link \MongoAR\ActiveRecord::getAttribute()} allowing
     * straight getting of properties above object.
     *
     * @param string $name Name of the key to get
     * @return mixed Current value of that key
     * @see \MongoAR\ActiveRecord::getAttribute()
     * @since 0.1
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Setting value for specific key
     *
     * This method is only a wrapper for
     *  {@link \MongoAR\ActiveRecord::setAttribute()} allowing
     * straight setting of properties above object.
     *
     * @param string $name Name of the key to change
     * @param mixed $value New value for that key
     * @return \MongoAR\ActiveRecord Instance of self or return value of setter method
     * @see \MongoAR\ActiveRecord::setAttribute()
     * @since 0.1
     */
    public function offsetSet($offset, $value)
    {
        return $this->setAttribute($offset, $value);
    }

    /**
     * Unsetting specific key of the document
     * @param string $offset Name of the key to unset
     * @since 0.1
     */
    public function offsetUnset($offset)
    {
        unset($this->document[$offset]);
    }
}