<?php

namespace MongoAR;

use Iterator;
use Countable;
use MongoCollection;

/**
 * QueryBuilder for selecting data from Mongo databases
 * @since 0.1
 * @author Tomas Tatarko <tomas@tatarko.sk>
 * @license http://choosealicense.com/licenses/mit/ The MIT License
 * @link https://github.com/tatarko/MongoAR Official github repo
 */
class QueryBuilder implements Iterator, Countable
{
    /**
     * Default class name of populated records
     */
    const DEFAULT_CLASS = '\\MongoAR\\ActiveRecord';

    /**
     * Default table name
     */
    const DEFAULT_TABLE = 'ActiveRecord';

    /**
     * Mongo iterator for fetching records from database
     * @var \MongoCursor
     */
    protected $iterator;

    /**
     * Class name of the populated records
     * @var string
     */
    protected $rowsClass = self::DEFAULT_CLASS;

    /**
     * Name of the table to get records from
     * @var \MongoCollection
     */
    protected $table;

    /**
     * List of fields to fetch (fieldName => fetchOrNot)
     * @var bolean[]
     */
    protected $fields = [];

    /**
     * List of conditions for querying database
     * @var array
     */
    protected $query = [];

    /**
     * List sorting conditions
     * @var int[]
     */
    protected $order = [];

    /**
     * Number of results to fetch
     * @var int
     */
    protected $limit;

    /**
     * Creating new QueryBuilder from ActiveRecord model
     * @param \MongoAR\ActiveRecord $record Instance of active record
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public static function createInstanceFromAR(ActiveRecord $record)
    {
        $instance = new self($record->getTable());
        return $instance->setRowsClass(get_class($record));
    }

    /**
     * Creating new instance (setting table)
     * @param \MongoCollection $table Instance of mongo table to fetch data from
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function __construct(MongoCollection $table = null)
    {
        $this->setTable($table ? : ActiveRecord::getDatabase()->selectCollection(self::DEFAULT_TABLE));
        return $this;
    }

    /**
     * Setting class name for populatedRecords
     * @param string $class
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function setRowsClass($class)
    {
        $this->rowsClass = $class;
        return $this;
    }

    /**
     * Setting table to fetch data from
     * @param MongoCollection $table Instance of mongo table
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function setTable(MongoCollection $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Compare specific key in document with given value
     * @param string $what Name of the document key to compare
     * @param mixed $whit Given value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function equals($what, $whit)
    {
        $this->query[$what] = $whit;
        return $this;
    }

    /**
     * Fetch documents where specific value not equals to given value
     * @param string $what Key name of the document value
     * @param mixed $whit Value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function notEquals($what, $whit)
    {
        $this->query[$what]['$ne'] = $whit;
        return $this;
    }

    /**
     * Fetch documents where specific value is lower than given value
     * @param string $what Key name of the document value
     * @param numeric $value Value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function lowerThan($what, $value)
    {
        $this->query[$what]['$le'] = $value;
        return $this;
    }

    /**
     * Fetch documents where specific value is lower or equals than given value
     * @param string $what Key name of the document value
     * @param numeric $value Value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function lowerThanOrEquals($what, $value)
    {
        $this->query[$what]['$lte'] = $value;
        return $this;
    }

    /**
     * Fetch documents where specific value is greater than given value
     * @param string $what Key name of the document value
     * @param numeric $value Value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function greaterThan($what, $value)
    {
        $this->query[$what]['$ge'] = $value;
        return $this;
    }

    /**
     * Fetch documents where specific value is greater or equals than given value
     * @param string $what Key name of the document value
     * @param numeric $value Value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function greaterThanOrEquals($what, $value)
    {
        $this->query[$what]['$gte'] = $value;
        return $this;
    }

    /**
     * Specify which fields will be selected
     * @param array $fields List of fields to select
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function select(array $fields = [])
    {
        $this->fields += $fields;
        return $this;
    }

    /**
     * Specify that all fields will be fetched
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function selectAll()
    {
        $this->fields = [];
        return $this;
    }

    /**
     * Fetch documents where specific value is in range of given values
     * @param string $what Key name of the document value
     * @param numeric $value Value to compare
     * @param numeric $from Lower value to compare
     * @param numeric $to Higher value to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function between($what, $from, $to)
    {
        $this->query[$what] = [
                '$lt' => $from,
                '$gt' => $to
        ];
        return $this;
    }

    /**
     * Performs some callable function on all of records
     * @param \MongoAR\callable $callback
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function each(callable $callback)
    {
        foreach ($this as $record) {
            $callback($record);
        }
        return $this;
    }

    /**
     * MapReduce by given JS function
     * @param string $function JavaScript function
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function byFunction($function)
    {
        $this->query['$where'] = $function;
        return $this;
    }

    /**
     * Fetch documents where specific value equals to one of given values
     * @param string $what Key name of the document value
     * @param array $values List of values to compare
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function isIn($what, array $values)
    {
        $this->query[$what] = ['$in' => $values];
        return $this;
    }

    /**
     * Ascending sort by given named variable
     * @param string $what Name of the variable to sort by
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function ascendingBy($what)
    {
        $this->order[$what] = MongoCollection::ASCENDING;
        return $this;
    }

    /**
     * Descending sort by given named variable
     * @param string $what Name of the variable to sort by
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function descendigBy($what)
    {
        $this->order[$what] = MongoCollection::DESCENDING;
        return $this;
    }

    /**
     * Sort results by given criteria
     * @param array $sortConditions Sorting criteria
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function sort(array $sortConditions = [])
    {
        $this->getIterator()->sort(array_merge($this->order, $sortConditions));
        return $this;
    }

    /**
     * Limit fetched results to the given ammount
     * @param int $limit How many results will be fetched
     * @param int $offset How many results will be skipped
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = max(1, $limit);
        $this->getIterator()->limit($limit);
        if ($offset !== NULL) {
            $this->offset($offset);
        }
        return $this;
    }

    /**
     * How many results was/will be fetched
     * @return int
     * @since 0.1
     */
    public function count()
    {
        return $this->limit ? : $this->getQueryCount();
    }

