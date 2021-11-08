<?php
namespace PL\Models\Site\Item\Api;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Item;
use PL\Models\Client\Client;

class Api extends AbstractSingleton {

    const tableName = 'SiteItemApi';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_itemInstance;

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

    public function getItemInstance() {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = Item::getInstance($this->getItemId());
        }
        return $this->_itemInstance;
    }

    public function getId()
    {
        return $this->_info['id'];
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

    public function getKey()
    {
        return $this->_info['key'];
    }

    public function setKey($var)
    {
        $this->_info['key'] = $var;
        $this->_changes['key'] = $this->_info['key'];
    }

    public function getSecret()
    {
        return $this->_info['secret'];
    }

    public function setSecret($var)
    {
        $this->_info['secret'] = $var;
        $this->_changes['secret'] = $this->_info['secret'];
    }

    public function getToken()
    {
        return $this->_info['token'];
    }

    public function setToken($var)
    {
        $this->_info['token'] = $var;
        $this->_changes['token'] = $this->_info['token'];
    }

    public function getToken2()
    {
        return $this->_info['token2'];
    }

    public function setToken2($var)
    {
        $this->_info['token2'] = $var;
        $this->_changes['token2'] = $this->_info['token2'];
    }

    public function getLastIssueDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['lastIssueDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastIssueDate($var)
    {
        $this->_info['lastIssueDate'] = $var;
        $this->_changes['lastIssueDate'] = $this->_info['lastIssueDate'];

    }
}