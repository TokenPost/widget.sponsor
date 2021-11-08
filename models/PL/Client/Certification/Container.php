<?php
namespace PL\Models\Client\Certification;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Client\Certification\Certification;
use PL\Models\Client\Client;

class Container extends AbstractContainer {


    protected $_client;
    protected $_clientId;

    public function __construct() {
        parent::__construct(Certification::tableName);
        $this->setTableName(Certification::tableName);
    }

    public static function getTableNameStatic(){
        return Certification::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Certification::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Certification::getInstance($date);
    }



    public function setClient(Client $client){
        $this->_client = $client;
        $this->setClientId($client->getId());
    }

    public function getClient() : Client {
        return $this->_client;
    }

    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }


    public static function issueSecret(){
        return Util::generateRandomString(32, '**');
    }

    public function addNewCreate($item){
        if(isset($item['secret']) == false || $item['secret'] == ''){
            $item['secret'] = self::issueSecret();
        }
        return $item;
    }

    public function getItemsPBF(){
        $where = array();
        if($this->getClientId() >= 1) $where[] = 'clientId = ' . $this->getClientId();
        return $where;
    }

}
