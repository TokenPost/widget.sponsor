<?php
namespace PL\Models\Client\Api;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Client\Api\Api;
use PL\Models\Client\Client;

class Container extends AbstractContainer {


    protected $_client;

    public function __construct() {
        parent::__construct(Api::tableName);
        $this->setTableName(Api::tableName);
    }

    public static function getTableNameStatic(){
        return Api::tableName;
    }

    public static function getObjectInstanceStatic($date) : Api {
        return Api::getInstance($date);
    }

    public function getObjectInstance($date) : Api {
        return Api::getInstance($date);
    }

    public static function isApi($key, $secret = null){
        $query = 'SELECT * FROM ClientApi';
        if(is_numeric($key) == true && $secret == null){
            $query .= ' WHERE id = ' . $key;
        } elseif($key && $secret) {
            $query .= ' WHERE `key` = "' . $key . '" AND `secret` = "' . $secret . '"';
        } else {
            return false;
        }
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetchAll();

        if(sizeof($data) == 1) {
            return Api::getInstance($data[0]);
        }
        return false;
    }


    public function getLastApi(){
        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('clientId', '=', $this->getClientId()));
        $filterContainer->add(new Filter('status', '=', '0'));
        $this->setListSize(1);
        $this->setFilterContainer($filterContainer);
        $ret = $this->getItems();

        if(sizeof($ret) != 0){
            return $ret[0];
        } else {
            return 0;
        }
    }

    public function setClient($client){
        $this->_client = $client;
    }

    public function getClient(){
        return $this->_client;
    }

    public function getClientId(){
        return $this->getClient()->getId();
    }

}
