<?php
namespace PL\Models\Client\Payment;

use Exception;
use PL\Models\Client\Client;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

class Payment extends AbstractSingleton {

    /**
     * const
     * 0. 결제 정상처리.
     * 1. 결제 실패
     * 2. token error
     * 3. 중복결제
     * 4. 기타 오류
     *
     */

    const Status_Success     = 0;
    const Status_Fail        = 1;
    const Status_Overlap     = 2;
    const Status_Tokenerror  = 3;
    const Status_Etcerror    = 4;

    protected  $_client;


    const tableName = 'ClientPayment';

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_string($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE token = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
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

    public function getClientTokenId() {
        return $this->_info['clientTokenId'];
    }

    public function setClientTokenId($clientTokenId) {
        $this->_info['clientTokenId']    = trim($clientTokenId);
        $this->_changes['clientTokenId'] = $this->_info['clientTokenId'];
    }

    public function getSubscrId() {
        return $this->_info['subscrId'];
    }

    public function setSubscrId($subscrId) {
        $this->_info['subscrId']    = trim($subscrId);
        $this->_changes['subscrId'] = $this->_info['subscrId'];
    }

    public function getTxnId() {
        return $this->_info['txnId'];
    }

    public function setTxnId($txnId) {
        $this->_info['txnId']    = trim($txnId);
        $this->_changes['txnId'] = $this->_info['txnId'];
    }

    public function getMode() {
        return $this->_info['mode'];
    }

    public function setMode($mode) {
        $this->_info['mode']    = $mode;
        $this->_changes['mode'] = $this->_info['mode'];
    }

    // 임시용.
    public function getType() {
        return $this->getPeriod();
    }
    public function setType($type) {
        $this->setPeriod($type);
    }

    public function getPeriod() {
        return $this->_info['period'];
    }

    public function setPeriod($period) {
        $this->_info['period']    = trim($period);
        $this->_changes['period'] = $this->_info['period'];
    }

    public function getCurrency() {
        return $this->_info['currency'];
    }

    public function setCurrency($currency) {
        $this->_info['currency']    = trim($currency);
        $this->_changes['currency'] = $this->_info['currency'];
    }

    public function getPayment() {
        return $this->_info['payment'];
    }

    public function setPayment($payment) {
        $this->_info['payment']    = trim($payment);
        $this->_changes['payment'] = $this->_info['payment'];
    }

    public function getPaymentDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['paymentDate']));
    }

    public function setPaymentDate($paymentDate) {
        $this->_info['paymentDate']    = trim($paymentDate);
        $this->_changes['paymentDate'] = $this->_info['paymentDate'];
    }

    public function getStartDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['startDate']));
    }

    public function setStartDate($regDate) {
        $this->_info['startDate']    = trim($regDate);
        $this->_changes['startDate'] = $this->_info['startDate'];
    }

    public function getEndDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['endDate']));
    }

    public function setEndDate($regDate) {
        $this->_info['endDate']    = trim($regDate);
        $this->_changes['endDate'] = $this->_info['endDate'];
    }

    public function getCancelDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['cancelDate']));
    }

    public function setCancelDate($regDate) {
        $this->_info['cancelDate']    = trim($regDate);
        $this->_changes['cancelDate'] = $this->_info['cancelDate'];
    }


    public function getSignalStartDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['signalStartDate']));
    }

    public function setSignalStartDate($regDate) {
        $this->_info['signalStartDate']    = trim($regDate);
        $this->_changes['signalStartDate'] = $this->_info['signalStartDate'];
    }

    public function getSignalEndDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['signalEndDate']));
    }

    public function setSignalEndDate($regDate) {
        $this->_info['signalEndDate']    = trim($regDate);
        $this->_changes['signalEndDate'] = $this->_info['signalEndDate'];
    }

    public function getSignalCancelDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['signalCancelDate']));
    }

    public function setSignalCancelDate($regDate) {
        $this->_info['signalCancelDate']    = trim($regDate);
        $this->_changes['signalCancelDate'] = $this->_info['signalCancelDate'];
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
                return 'User canceled';
                break;
            case 2:
                return 'Payment overlap';
                break;
            case 3:
                return 'TokenError';
                break;
            case 4:
                return 'Etc Error';
                break;
        }
    }

}