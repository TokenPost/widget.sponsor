<?php
namespace PL\Models\Client\Point\Lock;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Client;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_sortedItems;

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {
        parent::__construct(Lock::tableName);
        $this->setTableName(Lock::tableName);
        $this->setOrder('`id` asc, ');

        if(is_null($clientInstance) == false){
            $this->setClientInstance($clientInstance);
            $this->setClientId($clientInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Lock::tableName;
    }

    public static function getObjectInstanceStatic($date) : Lock {
        return Lock::getInstance($date);
    }

    public function getObjectInstance($date) : Lock {
        return Lock::getInstance($date);
    }


    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance($clientInstance){
        $this->_clientInstance = $clientInstance;
    }


    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }



    public function getItemsPBF() {
        $where = array();
        if (Util::isInteger($this->getClientId()) == true) $where[] = 'clientId = ' . $this->getClientId();
        return $where;
    }


}