<?php
namespace PL\Models\Client\Certification\Token;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Admin\Admin;
use PL\Models\Client\Client;
use PL\Models\Country\Country;
use PL\Models\Util\Util;

class Token extends AbstractSingleton {


    const tableName = 'ClientCertificationToken';

    const Status_Complete  = 0;
    const Status_Pending   = 1;
    const Status_Expired   = 2;

    protected $_admin;
    protected $_client;

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

    public function isAvailableIp(){
        if($this->getIp() == '') return true;
        $items = explode(',', $this->getIp());
        $clientIp = getIp();
        foreach($items as $var){
            if(trim($var) == $clientIp) return true;
        }
        //return strpos($this->getIp(), getIp());
        return false;
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
        $this->_info['clientId']    = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getCertificationId() {
        return $this->_info['certificationId'];
    }

    public function setCertificationId($certificationId) {
        $this->_info['certificationId']    = $certificationId;
        $this->_changes['certificationId'] = $this->_info['certificationId'];
    }

    public function getToken() {
        return $this->_info['token'];
    }

    public function setToken($token) {
        $this->_info['token']    = $token;
        $this->_changes['token'] = $this->_info['token'];
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    public function setSecret($secret) {
        $this->_info['secret']    = $secret;
        $this->_changes['secret'] = $this->_info['secret'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($regIp) {
        $this->_info['regIp']    = $regIp;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }


    public function getRegDate($format = 'Y-m-d') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Complete:
                return 'complete';
                break;

            case self::Status_Pending:
                return 'pending';
                break;

            case self::Status_Expired:
                return 'expired';
                break;

            default:
                return '(???)';
        }
    }
}