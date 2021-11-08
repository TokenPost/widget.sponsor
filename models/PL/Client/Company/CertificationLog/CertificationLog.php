<?php

namespace PL\Models\Client\Company\CertificationLog;

use PL\Models\Client\Client;
use Exception;
use Phalcon\Db;
use Phalcon\Di;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Recruit\Item\Item;
use PL\Models\Recruit\Item\Container as ItemContainer;

use PL\Models\File\File;
use PL\Models\Recruit\Item\Apply\Apply;
use PL\Models\Recruit\Item\Apply\Container as ApplyContainer;

use PL\Models\File\Container as FileContainer;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 2018-01-24
 * Time: 오후 4:48
 */
class CertificationLog extends AbstractSingleton
{
    const tableName = 'RecruitCompanyCertificationLog';

    protected $_logoInstance;
    protected $_licenseInstance;
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

    public static function getTableNameStatic(){
        return self::tableName;
    }

    public static function getObjectInstanceStatic($date) : self {
        return self::getInstance($date);
    }

    public function getObjectInstance($date) : self {
        return self::getInstance($date);
    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getLicenseNumber() {
        return $this->_info['licenseNumber'];
    }

    public function setLicenseNumber($licenseNumber) {
        $this->_info['licenseNumber'] = $licenseNumber;
        $this->_changes['licenseNumber'] = $this->_info['licenseNumber'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId'] = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getResult() {
        return $this->_info['result'];
    }

    public function setResult($result) {
        $this->_info['result'] = $result;
        $this->_changes['result'] = $this->_info['result'];
    }

    public function getTrtEndCd() {
        return $this->_info['trtEndCd'];
    }

    public function setTrtEndCd($trtEndCd) {
        $this->_info['trtEndCd']    = $trtEndCd;
        $this->_changes['trtEndCd'] = $this->_info['trtEndCd'];
    }

    public function getTrtCntn() {
        return $this->_info['trtCntn'];
    }

    public function setTrtCntn($trtCntn) {
        $this->_info['trtCntn']    = $trtCntn;
        $this->_changes['trtCntn'] = $this->_info['trtCntn'];
    }

    public function getNrgtTxprYn() {
        return $this->_info['nrgtTxprYn'];
    }

    public function setNrgtTxprYn($nrgtTxprYn) {
        $this->_info['nrgtTxprYn']    = $nrgtTxprYn;
        $this->_changes['nrgtTxprYn'] = $this->_info['nrgtTxprYn'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
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

    public function getItemByKey($key){
        switch(trim($key)){
            default:
                return $this->_info[$key];
                break;
        }
    }

}