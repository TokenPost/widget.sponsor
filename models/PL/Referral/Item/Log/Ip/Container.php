<?php
namespace PL\Models\Referral\Item\Log\Ip;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Referral\Item\Item;
use PL\Models\Referral\Item\Log\Log;
use PL\Models\Referral\Statistic\Statistic;

class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;
    protected $_logId;
    protected $_logInstance;

    public function __construct(Item $itemInstance = null) {

        if(is_null($itemInstance) != true){
            $this->setItemInstance($itemInstance);
        }
        parent::__construct(Ip::tableName);
        $this->setTableName(Ip::tableName);
    }

    public static function getTableNameStatic(){
        return Ip::tableName;
    }

    public static function getObjectInstanceStatic($data) : Ip {
        return Ip::getInstance($data);
    }

    public function getObjectInstance($data) : Ip {
        return Ip::getInstance($data);
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


    public function getLogInstance(){
        return $this->_logInstance;
    }

    public function setLogInstance(Log $logInstance){
        $this->_logInstance = $logInstance;
        $this->setLogId($logInstance->getId());
    }

    public function getLogId(){
        return $this->_logId;
    }

    public function setLogId($logId){
        $this->_logId = $logId;
    }

    public function findFirst($date, $clientIp)
    {
        if(Util::isInteger($this->getItemId()) != true) return null;
        if(Util::isDate($date, 'Y-m-d') != true) return null;


        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `itemId` = ? AND `date` = ? AND `ip` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($this->getItemId(), $date, $clientIp))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }

        return null;
    }

    public function recordLog($date, $clientIp = '', $logId = 0)
    {
        if(Util::isInteger($this->getItemId()) != true) return null;
        if(Util::isDate($date, 'Y-m-d') != true) return null;
        $newItem = array();
        $newItem['itemId']  = $this->getItemId();
        $newItem['date']    = $date;
        $newItem['ip']      = $clientIp;
        $newItem['logId']   = $logId;
        $newItem['regDate'] = Util::getDbNow();

        $ret = $this->addNew($newItem);
        if($ret >= 1){
            $ipInstance = self::isItem($ret);
            if($ipInstance instanceof Ip == true){
                $ipInstance->getItemInstance()->addLogIp();
                $ipInstance->getItemInstance()->getReferralInstance()->addLogIp();
                if($ipInstance->getItemInstance()->getReferralInstance()->getStatisticInstance() instanceof Statistic == true) $ipInstance->getItemInstance()->getReferralInstance()->getStatisticInstance()->addLogIp();
            }
            return self::isItem($ret);
        }

        return null;
    }

    public function firstOrCreate($date, $clientIp, $logId = 0)
    {
        $ipInstance = $this->findFirst($date, $clientIp);

        if($ipInstance instanceof Ip == true) {
            return $ipInstance;
        } else {
            $ret = $this->recordLog($date, $clientIp, $logId);
            if($ret >= 1){
                return self::isItem($ret);
            }
        }

        return null;
    }




    public function getItemsPBF() {
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = ' . $this->getItemId();
        if(Util::isInteger($this->getLogId()) == true) $where[] = '`logId` = ' . $this->getLogId();
        return $where;
    }

}
