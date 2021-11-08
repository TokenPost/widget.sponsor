<?php
namespace PL\Models\Site\Item\Point\Swap;


use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Site\Item\Item as SiteItem;

class Container extends AbstractContainer {


    protected $_itemId;
    protected $_itemInstance;

    public function __construct(SiteItem $siteItemInstance = null) {

        if(is_null($siteItemInstance) != true){
            $this->setSiteItemInstance($siteItemInstance);
        }
        parent::__construct(Swap::tableName);
        $this->setTableName(Swap::tableName);
    }

    public static function getTableNameStatic(){
        return Swap::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Swap::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Swap::getInstance($date);
    }


    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId){
        $this->_itemId = $itemId;
    }

    public function getSiteItemInstance(){
        return $this->_itemInstance;
    }

    public function setSiteItemInstance(SiteItem $siteItemInstance){
        $this->_itemInstance = $siteItemInstance;
        $this->setItemId($siteItemInstance->getId());
    }


    public function getItemsPBF() {
        $where = array();

        if (Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = ' . $this->getItemId();

        return $where;
    }

}
