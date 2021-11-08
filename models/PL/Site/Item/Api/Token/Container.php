<?php
namespace PL\Models\Site\Item\Api\Token;
use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;


use PL\Models\Util\Util;

use PL\Models\Site\Item\Api\Api;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


class Container extends AbstractContainer {

    protected $_apiInstance;
    protected $_apiId;

    protected $_typeId;

    public function __construct(Api $apiInstance = null) {
        parent::__construct(Token::tableName);
        $this->setTableName(Token::tableName);

        if(is_null($apiInstance) == false) {
            $this->setApiInstance($apiInstance);
        }
    }

    public static function getTableNameStatic(){
        return Token::tableName;
    }

    public static function getObjectInstanceStatic($data) : Token {
        return Token::getInstance($data);
    }

    public function getObjectInstance($data) : Token {
        return Token::getInstance($data);
    }

    public function getApiInstance(){
        return $this->_apiInstance;
    }

    public function setApiInstance(Api $apiInstance){
        $this->_apiInstance = $apiInstance;
        $this->setApiId($apiInstance->getId());
    }

    public function getApiId(){
        return $this->_apiId;
    }

    public function setApiId($apiId) {
        $this->_apiId = $apiId;
    }



    public function getApisPBF(){
        $where = array();
        if(Util::isInteger($this->getApiId()) == true) $where[] = '`apiId` = "' . $this->getApiId() . '"';
        return $where;
    }
}