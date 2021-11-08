<?php
namespace PL\Models\Site\Item\Information;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Item as SiteItem;

class Container extends AbstractContainer {

    protected $_itemInstance;
    protected $_itemId;

    public function __construct(SiteItem $itemInstance = null) {
        parent::__construct(Information::tableName);
        $this->setTableName(Information::tableName);

        if(is_null($itemInstance) == false){
            $this->_itemInstance = $itemInstance;
            $this->_itemId = $itemInstance->getId();
        } else {
            $this->_itemInstance = null;
            $this->_itemId = 0;
        }
    }

    public static function getTableNameStatic(){
        return Information::tableName;
    }

    public static function getObjectInstanceStatic($date) : Information {
        return Information::getInstance($date);
    }

    public function getObjectInstance($date) : Information {
        return Information::getInstance($date);
    }

    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function getItemId(){
        return $this->_itemId;
    }



    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = "'. $this->getItemId() . '"';
        return $where;
    }
}