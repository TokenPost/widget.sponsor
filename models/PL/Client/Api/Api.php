<?php
namespace PL\Models\Client\Api;

use Exception;
use PL\Models\Admin\Admin;
use PL\Models\Client\Client;
use PL\Models\Country\Country;
use PL\Models\Util\Util;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;


class Api extends AbstractSingleton {


    const tableName = 'ClientApi';


    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

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


    public function isAvailableType($type){
        if($this->getType() != $type && $this->getType() != 'all'){
            return false;
        }
        return true;
    }

    public function checkLimit($mode = ''){
        if($this->getLimit() != -1){
            if($this->getLastCall() != ''){
                if($this->getLastCall('Y-m') != Util::getDbNow('Y-m')){
                    // 최근 콜과 이번달이 다르면 (새로운 달이 되었다) limit초기화.
                    $this->setLimit($this->getLimitPerMonth());
                }
            } else {
                // 처음 call.
                // 등록일과 비교하여 새로운 달일경우 초기화.
                if($this->getRegDate('Y-m') != Util::getDbNow('Y-m')){
                    $this->setLimit($this->getLimitPerMonth());
                }
            }
            $this->setLastCall(Util::getDbNow());

            if($this->getLimit() < 1){
                return false;
            } else {
                if($mode != 'check') $this->subtractLimit();
                return true;
            }
        } else {
            $this->setLastCall(Util::getDbNow());
        }
        return true;
    }

    public function getClientInstance() {
        if (isset($this->_client) == false) {
            $this->_client = Client::getInstance($this->getClientId());
        }
        return $this->_client;
    }

    public function getAdminInstance() {
        if (isset($this->_admin) == false) {
            $this->_admin = Admin::getInstance($this->getAdminId());
        }
        return $this->_admin;
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

    public function getAdminId() {
        return $this->_info['adminId'];
    }

    public function setAdminId($adminId) {
        $this->_info['adminId']    = $adminId;
        $this->_changes['adminId'] = $this->_info['adminId'];
    }

    public function getKey() {
        return $this->_info['key'];
    }

    public function setKey($key) {
        $this->_info['key']    = $key;
        $this->_changes['key'] = $this->_info['key'];
    }

    public function getSecret() {
        return $this->_info['secret'];
    }

    public function setSecret($secret) {
        $this->_info['secret']    = $secret;
        $this->_changes['secret'] = $this->_info['secret'];
    }

    public function getType() {
        return $this->_info['type'];
    }

    public function setType($type) {
        $this->_info['type']    = $type;
        $this->_changes['type'] = $this->_info['type'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($ip) {
        $this->_info['ip']    = $ip;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    //limit으로 변경.
    public function getRemaining() {
        return $this->getLimit();
    }

    public function getLimit() {
        return $this->_info['limit'];
    }

    public function setLimit($limit) {
        $this->_info['limit']    = $limit;
        $this->_changes['limit'] = $this->_info['limit'];
    }

    public function addLimit() {
        $this->setLimit($this->getLimit()+1);
        $this->saveChanges();
    }

    public function subtractLimit() {
        $this->setLimit($this->getLimit()-1);
        $this->saveChanges();
    }

    public function getLimitPerMonth() {
        return $this->_info['limitPerMonth'];
    }

    public function setLimitPerMonth($limitPerMonth) {
        $this->_info['limitPerMonth']    = $limitPerMonth;
        $this->_changes['limitPerMonth'] = $this->_info['limitPerMonth'];
    }

    public function getLastCall($format = 'Y-m-d') {
        if($this->_info['lastCall'] == '0000-00-00 00:00:00' || null) return '';
        return date($format, strtotime($this->_info['lastCall']));
    }

    public function setLastCall($lastCall) {
        $this->_info['lastCall']    = $lastCall;
        $this->_changes['lastCall'] = $this->_info['lastCall'];
    }

    public function getStartDate($format = 'Y-m-d') {
        if($this->_info['startDate'] == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($this->_info['startDate']));
    }

    public function setStartDate($startDate) {
        $this->_info['startDate']    = $startDate;
        $this->_changes['startDate'] = $this->_info['startDate'];
    }

    public function getEndDate($format = 'Y-m-d') {
        if($this->_info['endDate'] == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($this->_info['endDate']));
    }

    public function setEndDate($endDate) {
        $this->_info['endDate']    = $endDate;
        $this->_changes['endDate'] = $this->_info['endDate'];
    }

    public function getRegDate($format = 'Y-m-d') {
        if($this->_info['regDate'] == '0000-00-00 00:00:00') return '';
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
            case self::Status_Active:
                return 'active';
                //return 'normal';
                break;

            case self::Status_Inactive:
                return 'inactive';
                //return 'delete';
                break;

            default:
                return '(???)';
        }
    }
}