<?php
namespace PL\Models\Client\Referral;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

class Container extends AbstractContainer {

    protected $_clientId;

    public function __construct() {
        parent::__construct(Referral::tableName);
        $this->setTableName(Referral::tableName);
    }

    public static function getTableNameStatic(){
        return Referral::tableName;
    }

    public static function getObjectInstanceStatic($date) : Referral {
        return Referral::getInstance($date);
    }

    public function getObjectInstance($date) : Referral {
        return Referral::getInstance($date);
    }

    public function getClientId(){
        return $this->_clientId;
    }

    /*
     * random
     * */
    public static function issueReferralCodeCode(){
        return Util::generateRandomString(6, '**');
    }

    /*
     * create new referCode
     * */
    public function addNewCreate($item){
        if(isset($item['referralCode']) == false || $item['referralCode'] == ''){
            $item['referralCode'] = self::issueReferralCodeCode();
        }
        return $item;
    }

    public function getItemsPBF(){
        $where = array();
        if($this->getClientId() >= 1) $where[] = 'clientId = ' . $this->getClientId();
        return $where;
    }

}