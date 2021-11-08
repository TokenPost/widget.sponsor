<?php
namespace PL\Models\Client\Sms\Auth;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;

class Auth extends AbstractSingleton {
    /**
     * log 저장
    */

    const tableName = 'ClientSmsAuth';

    const Result_Success = 0;
    const Result_Fail = 0;


    protected $_admin;
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

    public function getResultCode() {
        return $this->_info['resultCode'];
    }

    public function setResultCode($resultCode) {
        $this->_info['resultCode']    = $resultCode;
        $this->_changes['resultCode'] = $this->_info['resultCode'];
    }

    public function getResultMassage() {
        return $this->_info['resultMassage'];
    }

    public function setResultMassage($resultMassage) {
        $this->_info['resultMassage']    = $resultMassage;
        $this->_changes['resultMassage'] = $this->_info['resultMassage'];
    }

    public function getResponseData() {
        return $this->_info['responseData'];
    }

    public function setResponseData($responseData) {
        $this->_info['responseData']    = $responseData;
        $this->_changes['responseData'] = $this->_info['responseData'];
    }

    public function getSendMessage() {
        return $this->_info['sendMessage'];
    }

    public function setSendMessage($sendMessage) {
        $this->_info['sendMessage']    = $sendMessage;
        $this->_changes['sendMessage'] = $this->_info['sendMessage'];
    }

    public function getAuthCode() {
        return $this->_info['authCode'];
    }

    public function setAuthCode($authCode) {
        $this->_info['authCode']    = $authCode;
        $this->_changes['authCode'] = $this->_info['authCode'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($ip) {
        $this->_info['ip']    = $ip;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getSessionId() {
        return $this->_info['sessionId'];
    }

    public function setSessionId($sessionId) {
        $this->_info['sessionId']    = $sessionId;
        $this->_changes['sessionId'] = $this->_info['sessionId'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

}