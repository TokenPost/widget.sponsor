<?php
namespace PL\Models\Referral\Statistic;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Referral\Referral;

class Container extends AbstractContainer {

    protected $_referralId;
    protected $_referralInstance;
    protected $_date;

    public function __construct(Referral $referralInstance = null) {

        if(is_null($referralInstance) != true){
            $this->setReferralInstance($referralInstance);
        }
        parent::__construct(Statistic::tableName);
        $this->setTableName(Statistic::tableName);
    }

    public static function getTableNameStatic(){
        return Statistic::tableName;
    }

    public static function getObjectInstanceStatic($data) : Statistic {
        return Statistic::getInstance($data);
    }

    public function getObjectInstance($data) : Statistic {
        return Statistic::getInstance($data);
    }

    public function getReferralInstance(){
        return $this->_referralInstance;
    }

    public function setReferralInstance(Referral $referralInstance){
        $this->_referralInstance = $referralInstance;
        $this->setReferralId($referralInstance->getId());
    }

    public function getReferralId(){
        return $this->_referralId;
    }

    public function setReferralId($referralId){
        $this->_referralId = $referralId;
    }

    public function getDate(){
        return $this->_date;
    }

    public function setDate($date){
        $this->_date = $date;
    }


    public static function isDate($date, $referralId){
        return self::_isDate($date, $referralId);
    }

    public function _isDate($date, $referralId = 0){
        if($date == '') return null;
        if($referralId == 0) $referralId = $this->getReferralId();
        if($referralId == 0) return null;

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `referralId` = ? AND `date` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($referralId, $date))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }

    public function firstOrCreate($date)
    {
        if($date == '' || strtotime($date) == false) return null;
        if(Util::isInteger($this->getReferralId()) != true) return null;

        $statisticInstance = self::_isDate($date);
        if($statisticInstance Instanceof Statistic != true){
            // 처음호출
            $newItem = array();
            $newItem['referralId']  = $this->getReferralId();
            $newItem['date']        = $date;
            $newItem['item']        = 0;
            $newItem['log']         = 0;
            $newItem['logIp']       = 0;
            $newItem['reward']      = 0;
            $newItem['rewardTotal'] = 0;

            $statisticContainer = new Container();
            $ret = $statisticContainer->addNew($newItem);
            if($ret >= 1){
                return self::isItem($ret);
            } else {
                return null;
            }
        } else {
            //기존에 있다.
            return $statisticInstance;
        }

    }


    public function getItemsPBF() {
        $where = array();
        if(Util::isInteger($this->getReferralId()) == true) $where[] = '`referralId` = ' . $this->getReferralId();
        if(Util::isDate($this->getDate(), 'Y-m-d') == true) $where[] = '`date` = ' . $this->getDate();
        return $where;
    }

}
