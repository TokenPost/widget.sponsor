<?php
namespace PL\Models\Client\LoginLog;

use PL\Models\Client\Client;
use Exception;
use PL\Models\Country\Country;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;


class LoginLog extends AbstractSingleton {

    const tableName = 'ClientLoginLog';

    /* 인증 수단 */
    const Type_default          = 0;
//    const Type_Certification    = 1;    // 본인 인증
    const Type_Email            = 1;    // 이메일 인증
    const Type_OTP              = 2;    // OTP 인증
    const Type_SMS              = 3;    // SMS 인증

    protected $_clientInstance;
    protected $_countryInstance;

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
        if(isset($this->_clientInstance) === false){
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getCountryInstance() {
        if(isset($this->_countryInstance) === false){
            $this->_countryInstance = Country::getInstance($this->getCountryId());
        }
        return $this->_countryInstance;
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

    public function getCountryId() {
        return $this->_info['countryId'];
    }

    public function setCountryId($countryId) {
        $this->_info['countryId']    = $countryId;
        $this->_changes['countryId'] = $this->_info['countryId'];
    }

    public function getCountry() {
        return $this->_info['country'];
    }

    public function setCountry($country) {
        $this->_info['country']    = $country;
        $this->_changes['country'] = $this->_info['country'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($ip) {
        $this->_info['ip']    = $ip;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getType() {
        return $this->_info['type'];
    }

    public function getTypeName() {
        $returnVal = '';
       
        switch($this->getType()) {
            case LoginLog::Type_default:
                $returnVal = '-';
                break;
            case LoginLog::Type_Email: 
                $returnVal = '이메일 인증';
                break;
            case LoginLog::Type_SMS: 
                $returnVal = 'SMS 인증';
                break;
            case LoginLog::Type_OTP: 
                $returnVal = 'OTP 인증';
                break;
        }

        return $returnVal;
    }

    public function setType($type) {
        $this->_info['type']    = $type;
        $this->_changes['type'] = $this->_info['type'];
    }

    public function getLocation() {
        return $this->_info['location'];
    }

    public function setLocation($location) {
        $this->_info['location']    = $location;
        $this->_changes['location'] = $this->_info['location'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regTimestamp']));
    }

    public function setRegTimestamp($regTimestamp) {
        $this->_info['regTimestamp']    = $regTimestamp;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }


    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }


}