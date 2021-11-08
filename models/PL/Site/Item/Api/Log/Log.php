<?php
namespace PL\Models\Site\Item\Api\Log;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Api;
use PL\Models\Client\Client;

class Log extends AbstractSingleton {

    const tableName = 'SiteItemApiLog';

    protected $_apiInstance;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getApiInstance() {
        if (isset($this->_apiInstance) == false) {
            $this->_apiInstance = Api::getInstance($this->getApiId());
        }
        return $this->_apiInstance;
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getApiId()
    {
        return $this->_info['apiId'];
    }

    public function setApiId($var)
    {
        $this->_info['apiId'] = $var;
        $this->_changes['apiId'] = $this->_info['apiId'];
    }

    public function getItemId()
    {
        return $this->_info['itemId'];
    }

    public function setItemId($var)
    {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getType()
    {
        return $this->_info['type'];
    }

    public function setType($var)
    {
        $this->_info['type'] = $var;
        $this->_changes['type'] = $this->_info['type'];
    }

    public function getTitle()
    {
        return $this->_info['title'];
    }

    public function setTitle($var)
    {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getValue()
    {
        return $this->_info['value'];
    }

    public function setValue($var)
    {
        $this->_info['value'] = $var;
        $this->_changes['value'] = $this->_info['value'];
    }

    public function getMemo()
    {
        return $this->_info['memo'];
    }

    public function setMemo($var)
    {
        $this->_info['memo'] = $var;
        $this->_changes['memo'] = $this->_info['memo'];
    }

    public function getIp()
    {
        return $this->_info['ip'];
    }

    public function setIp($var)
    {
        $this->_info['ip'] = $var;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getRequest()
    {
        return $this->_info['request'];
    }

    public function setRequest($var)
    {
        $this->_info['request'] = $var;
        $this->_changes['request'] = $this->_info['request'];
    }

    public function getError()
    {
        return $this->_info['error'];
    }

    public function setError($var)
    {
        $this->_info['error'] = $var;
        $this->_changes['error'] = $this->_info['error'];
    }

    public function getResponse()
    {
        return $this->_info['response'];
    }

    public function setResponse($var)
    {
        $this->_info['response'] = $var;
        $this->_changes['response'] = $this->_info['response'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var)
    {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];

    }
}