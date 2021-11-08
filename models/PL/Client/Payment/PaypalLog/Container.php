<?php
namespace PL\Models\Client\Payment\PaypalLog;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Client;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(PaypalLog::tableName);
        $this->setTableName(PaypalLog::tableName);
    }

    public static function getTableNameStatic(){
        return PaypalLog::tableName;
    }

    public static function getObjectInstanceStatic($date) : PaypalLog {
        return PaypalLog::getInstance($date);
    }

    public function getObjectInstance($date) : PaypalLog {
        return PaypalLog::getInstance($date);
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

    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

}
