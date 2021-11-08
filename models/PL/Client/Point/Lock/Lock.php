<?php
namespace PL\Models\Client\Point\Lock;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Client\Ref\Type\Type as ClientRefType;

class Lock extends AbstractSingleton {

    const tableName = 'ClientPointLock';

    protected $_clientInstance;

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


    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }



    public function getId()
    {
        return $this->_info['id'];
    }

    public function getClientId()
    {
        return $this->_info['clientId'];
    }

    public function setClientId($var)
    {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getTokenId()
    {
        return $this->_info['tokenId'];
    }

    public function setTokenId($var)
    {
        $this->_info['tokenId'] = $var;
        $this->_changes['tokenId'] = $this->_info['tokenId'];
    }

    public function getPoint()
    {
        return $this->_info['point'];
    }

    public function setPoint($var)
    {
        $this->_info['point'] = $var;
        $this->_changes['point'] = $this->_info['point'];
    }

    public function getTargetTypeId()
    {
        return $this->_info['targetTypeId'];
    }

    public function setTargetTypeId($var)
    {
        $this->_info['targetTypeId'] = $var;
        $this->_changes['targetTypeId'] = $this->_info['targetTypeId'];
    }

    public function getTargetId()
    {
        return $this->_info['targetId'];
    }

    public function setTargetId($var)
    {
        $this->_info['targetId'] = $var;
        $this->_changes['targetId'] = $this->_info['targetId'];
    }

    public function getOrderId()
    {
        return $this->_info['orderId'];
    }

    public function setOrderId($var)
    {
        $this->_info['orderId'] = $var;
        $this->_changes['orderId'] = $this->_info['orderId'];
    }

    public function getDueDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['dueDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setDueDate($var)
    {
        $this->_info['dueDate'] = $var;
        $this->_changes['dueDate'] = $this->_info['dueDate'];
    }

    public function getDueTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['dueTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setDueTimestamp($var)
    {
        $this->_info['dueTimestamp'] = $var;
        $this->_changes['dueTimestamp'] = $this->_info['dueTimestamp'];
    }

    public function getRegIp()
    {
        return $this->_info['regIp'];
    }

    public function setRegIp($var)
    {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
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

    public function getRegTimstamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regTimstamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTimstamp($var)
    {
        $this->_info['regTimstamp'] = $var;
        $this->_changes['regTimstamp'] = $this->_info['regTimstamp'];
    }

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }
}