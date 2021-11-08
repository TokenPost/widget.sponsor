<?php
namespace PL\Models\Donation\Item;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Donation\Donation;



class Container extends AbstractContainer {

    protected $_donationInstance;
    protected $_donationId;

    protected $_targetId;


    public function __construct(Donation $donationInstance = null) {
        parent::__construct(Item::tableName);
        $this->setTableName(Item::tableName);

        if($donationInstance != null){
            $this->_donationInstance = $donationInstance;
            $this->_donationId = $donationInstance->getId();
        } else {
            $this->_donationInstance = null;
            $this->_donationId = 0;
        }
    }

    public static function getTableNameStatic(){
        return Item::tableName;
    }

    public static function getObjectInstanceStatic($date) : Item {
        return Item::getInstance($date);
    }

    public function getObjectInstance($date) : Item {
        return Item::getInstance($date);
    }


    public function getDonationInstance() {
        return $this->_donationInstance;
    }

    public function getDonationId() {
        return $this->_donationId;
    }

    public function getTargetId() {
        return $this->_targetId;
    }

    public function setTargetId($targetId) {
        $this->_targetId = $targetId;
    }



    public function firstOrCreate($targetId)
    {
        if(Util::isInteger($this->getDonationId()) != true) return null;
        if(Util::isInteger($targetId) != true) return null;

        $this->setTargetId($targetId);
        $itemInstance = $this->getOneFlushed();
        if($itemInstance instanceof Item == true){
            return $itemInstance;
        }


        $newItem = array();
        $newItem['donationId']          = $this->getDonationId();
        $newItem['targetId']            = $targetId;
        $newItem['pay']                 = 0;
        $newItem['krwConvertedAmount']  = 0;
        $newItem['krwConvertedFee']     = 0;
        $newItem['krwCount']            = 0;
        $newItem['krwAmount']           = 0;
        $newItem['krwFee']              = 0;
        $newItem['usdCount']            = 0;
        $newItem['usdAmount']           = 0;
        $newItem['usdFee']              = 0;
        $newItem['newsCount']           = 0;
        $newItem['newsAmount']          = 0;
        $newItem['newsFee']             = 0;
        $newItem['pointCount']          = 0;
        $newItem['pointAmount']         = 0;
        $newItem['pointFee']            = 0;
        $newItem['lastPayDate']         = "0000-00-00 00:00:00";
//        $newItem['lastPayTimestamp']    = "0000-00-00 00:00:00";
        $newItem['regDate']             = Util::getLocalTime();
//        $newItem['regTimestamp']        = Util::getLocalTime();
        $newItem['status']              = Item::Status_Active;


        $ret = $this->addNew($newItem);
        if($ret >= 1){

            $this->getDonationInstance()->addItem();

            return self::isItem($ret);
        }

        // 이경우는 없겠지만
        return null;

    }

    
    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getDonationId()) == true) $where[] = '`donationId` = '  . $this->getDonationId();
        if(Util::isInteger($this->getTargetId()) == true) $where[] = '`targetId` = '  . $this->getTargetId();
        return $where;
    }

}
