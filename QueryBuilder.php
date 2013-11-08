<?php

namespace MongoAR;

use Iterator;
use Countable;

class QueryBuilder implements Iterator, Countable
{
    const DEFAULT_CLASS = 'MongoAR\\ActiveRecord';
    const DEFAULT_TABLE = 'ActiveRecord';

    protected $iterator;
    protected $rowsClass = self::DEFAULT_CLASS;
    protected $table = self::DEFAULT_TABLE;
    protected $fields = [];
    protected $query = [];
    protected $order = [];
    protected $limit;

    public static function createInstanceFromAR(ActiveRecord $record)
    {
        $instance = new self;
        $instance->setRowsClass(get_class($record))->setTable($record->getTable());
        return $instance;
    }

    public function setRowsClass($class)
    {
        $this->rowsClass = $class;
        return $this;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function equals($what, $whit)
    {
        $this->query[$what] = $whit;
        return $this;
    }

    public function notEquals($what, $whit)
    {
        $this->query[$what]['$ne'] = $whit;
        return $this;
    }

    public function lowerThan($what, $value)
    {
        $this->query[$what]['$le'] = $value;
        return $this;
    }

    public function lowerThanOrEquals($what, $value)
    {
        $this->query[$what]['$lte'] = $value;
        return $this;
    }

    public function greaterThan($what, $value)
    {
        $this->query[$what]['$ge'] = $value;
        return $this;
    }

    public function greaterThanOrEquals($what, $value)
    {
        $this->query[$what]['$gte'] = $value;
        return $this;
    }

    public function select(array $fields = array())
    {
        $this->fields = $fields;
        return $this;
    }

    public function inRange($what, $from, $to)
    {
        $this->query[$what] = [
                '$lt' => $from,
                '$gt' => $to
        ];
        return $this;
    }

    public function each(callable $callback)
    {
        foreach ($this as $record) {
            $callback($record);
        }
    }

    public function byFunction($function)
    {
        $this->query['$where'] = $function;
        return $this;
    }

    public function isIn($what, array $values)
    {
        $this->query[$what] = ['$in' => $values];
        return $this;
    }

    public function ascendingBy($what)
    {
        $this->order[$what] = 1;
        return $this;
    }

    public function descendigBy($what)
    {
        $this->order[$what] = -1;
        return $this;
    }

    public function sort(array $sortConditions = array())
    {
        $this->getIterator()->sort(array_merge($this->order, $sortConditions));
        return $this;
    }

    public function limit($limit, $offset = null)
    {
        $this->limit = max(1, $limit);
        $this->getIterator()->limit($limit);
        if ($offset !== NULL) {
            $this->offset($offset);
        }
        return $this;
    }

    public function count()
    {
        return $this->limit ? : $this->getQueryCount();
    }

    public function getQueryCount()
    {
        return $this->getIterator()->count();
    }

    public function offset($offset)
    {
        $this->getIterator()->skip(max(0, (int) $offset));
        return $this;
    }

    public function find(array $query = array())
    {
        $class = $this->rowsClass;
        return new $class($this->table->findOne(array_merge($this->query, $query), $this->fields));
    }

    public function findAll(array $query = array())
    {
        $query = array_merge($this->query, $query);
        $this->iterator = $this->table->find($query, $this->fields);
        return $this;
    }

    public function getIterator()
    {
        if (!$this->iterator) {
            $this->findAll();
        }
        return $this->iterator;
    }

    public function current()
    {
        $classname = $this->rowsClass;
        return new $classname($this->getIterator()->current());
    }

    public function key()
    {
        return $this->getIterator()->key();
    }

    public function next()
    {
        return $this->getIterator()->next();
    }

    public function rewind()
    {
        return $this->getIterator()->rewind();
    }

    public function valid()
    {
        return $this->getIterator()->valid();
    }
}