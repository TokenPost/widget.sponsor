<?php
namespace PL\Models\Client\Sms\Auth;

use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Util\Util;

class Container extends AbstractContainer {


    protected $_client;
    protected $_clientId;

    public function __construct() {
        parent::__construct(Auth::tableName);
        $this->setTableName(Auth::tableName);
    }

    public static function getTableNameStatic(){
        return Auth::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Auth::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Auth::getInstance($date);
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getLimitRequestTime($clientId = '' ){
        $filterContainer = new FilterContainer();

        if($clientId != '' && $clientId != 0){
            $filterContainer->add(new Filter('clientId', '=', $clientId));
        } else {
            $filterContainer->add(new Filter('sessionId', '=', '"' . session_id() . '"'));

        }

        $this->setListSize(3);
        $items = $this->getItems();

        if(sizeof($items) < 3){
            return true;
        } else {
            $limitTime = $items[2]->getRegDate();
            if(date("Y-m-d H:i:s", strtotime("-3 minute", strtotime(Util::getDbNow('Y-m-d H:i:s', 'kst')))) < $limitTime){
                return false;
            }
        }
        return true;
    }

    public function checkSmsAuth($reqAuthCode, $clientId){
        $filterContainer = new FilterContainer();

        if($clientId != '' && $clientId != 0){
            $filterContainer->add(new Filter('clientId', '=', $clientId));
        } else {
            $filterContainer->add(new Filter('sessionId', '=', '"' . session_id() . '"'));
        }

        $this->setFilterContainer($filterContainer);
        $this->setListSize(1);
        $authItem = $this->getItems();

//        var_dump($authItem);

        $issuedAuthCode = $authItem[0]->getAuthCode();
        $issuedAuthCodeRegTime = $authItem[0]->getRegDate();

        if(date("Y-m-d H:i:s", strtotime("-3 minute", strtotime(Util::getDbNow('Y-m-d H:i:s', 'kst')))) > $issuedAuthCodeRegTime){
            return 1;
        }

        if($reqAuthCode != $issuedAuthCode){
            return 2;
        }
        return 0;
    }

    public function getItemsPBF(){
        $where = array();
        if($this->getClientId() >= 1) $where[] = '`clientId` = ' . $this->getClientId();
        return $where;
    }

}
