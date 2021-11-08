<?php
namespace PL\Models\Client\SubscribeLog;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\SubscribeLog\SubscribeLog;


class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(SubscribeLog::tableName);
        $this->setTableName(SubscribeLog::tableName);
    }

    public static function getTableNameStatic(){
        return SubscribeLog::tableName;
    }

    public static function getObjectInstanceStatic($date) : SubscribeLog {
        return SubscribeLog::getInstance($date);
    }

    public function getObjectInstance($date) : SubscribeLog {
        return SubscribeLog::getInstance($date);
    }


    public function getClientId(){
        return $this->_clientId;
    }

    private function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance() : Client {
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }



    public static function isItemByClientId($clientId, $result = ''){
        $query = 'SELECT * FROM ClientSubscribeLog';
        if(is_numeric($clientId) == true){
            $query .= ' WHERE clientId = ' . $clientId;
        } else {
            return false;
        }
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetch();
        if(is_array($data) == true) {
            if($result == 'obj') return SubscribeLog::getInstance($data);
            return true;
        }
        return false;
    }

    /**
     * ???
     */

    public function getMode(){
        return $this->_mode;
    }

    public function setMode($mode){
        $this->_mode = $mode;
    }

    public function getModeValue(){
        return $this->_modeValue;
    }

    public function setModeValue($modeValue){
        $this->_modeValue = $modeValue;
    }



    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}