    /**
     * How many results was matched by query
     * @return int
     * @since 0.1
     */
    public function getQueryCount()
    {
        return $this->getIterator()->count();
    }

    /**
     * Skip given ammount of results
     * @param int $offset
     * @return \MongoAR\QueryBuilder
     * @since 0.1
     */
    public function offset($offset)
    {
        $this->getIterator()->skip(max(0, (int) $offset));
        return $this;
    }

    /**
     * Find one result by given criteria
     * @param array $query Criteria to filter fetched results
     * @return \MongoAR\ActiveRecord
     * @since 0.1
     */
    public function find(array $query = [])
    {
        $class = $this->rowsClass;
        return new $class($this->table->findOne(array_merge($this->query, $query), $this->fields));
    }

    /**
     * Find all results by given criteria
     * @param array $query Criteria to filter fetched results
     * @return \MongoAR\ActiveRecord[]
     * @since 0.1
     */
    public function findAll(array $query = [])
    {
        $query = array_merge($this->query, $query);
        $this->iterator = $this->table->find($query, $this->fields);
        return $this;
    }

    /**
     * Get mongo cursor for current query
     * @return \MongoCursor
     * @since 0.1
     */
    public function getIterator()
    {
        if (!$this->iterator) {
            $this->findAll();
        }
        return $this->iterator;
    }

    /**
     * Fetching current record
     * @return \MongoAR\ActiveRecord
     * @since 0.1
     */
    public function current()
    {
        $classname = $this->rowsClass;
        return new $classname($this->getIterator()->current());
    }

    /**
     * Results current record's id
     * @return string
     * @since 0.1
     */
    public function key()
    {
        return $this->getIterator()->key();
    }

    /**
     * Moving to the next record
     * @since 0.1
     */
    public function next()
    {
        $this->getIterator()->next();
    }

    /**
     * Rewind interal pointer of fetched data
     * @since 0.1
     */
    public function rewind()
    {
        if (!empty($this->order)) {
            $this->sort();
        }
        $this->getIterator()->rewind();
    }

    /**
     * Checks if the cursor is reading a valid result
     * @return boolean
     * @since 0.1
     */
    public function valid()
    {
        return $this->getIterator()->valid();
    }
}