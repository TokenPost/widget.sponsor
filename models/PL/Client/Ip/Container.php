<?php
namespace PL\Models\Client\Ip;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Client\MyCalendar\Client;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Ip::tableName);
        $this->setTableName(Ip::tableName);
    }

    public static function getTableNameStatic(){
        return Ip::tableName;
    }

    public static function getObjectInstanceStatic($date) : Ip {
        return Ip::getInstance($date);
    }

    public function getObjectInstance($date) : Ip {
        return Ip::getInstance($date);
    }


    public function getClientId(){
        return $this->_clientId;
    }

    private function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }


    public function getCountIp() {
        $result = $this->db->query('SELECT count(*) FROM ClientIp WHERE clientId = ? and status = 0', array($this->getClientId()));

        $data = $result->fetch();
        return $data[0];
    }

    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }


}
