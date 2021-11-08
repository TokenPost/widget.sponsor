<?php
namespace PL\Models\Client\Point\Transaction\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\Point\Point;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


class Container extends AbstractContainer {


    protected $_clientId;
    protected $_clientInstance;

    protected $_typeId;
    protected $_addressId;
    protected $_tokenId;
    protected $_targetId;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct(Log::tableName);
        $this->setTableName(Log::tableName);
    }

    public static function getTableNameStatic(){
        return Log::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Log::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Log::getInstance($date);
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

    public function getAddressId(){
        return $this->_addressId;
    }

    public function setAddressId($addressId){
        $this->_addressId = $addressId;
    }

    public function getTypeId(){
        return $this->_typeId;
    }

    public function setTypeId($typeId){
        $this->_typeId = $typeId;
    }

    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;
    }

    // 내부일경우 1보다 크고 외부는 0
    public function getTargetId(){
        return $this->_targetId;
    }

    public function setTargetId($targetId){
        $this->_targetId = $targetId;
    }


    public function getItemsPBF() {
        $where = array();

        if (Util::isInteger($this->getClientId()) == true)  $where[] = 'clientId = ' . $this->getClientId();
        if (Util::isInteger($this->getAddressId()) == true) $where[] = 'addressId = ' . $this->getAddressId();
        if (Util::isInteger($this->getTypeId()) == true)    $where[] = 'typeId = ' . $this->getTypeId();
        if (Util::isInteger($this->getTokenId()) == true)   $where[] = 'pointTokenId = ' . $this->getTokenId();
        if (Util::isInteger($this->getTargetId()) == true)  $where[] = 'targetId = ' . $this->getTargetId();

        return $where;
    }

}
