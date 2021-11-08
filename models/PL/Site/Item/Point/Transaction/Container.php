<?php
namespace PL\Models\Site\Item\Point\Transaction;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Site\Item\Item as SiteItem;
use PL\Models\Site\Item\Container as SiteItemContainer;


class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;

    protected $_typeId;
    protected $_senderId;
    protected $_pointId;
    protected $_tokenId;

    public function __construct(SiteItem $itemInstance = null) {

        if(is_null($itemInstance) != true){
            $this->setItemInstance($itemInstance);
        }
        parent::__construct(Transaction::tableName);
        $this->setTableName(Transaction::tableName);
    }

    public static function getTableNameStatic(){
        return Transaction::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Transaction::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Transaction::getInstance($date);
    }


    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId){
        $this->_itemId = $itemId;
    }

    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function setItemInstance(SiteItem $itemInstance){
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public static function getLastItemCount($adminId, $hours = 24, $typeId = '') {
        //$db     = DI::getDefault()->get('master/slave db');
        $db     = DI::getDefault()->getShared('db');

        if($typeId != '' && $typeId >= 1 && $typeId <= 3 ){
            $query = "SELECT COUNT(`id`) FROM `" . self::getTableNameStatic() . "` WHERE `typeId` = ? AND `senderId` = ? AND `regTime` >= ?";
            $data = $db->query($query, array(Transaction::Type_Withdrawal, $adminId, date('Y-m-d H:i:s', (time() - ($hours * 60 * 60) ) )) )->fetch();
        } else {
            $query = "SELECT COUNT(`id`) FROM `" . self::getTableNameStatic() . "` WHERE `senderId` = ? AND `regTime` >= ?";
            $data = $db->query($query, array($adminId, date('Y-m-d H:i:s', (time() - ($hours * 60 * 60) ) )) )->fetch();
        }
        return $data[0];

    }

    public static function getLastItemAmount($adminId, $hours = 24, $typeId = '') {
        //$db     = DI::getDefault()->get('master/slave db');
        $db     = DI::getDefault()->getShared('db');

        // 1 출금보낸 모든
        // 2 수신받은 모든
        // 3 이건...?
        if($typeId == 1){
            $query = "SELECT SUM(`sendQuantity`) FROM `" . self::getTableNameStatic() . "` WHERE `typeId` = ? AND `senderId` = ? AND `regTime` >= ?";
            $data = $db->query($query, array(Transaction::Type_Withdrawal, $adminId, date('Y-m-d H:i:s', (time() - ($hours * 60 * 60) ) )) )->fetch();
        }elseif($typeId == 2 ){
            $query = "SELECT SUM(`receiveQuantity`) FROM `" . self::getTableNameStatic() . "` WHERE `typeId` = ? AND `senderId` = ? AND `regTime` >= ?";
            $data = $db->query($query, array(Transaction::Type_Receive, $adminId, date('Y-m-d H:i:s', (time() - ($hours * 60 * 60) ) )) )->fetch();
        } else {
            return 0;
            /*$query = "SELECT COUNT(`id`) FROM `" . self::getTableNameStatic() . "` WHERE `senderId` = ? AND `regTime` >= ?";
            $data = $db->query($query, array($clientId, date('Y-m-d H:i:s', (time() - ($hours * 60 * 60) ) )) )->fetch();*/
        }
        return $data[0];

    }



    public function getSenderId(){
        return $this->_senderId;
    }

    public function setSenderId($senderId){
        $this->_senderId = $senderId;
    }

    public function getSiteItemId(){
        return $this->_siteItemId;
    }

    public function setSiteItemId($siteItemId){
        $this->_siteItemId = $siteItemId;
    }

    public function getTypeId(){
        return $this->_typeId;
    }

    public function setTypeId($typeId){
        $this->_typeId = $typeId;
    }

    public function getPointId(){
        return $this->_pointId;
    }

    public function setPointId($pointId){
        $this->_pointId = $pointId;
    }

    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;
    }


    public function getItemsPBF() {
        $where = array();

//        if (Util::isInteger($this->getClientId()) == true) $where[] = '`adminId` = ' . $this->getItemId();
        if (Util::isInteger($this->getSenderId()) == true) $where[] = '`senderId` = ' . $this->getSenderId();
        if (Util::isInteger($this->getTypeId()) == true) $where[] = '`typeId` = ' . $this->getTypeId();
        if (Util::isInteger($this->getPointId()) == true) $where[] = '`pointTokenId` = ' . $this->getPointId();
        if (Util::isInteger($this->getTokenId()) == true) $where[] = '`tokenId` = ' . $this->getTokenId();

        return $where;
    }

}
