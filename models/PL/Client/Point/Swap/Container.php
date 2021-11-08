<?php
namespace PL\Models\Client\Point\Swap;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Client;


class Container extends AbstractContainer {


    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Swap::tableName);
        $this->setTableName(Swap::tableName);
    }

    public static function getTableNameStatic(){
        return Swap::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Swap::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Swap::getInstance($date);
    }


    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }


    public function getItemsPBF() {
        $where = array();

        if (Util::isInteger($this->getClientId()) == true) $where[] = '`clientId` = ' . $this->getClientId();

        return $where;
    }

}
