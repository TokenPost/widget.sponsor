<?php
namespace PL\Models\Client\Activity\Calculate;

use PL\Models\Client\Client;
use PL\Models\Util\Util;
use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    protected $_date;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Calculate::tableName);
        $this->setTableName(Calculate::tableName);
    }

    public static function getTableNameStatic(){
        return Calculate::tableName;
    }

    public static function getObjectInstanceStatic($date) : Calculate {
        return Calculate::getInstance($date);
    }

    public function getObjectInstance($date) : Calculate {
        return Calculate::getInstance($date);
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getDate(){
        return $this->_date;
    }

    public function setDate($date){
        $this->_date = $date;
    }

    public function getItemsPBF() {
        $where = array();
        if(Util::isNumeric($this->getClientId()) == true) $where[] = '`clientId` = ' . $this->getClientId();
        if($this->getDate() != '') $where[] = '`date` = "' . $this->getDate() . '"';
        return $where;
    }

}
