<?php
namespace PL\Models\Client\Ref\Type;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;


class Container extends AbstractContainer {

    protected $_sortedItems;
    protected $_arrayItems;

    protected $_typeId;
    protected $_typeInstance;

    public function __construct() {
        parent::__construct(Type::tableName);
        $this->setTableName(Type::tableName);
        $this->setOrder('`id` asc, ');
    }

    public static function getTableNameStatic(){
        return Type::tableName;
    }

    public static function getObjectInstanceStatic($date) : Type {
        return Type::getInstance($date);
    }

    public function getObjectInstance($date) : Type {
        return Type::getInstance($date);
    }


    public function getItems($type = 0) {
        $query = 'SELECT * FROM ' . $this->getTableName();

        $where = array();
        if (isset($this->_filter)) {
            foreach ($this->_filter->getItems() as $filter) {
                $where[] = $filter->getFieldName() . ' ' . $filter->getCondition() . ' ' . $filter->getValue();
            }
        }

        if (sizeof(array_filter($where)) > 0) {
            $query .= ' WHERE ' . join(' AND ', array_filter($where));
        }

        $this->setQuery($query);
        $this->init();

        $limitStart = (($this->_page - 1) * $this->_listSize);
        $limitEnd   = $this->_listSize;

        $query .= " ORDER BY " . $this->_order . " `id` asc";
        $query .= " LIMIT " . $limitStart . " , " . $limitEnd;

        $result = $this->db->query($query)->fetchAll();

        if ($result === false) return array();

        $return = array();
        $arrayItems = array();

        foreach ($result as $key => $var) {
            $temp = Type::getInstance($var);
            $return[] = $temp;
            $arrayItems[$temp->getId()] = $temp->getTitle();
        }

        $this->_items = $return;
        $this->_arrayItems = $arrayItems;
        return $return;
    }


    public function getArrayItems() {
        if(isset($this->_arrayItems) != true){
            $this->getItems();
        }
        return $this->_arrayItems;
    }


}
