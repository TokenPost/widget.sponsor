<?php
namespace PL\Models\Client\Payment\Token;

use Exception;
use PL\Models\Client\Client;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

class Token extends AbstractSingleton {

    /**
     * const
     * 0. 결제 정상처리.
     * 1. paypal 응답대기
     * 2. paypal 에서 1차 response 수신.
     * 3. 1차 수신후 tx api 수신받았으나 결제 에러. 정상처리일경우 0으로
     *
     * 5. 기한 만료. 1에서 새로고침하거나 시간이 오래된 경우등.
     */

    const Status_Success      = 0;
    const Status_Standby      = 1;
    const Status_Response     = 2;
    const Status_Tokenerror   = 3;
    const Status_Clienterror  = 4;
    const Status_Expired      = 5;

    protected  $_client;

    private $_changes;

    const tableName = 'ClientPaymentPaypalLog';

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE id = ?', array($data));

            $data = $result->fetch();
        } elseif (is_string($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE token = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception('token key error');
        }

        return parent::getInstance($data, 'id');
    }


    public function getClientInstance() {
        if (isset($this->_client) == false) {
            $this->_client = Client::getInstance($this->getClientId());
        }
        return $this->_client;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId']    = trim($clientId);
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getToken() {
        return $this->_info['token'];
    }

    public function isToken($token) {
        return $this->getToken() == $token ? true : false;
    }

    public function getTx() {
        return $this->_info['tx'];
    }

    public function setTx($tx) {
        $this->_info['tx']    = trim($tx);
        $this->_changes['tx'] = $this->_info['tx'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($regIp) {
        $this->_info['regIp']    = trim($regIp);
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = trim($regDate);
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = trim($status);
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName() {
        switch ($this->getStatus()) {
            case 0:
                return 'Success';
                break;
            case 1:
                return 'StandBy';
                break;
            case 2:
                return 'Response';
                break;
            case 3:
                return 'TX API Error';
                break;
            case 5:
                return 'Expired';
                break;
        }
    }

}