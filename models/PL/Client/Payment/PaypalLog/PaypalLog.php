<?php
namespace PL\Models\Client\Payment\PaypalLog;

use Exception;
use PL\Models\Client\Client;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

class PaypalLog extends AbstractSingleton {

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

    const tableName = 'ClientPaymentPaypalLog';

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

    public function getClientPaymentId() {
        return $this->_info['clientPaymentId'];
    }

    public function setClientPaymentId($id) {
        $this->_info['clientPaymentId']    = trim($id);
        $this->_changes['clientPaymentId'] = $this->_info['clientPaymentId'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function getPaymentDateGMT($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function getTxnId() {
        return $this->_info['txn_id'];
    }

    public function getTxnType() {
        return $this->_info['txn_type'];
    }

    public function getBtnId() {
        return $this->_info['btn_id'];
    }

    public function getSubscrId() {
        return $this->_info['subscr_id'];
    }

    public function getCustom() {
        return $this->_info['custom'];
    }

    public function getPaymentDate() {
        return $this->_info['payment_date'];
    }

    public function getPaymentType() {
        return $this->_info['payment_type'];
    }

    public function getPaymentGross() {
        return $this->_info['payment_gross'];
    }

    public function getPaymentFee() {
        return $this->_info['payment_fee'];
    }

    public function getPaymentStatus() {
        return $this->_info['payment_status'];
    }

    public function getMcGross() {
        return $this->_info['mc_gross'];
    }

    public function getMcFee() {
        return $this->_info['mc_fee'];
    }

    public function getMcCurrency() {
        return $this->_info['mc_currency'];
    }

    public function getPayerId() {
        return $this->_info['payer_id'];
    }

    public function getPayerEmail() {
        return $this->_info['payer_email'];
    }

    public function getPayerStatus() {
        return $this->_info['payer_status'];
    }

    public function getFirstName() {
        return $this->_info['first_name'];
    }

    public function getLastName() {
        return $this->_info['last_name'];
    }

    public function getAddressName() {
        return $this->_info['address_name'];
    }

    public function getAddressCountryCode() {
        return $this->_info['address_country_code'];
    }

    public function getAddressCountry() {
        return $this->_info['address_country'];
    }

    public function getAddressState() {
        return $this->_info['address_state'];
    }

    public function getAddressCity() {
        return $this->_info['address_city'];
    }

    public function getAddressStreet() {
        return $this->_info['address_street'];
    }

    public function getAddressZip() {
        return $this->_info['address_zip'];
    }

    public function getAddress_status() {
        return $this->_info['address_status'];
    }

    public function getItemName() {
        return $this->_info['item_name'];
    }

    public function getItemNumber() {
        return $this->_info['item_number'];
    }

    public function getBusiness() {
        return $this->_info['business'];
    }

    public function getReceiverId() {
        return $this->_info['receiver_id'];
    }

    public function getReceiverEmail() {
        return $this->_info['receiver_email'];
    }

    public function getResidenceCountry() {
        return $this->_info['residence_country'];
    }

    public function getTransactionSubject() {
        return $this->_info['transaction_subject'];
    }

    public function getVerifySigh() {
        return $this->_info['verify_sigh'];
    }

    public function getCharset() {
        return $this->_info['charset'];
    }

    public function getProtectionEligibility() {
        return $this->_info['protection_eligibility'];
    }

    public function getNotifyVersion() {
        return $this->_info['notify_version'];
    }

    public function getSubscrDate() {
        return $this->_info['subscr_date'];
    }

    public function getPeriod3() {
        return $this->_info['period3'];
    }

    public function getReattempt() {
        return $this->_info['reattempt'];
    }

    public function getRecurring() {
        return $this->_info['recurring'];
    }

    public function getAmount3() {
        return $this->_info['amount3'];
    }

    public function getMcAmount3() {
        return $this->_info['mc_amount3'];
    }

    public function getIpnTrackId() {
        return $this->_info['ipn_track_id'];
    }

    public function getEtc() {
        return $this->_info['etc'];
    }

    public function getTotal() {
        return $this->_info['total'];
    }

}