<?php
namespace PL\Models\Client\Ip;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;


class Ip extends AbstractSingleton {

    const tableName = 'ClientIp';

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

    public function isMatchClientId($clientId) {
        return ($clientId == $this->_info['clientId']);
    }




    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId']    = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($ip) {
        $this->_info['ip']    = $ip;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }


}