<?php
namespace PL\Models\Client\SubscribeLog;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Util\Util;


class SubscribeLog extends AbstractSingleton {

    protected $_fileContainer;

    const tableName = 'ClientSubscribeLog';

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

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId']    = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getEmail() {
        return $this->_info['email'];
    }

    public function setEmail($email) {
        $this->_info['email']    = $email;
        $this->_changes['email'] = $this->_info['email'];
    }

    public function getType() {
        return $this->_info['type'];
    }

    public function setType($type) {
        $this->_info['type']    = $type;
        $this->_changes['type'] = $this->_info['type'];
    }

    public function getAgent() {
        return $this->_info['agent'];
    }

    public function setAgent($agent) {
        $this->_info['agent']    = $agent;
        $this->_changes['agent'] = $this->_info['agent'];
    }

    public function getUrl() {
        return $this->_info['url'];
    }

    public function setUrl($url) {
        $this->_info['url']    = $url;
        $this->_changes['url'] = $this->_info['url'];
    }

    public function getCountry() {
        return $this->_info['country'];
    }

    public function setCountry($country) {
        $this->_info['country']    = $country;
        $this->_changes['country'] = $this->_info['country'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($regIp) {
        $this->_info['regIp']    = $regIp;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

}