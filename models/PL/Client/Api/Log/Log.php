<?php
namespace PL\Models\Client\Api\Log;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;


class Log extends AbstractSingleton {

    const tableName = 'ClientApiLog';

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

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

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientApiId() {
        return $this->_info['clientApiId'];
    }

    public function setClientApiId($clientApiId) {
        $this->_info['clientApiId']    = $clientApiId;
        $this->_changes['clientApiId'] = $this->_info['clientApiId'];
    }

    public function getType() {
        return $this->_info['type'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function getRequest() {
        return $this->_info['request'];
    }

    public function getError() {
        return $this->_info['error'];
    }

    public function getResponse() {
        return $this->_info['response'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

}