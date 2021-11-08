<?php
namespace PL\Models\Referral\Item;

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

    protected $_referralId;
    protected $_rewardId;
    protected $_targetId;
    protected $_clientId;
    protected $_code;

    protected $_referralInstance;
    protected $_rewardInstance;     // SiteReward

    public function __construct(Referral $referralInstance = null) {

        if(is_null($referralInstance) != true){
            $this->setReferralInstance($referralInstance);
        }
        parent::__construct(Item::tableName);
        $this->setTableName(Item::tableName);
    }

    public static function getTableNameStatic(){
        return Item::tableName;
    }

    public static function getObjectInstanceStatic($data) : Item {
        return Item::getInstance($data);
    }

    public function getObjectInstance($data) : Item {
        return Item::getInstance($data);
    }

    public function getReferralInstance(){
        return $this->_referralInstance;
    }

    public function setReferralInstance(Referral $referralInstance){
        $this->_referralInstance = $referralInstance;
        $this->setReferralId($referralInstance->getId());
    }

    public function getRewardInstance(){
        return $this->_rewardInstance;
    }

    public function getReferralId(){
        return $this->_referralId;
    }

    public function setReferralId($referralId){
        $this->_referralId = $referralId;
    }

    public function getRewardId(){
        return $this->_referralId;
    }

    public function setRewardId($rewardId){
        $this->_rewardId = $rewardId;
    }

    public function getTargetId(){
        return $this->_targetId;
    }

    public function setTargetId($targetId){
        $this->_targetId = $targetId;
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getCode(){
        return $this->_code;
    }

    public function setCode($code){
        $this->_code = $code;
    }


    public static function isCode($code, $referralId){
        return self::_isCode($code, $referralId);
    }

    public function _isCode($code, $referralId = 0){
        if($code == '') return null;
        if($referralId == 0) $referralId = $this->getReferralId();
        if($referralId == 0) return null;

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `referralId` = ? AND `code` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($referralId, $code))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }


    public static function isTargetCode($code, $targetId, $referralId){
        return self::_isTargetCode($code, $targetId, $referralId);
    }

    public function _isTargetCode($code, $targetId, $referralId = 0){
        if($code == '') return null;
        if($referralId == 0) $referralId = $this->getReferralId();
        if($referralId == 0) return null;
        if($targetId == '') return null;

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `referralId` = ? AND `targetId` = ? AND `code` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->getShared('master/slave db');
        $data = $db->query($query, array($referralId, $targetId, $code))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }

    public function firstOrCreate($targetId, $clientId, $itemId, $reward)
    {
        $codeLength = 6; // to obj db

        if($targetId == '') return null;
        if($clientId == '') return null;
        if($itemId == '') return null;
        if($reward == '') return null;

        // SELECT * FROM `ReferralItem` WHERE `targetId` LIKE '/article-7269' AND `clientId` = 145
        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `targetId` LIKE ? AND `clientId` = ? AND `itemId` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query, array($targetId, $clientId, $itemId))->fetch();

        if(is_array($data) == true) {
            // 해당 페이지 referral code 존재하는 경우
            return static::getObjectInstanceStatic($data);
        } else {
            // 해당 페이지 referral code 존재안하는 경우
            // 새로 생성
            $newItem = array();
            $newItem['referralId']  = $this->getReferralId();
            $newItem['rewardId']    = $this->getRewardId();
            $newItem['itemId']      = $itemId;
            $newItem['reward']      = $reward;
            $newItem['targetId']    = $targetId;
            $newItem['clientId']    = $clientId;
            $newItem['regDate']     = Util::getDbNow();
            $newItem['status']      = Item::Status_Active;

            while (true){
                $newItem['code'] = Util::generateRandomAlphanumeric($codeLength);
                if($this->_isCode($newItem['code']) == null){
                    // 없는 파일 생성 가능
                    break;
                }
            };
            $ret = $this->addNew($newItem);
            if($ret >= 1){
                $this->getReferralInstance()->addItem();
                if($this->getReferralInstance()->getStatisticInstance() instanceof Statistic == true) $this->getReferralInstance()->getStatisticInstance()->addItem();
                return self::isItem($ret);
            }
            // 이 경우는 없겠지만
            return null;
        }

        /*
        $this->setTargetId($targetId);
        $this->setClientId($clientId);
        if($this->getOne() instanceof Item == true){
            return $this->getOne();
        } else {
            $newItem = array();
            $newItem['referralId'] = $this->getReferralId();
            $newItem['targetId']   = $targetId;
            $newItem['clientId']   = $clientId;
            $newItem['regDate']    = Util::getDbNow();
            $newItem['status']     = Item::Status_Active;

            while (true){
                $newItem['code'] = Util::generateRandomAlphanumeric($codeLength);

                if($this->_isCode($newItem['code']) == null){
                    // 없는 파일 생성 가능
                    break;
                }
            };

            $ret = $this->addNew($newItem);
            if($ret >= 1){
                $this->getReferralInstance()->addItem();
                if($this->getReferralInstance()->getStatisticInstance() instanceof Statistic == true) $this->getReferralInstance()->getStatisticInstance()->addItem();
                return self::isItem($ret);
            }

            // 이경우는 없겠지만
            return null;
        }
        */
    }


    public function getItemsPBF() {
        $where = array();
        if(Util::isInteger($this->getReferralId()) == true) $where[] = '`referralId` = ' . $this->getReferralId();
        if(Util::isInteger($this->getTargetId()) == true) $where[] = '`targetId` = ' . $this->getTargetId();
        if(Util::isInteger($this->getClientId()) == true) $where[] = '`clientId` = ' . $this->getClientId();
        if($this->getCode() != '') $where[] = '`code` = "' . $this->getCode() . '"';
        return $where;
    }

}
