<?php
namespace PL\Models\Site\Item\Api;
use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;


use PL\Models\Util\Util;

use PL\Models\Site\Item\Item;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


class Container extends AbstractContainer {

    protected $_itemInstance;
    protected $_itemId;

    protected $_typeId;

    public function __construct(Item $itemInstance = null) {
        parent::__construct(Api::tableName);
        $this->setTableName(Api::tableName);

        if(is_null($itemInstance) == false) {
            $this->setItemInstance($itemInstance);
        }
    }

    public static function getTableNameStatic(){
        return Api::tableName;
    }

    public static function getObjectInstanceStatic($data) : Api {
        return Api::getInstance($data);
    }

    public function getObjectInstance($data) : Api {
        return Api::getInstance($data);
    }

    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function setItemInstance(Item $itemInstance){
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId) {
        $this->_itemId = $itemId;
    }



    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = "' . $this->getItemId() . '"';
        return $where;
    }
}