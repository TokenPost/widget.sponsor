<?php
namespace PL\Models\Site\Item\Point\Charge;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Site\Item\Item as SiteItem;


class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;

    protected $_typeId;
    protected $_pointId;
    protected $_tokenTypeId;
    protected $_tokenId;
    protected $_platformId;

    public function __construct(SiteItem $itemInstance = null) {

        if(is_null($itemInstance) != true){
            $this->setSiteItemInstance($itemInstance);
        }
        parent::__construct(Charge::tableName);
        $this->setTableName(Charge::tableName);
    }

    public static function getTableNameStatic(){
        return Charge::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Charge::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Charge::getInstance($date);
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

    public function setSiteItemInstance(SiteItem $itemInstance) {
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public function getTokenTypeId(){
        return $this->_tokenTypeId;
    }

    public function setTokenTypeId($tokenTypeId){
        $this->_tokenTypeId = $tokenTypeId;
    }

    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;
    }


    public function getItemsPBF() {
        $where = array();
        if (Util::isInteger($this->getItemId()) == true) $where[] = 'itemId = ' . $this->getItemId();
        return $where;
    }

    public function getChargeTotal($siteItemId) {
        if(empty($siteItemId)) return null;

        $db     = DI::getDefault()->getShared('db');
        $query  = "
            SELECT 
                SUM(A.requestPoint) as point
            FROM 
                SiteItemPointCharge A 
            WHERE 
                A.itemId='" .$siteItemId ."'
                AND A.status='" .Charge::Status_Completed  ."' 
        ";
        $data = $db->query($query)->fetch();
        if (!empty($data) && isset($data[0])) return $data[0];
        return null;
    }
}
