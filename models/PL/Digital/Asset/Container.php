<?php
namespace PL\Models\Digital\Asset;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Digital\Digital;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_digitalId;
    protected $_clientId;

    protected $_digitalInstance;
    protected $_clientInstance;


    public function __construct(Digital $digitalInstance = null) {
        parent::__construct(Asset::tableName);
        $this->setTableName(Asset::tableName);
        $this->setOrder('`id` ASC, ');

        if(is_null($digitalInstance) == false){
            $this->setDigitalInstance($digitalInstance);
            $this->setDigitalId($digitalInstance->getId());
        }
    }


    public static function getTableNameStatic(){
        return Asset::tableName;
    }

    public static function getObjectInstanceStatic($data) : Asset {
        return Asset::getInstance($data);
    }

    public function getObjectInstance($data) : Asset {
        return Asset::getInstance($data);
    }

    public function getDigitalInstance(){
        return $this->_digitalInstance;
    }

    public function setDigitalInstance($digitalInstance){
        $this->_digitalInstance = $digitalInstance;
    }

    public function getDigitalId(){
        return $this->_digitalId;
    }

    public function setDigitalId($digitalId){
        return $this->_digitalId = $digitalId;
    }


    public function getItemPBF() {
        $where = array();
        if($this->getDigitalId() >= 1) $where[] = '`digitalId` = ' . $this->getDigitalId();
    }

}