<?php
namespace PL\Models\Referral\Item\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Client;
use PL\Models\Referral\Referral;
use PL\Models\Referral\Item\Item;
use PL\Models\Referral\Statistic\Statistic;

class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;
    protected $_clientId;

    public function __construct(Item $itemInstance = null) {

        if(is_null($itemInstance) != true){
            $this->setItemInstance($itemInstance);
        }
        parent::__construct(Log::tableName);
        $this->setTableName(Log::tableName);
    }

    public static function getTableNameStatic(){
        return Log::tableName;
    }

    public static function getObjectInstanceStatic($data) : Log {
        return Log::getInstance($data);
    }

    public function getObjectInstance($data) : Log {
        return Log::getInstance($data);
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

    public function setItemId($itemId){
        $this->_itemId = $itemId;
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function recordLog($clientId = 0, $regIp, $reward = 0, $activityId = 0, $activityDate) {
        if($regIp == '') return null;
        if($activityDate == '') return null;

        $newLogItem['itemId']       = $this->getItemId();
        $newLogItem['clientId']     = $clientId;
        $newLogItem['reward']       = $reward;
        $newLogItem['date']         = $activityDate;
        $newLogItem['activityId']   = $activityId;
        $newLogItem['regIp']        = $regIp;
        $newLogItem['regDate']      = Util::getDbNow();

        $result  = $this->addNew($newLogItem);
        if($result >= 1){
            $this->getItemInstance()->addLog();
            $this->getItemInstance()->getReferralInstance()->addLog();
            if($this->getItemInstance()->getReferralInstance()->getStatisticInstance() instanceof Statistic == true) $this->getItemInstance()->getReferralInstance()->getStatisticInstance()->addLog();
            return self::isItem($result);
        }
    }

    public function getItemsPBF() {
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = ' . $this->getItemId();
        if(Util::isInteger($this->getClientId()) == true) $where[] = '`clientId` = ' . $this->getClientId();
        return $where;
    }

}
