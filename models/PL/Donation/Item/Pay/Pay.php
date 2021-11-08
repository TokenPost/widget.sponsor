<?php
namespace PL\Models\Donation\Item\Pay;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

use PL\Models\Donation\Item\Item;
use PL\Models\Donation\Container  as DonationContainer;
use PL\Models\Donation\Item\Container as ItemContainer;

use PL\Models\Site\Item\Container  as SiteItemContainer;



class Pay extends AbstractSingleton {

    /**
     * 상수 설정
     */

    const tableName = 'DonationItemPay';


    const Type_Fiat = 1;
    const Type_Token = 2;

    const Period_Onetime      = 1;
    const Period_Daily        = 2;
    const Period_Weekly       = 3;
    const Period_Biweekly     = 4;
    const Period_Monthly      = 5;
    const Period_Bimonthly    = 6;
    const Period_Quarterly    = 7;
    const Period_SemiAnnually = 8;
    const Period_Annually     = 9;


    const Status_Active     = 0;
    const Status_Inactive   = 1;
    const Status_Refund     = 3;

    protected $_itemInstance;
    protected $_siteItemInstance;

    /**
     * php7 이후 상속받기위해서는 Parameter 동일해야한다.
     */
    public static function getInstance($data, $keyIndex = 'id') {
        //public static function getInstance($data, $keyIndex = 'id') : Item {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getDonationInstance() {
        if (isset($this->_donationInstance) == false) {
            $this->_donationInstance = DonationContainer::isItem($this->getDonationId());
        }
        return $this->_donationInstance;
    }

    public function getItemInstance() {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = ItemContainer::isItem($this->getItemId());
        }
        return $this->_itemInstance;
    }

    public function getSiteItemInstance() {       
        $this->_itemInstance = $this->getItemInstance();

        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItemContainer::isItem($this->_itemInstance->getTargetId());
        }
        return $this->_siteItemInstance;
    }

    public function getClientInstance() {
        return ClientContainer::isItem($this->getClientId());
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getDonationId() {
        return $this->_info['donationId'];
    }

    public function setDonationId($var) {
        $this->_info['donationId'] = $var;
        $this->_changes['donationId'] = $this->_info['donationId'];
    }

    public function getItemId() {
        return $this->_info['itemId'];
    }

    public function setItemId($var) {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($var) {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($var) {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

    public function getPeriodId() {
        return $this->_info['periodId'];
    }

    public function setPeriodId($var) {
        $this->_info['periodId'] = $var;
        $this->_changes['periodId'] = $this->_info['periodId'];
    }

    public function getPaymentPayId() {
        return $this->_info['paymentPayId'];
    }

    public function setPaymentPayId($var) {
        $this->_info['paymentPayId'] = $var;
        $this->_changes['paymentPayId'] = $this->_info['paymentPayId'];
    }

    public function getPayCurrencyTypeId() {
        return $this->_info['payCurrencyTypeId'];
    }

    public function setPayCurrencyTypeId($var) {
        $this->_info['payCurrencyTypeId'] = $var;
        $this->_changes['payCurrencyTypeId'] = $this->_info['payCurrencyTypeId'];
    }

    public function getPayCurrencyId() {
        return $this->_info['payCurrencyId'];
    }

    public function setPayCurrencyId($var) {
        $this->_info['payCurrencyId'] = $var;
        $this->_changes['payCurrencyId'] = $this->_info['payCurrencyId'];
    }

    public function getPaid() {
        return number_format($this->_info['paid'], 4);
    }

    public function getDisplayPaid() {
        $paid = (int)$this->_info['paid'];
        return $paid;
    }

    public function setPaid($var) {
        $this->_info['paid'] = $var;
        $this->_changes['paid'] = $this->_info['paid'];
    }

    public function getFee() {
        return $this->_info['fee'];
    }

    public function setFee($var) {
        $this->_info['fee'] = $var;
        $this->_changes['fee'] = $this->_info['fee'];
    }

    public function getQuantity() {
        return $this->_info['quantity'];
    }

    public function setQuantity($var) {
        $this->_info['quantity'] = $var;
        $this->_changes['quantity'] = $this->_info['quantity'];
    }

    public function getDistribute() {
        return $this->_info['distribute'];
    }

    public function setDistribute($var) {
        $this->_info['distribute'] = $var;
        $this->_changes['distribute'] = $this->_info['distribute'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var) {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

//    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
//        $date = trim($this->_info['regTimestamp']);
//        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
//        return date($format, strtotime($date));
//    }
//
//    public function setRegTimestamp($var) {
//        $this->_info['regTimestamp'] = $var;
//        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
//    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }


    public function getQuantityWithNumberFormat($n) {
        return number_format($this->_info['quantity'], $n);
    }

}