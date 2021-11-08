<?php
namespace PL\Models\Donation;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Donation\Item\Container as ItemContainer;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;
use PL\Models\Admin\Admin;
use PL\Models\Admin\Container as AdminContainer;

class Donation extends AbstractSingleton {

    /**
     * 상수 설정
     */

    const tableName = 'Donation';


    const Status_Active     = 0;  // Active
    const Status_Inactive   = 1;  // Inactive

    const Target_Site     = 1;
    const Target_Article  = 2;
    const Target_Client   = 3;
    const Target_Reporter = 4;


    protected $_itemContainer;
    protected $_eventContainer;

    /**
     * php7 이후 상속받기위해서는 Parameter 동일해야한다.
     */
    public static function getInstance($data, $keyIndex = 'id') : Donation {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getItemContainer() {
        if (isset($this->_itemContainer) == false) {
            $this->_itemContainer = new ItemContainer($this);
        }
        return $this->_itemContainer;
    }

    public function getEventContainer() {
        if (isset($this->_evnetContainer) == false) {
            $this->_evnetContainer = new EventContainer($this);
        }
        return $this->_evnetContainer;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getCode() {
        return $this->_info['code'];
    }

    public function setCode($var) {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
    }

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($var) {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getDescription() {
        return $this->_info['description'];
    }

    public function setDescription($var) {
        $this->_info['description'] = $var;
        $this->_changes['description'] = $this->_info['description'];
    }

    public function getFeeRate() {
        return $this->_info['feeRate'];
    }

    public function setFeeRate($var) {
        $this->_info['feeRate'] = $var;
        $this->_changes['feeRate'] = $this->_info['feeRate'];
    }

    public function getItem() {
        return $this->_info['item'];
    }

    public function setItem($var) {
        $this->_info['item'] = $var;
        $this->_changes['item'] = $this->_info['item'];
    }

    public function addItem($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `item` = `item` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractItem($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `item` = `item` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getPay() {
        return $this->_info['pay'];
    }

    public function setPay($var) {
        $this->_info['pay'] = $var;
        $this->_changes['pay'] = $this->_info['pay'];
    }

    public function addPay($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pay` = `pay` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractPay($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pay` = `pay` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getKrwConvertedAmount() {
        return $this->_info['krwConvertedAmount'];
    }

    public function setKrwConvertedAmount($var) {
        $this->_info['krwConvertedAmount'] = $var;
        $this->_changes['krwConvertedAmount'] = $this->_info['krwConvertedAmount'];
    }

    public function addKrwConvertedAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwConvertedAmount` = `krwConvertedAmount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractKrwConvertedAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwConvertedAmount` = `krwConvertedAmount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getKrwConvertedFee() {
        return $this->_info['krwConvertedFee'];
    }

    public function setKrwConvertedFee($var) {
        $this->_info['krwConvertedFee'] = $var;
        $this->_changes['krwConvertedFee'] = $this->_info['krwConvertedFee'];
    }

    public function addKrwConvertedFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwConvertedFee` = `krwConvertedFee` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractKrwConvertedFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwConvertedFee` = `krwConvertedFee` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getKrwCount() {
        return $this->_info['krwCount'];
    }

    public function setKrwCount($var) {
        $this->_info['krwCount'] = $var;
        $this->_changes['krwCount'] = $this->_info['krwCount'];
    }

    public function addKrwCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwCount` = `krwCount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractKrwCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwCount` = `krwCount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getKrwAmount() {
        return $this->_info['krwAmount'];
    }

    public function setKrwAmount($var) {
        $this->_info['krwAmount'] = $var;
        $this->_changes['krwAmount'] = $this->_info['krwAmount'];
    }

    public function addKrwAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwAmount` = `krwAmount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractKrwAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwAmount` = `krwAmount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getKrwFee() {
        return $this->_info['krwFee'];
    }

    public function setKrwFee($var) {
        $this->_info['krwFee'] = $var;
        $this->_changes['krwFee'] = $this->_info['krwFee'];
    }

    public function addKrwFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwFee` = `krwFee` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractKrwFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `krwFee` = `krwFee` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getUsdCount() {
        return $this->_info['usdCount'];
    }

    public function setUsdCount($var) {
        $this->_info['usdCount'] = $var;
        $this->_changes['usdCount'] = $this->_info['usdCount'];
    }

    public function addUsdCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdCount` = `usdCount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractUsdCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdCount` = `usdCount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getUsdAmount() {
        return $this->_info['usdAmount'];
    }

    public function setUsdAmount($var) {
        $this->_info['usdAmount'] = $var;
        $this->_changes['usdAmount'] = $this->_info['usdAmount'];
    }

    public function addUsdAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdAmount` = `usdAmount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractUsdAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdAmount` = `usdAmount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getUsdFee() {
        return $this->_info['usdFee'];
    }

    public function setUsdFee($var) {
        $this->_info['usdFee'] = $var;
        $this->_changes['usdFee'] = $this->_info['usdFee'];
    }

    public function addUsdFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdFee` = `usdFee` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractUsdFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `usdFee` = `usdFee` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getNewsCount() {
        return $this->_info['newsCount'];
    }

    public function setNewsCount($var) {
        $this->_info['newsCount'] = $var;
        $this->_changes['newsCount'] = $this->_info['newsCount'];
    }

    public function addNewsCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsCount` = `newsCount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractNewsCount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsCount` = `newsCount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getNewsAmount() {
        return $this->_info['newsAmount'];
    }

    public function setNewsAmount($var) {
        $this->_info['newsAmount'] = $var;
        $this->_changes['newsAmount'] = $this->_info['newsAmount'];
    }

    public function addNewsAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsAmount` = `newsAmount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractNewsAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsAmount` = `newsAmount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getNewsFee() {
        return $this->_info['newsFee'];
    }

    public function setNewsFee($var) {
        $this->_info['newsFee'] = $var;
        $this->_changes['newsFee'] = $this->_info['newsFee'];
    }

    public function addNewsFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsFee` = `newsFee` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractNewsFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `newsFee` = `newsFee` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getPointCount() {
        return $this->_info['pointCount'];
    }

    public function setPointCount($var) {
        $this->_info['pointCount'] = $var;
        $this->_changes['pointCount'] = $this->_info['pointCount'];
    }

    public function addPointCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointCount` = `pointCount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractPointCount($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointCount` = `pointCount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getPointAmount() {
        return $this->_info['pointAmount'];
    }

    public function setPointAmount($var) {
        $this->_info['pointAmount'] = $var;
        $this->_changes['pointAmount'] = $this->_info['pointAmount'];
    }

    public function addPointAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointAmount` = `pointAmount` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractPointAmount($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointAmount` = `pointAmount` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getPointFee() {
        return $this->_info['pointFee'];
    }

    public function setPointFee($var) {
        $this->_info['pointFee'] = $var;
        $this->_changes['pointFee'] = $this->_info['pointFee'];
    }

    public function addPointFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointFee` = `pointFee` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractPointFee($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `pointFee` = `pointFee` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getLastPayDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['lastPayDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastPayDate($var) {
        $this->_info['lastPayDate'] = $var;
        $this->_changes['lastPayDate'] = $this->_info['lastPayDate'];
    }

    public function getLastPayTimestamp($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['lastPayTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastPayTimestamp($var) {
        $this->_info['lastPayTimestamp'] = $var;
        $this->_changes['lastPayTimestamp'] = $this->_info['lastPayTimestamp'];
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

    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTimestamp($var) {
        $this->_info['regTimestamp'] = $var;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }








    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Active:
                return 'active';
                break;

            case self::Status_Inactive:
                return 'inactive';
                break;

            default:
                return '(???)';
        }
    }

}