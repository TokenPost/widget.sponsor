<?php
namespace PL\Models\Referral\Item\Log\Ip;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Referral\Item\Item;
use PL\Models\Referral\Item\Container as ItemContainer;

class Ip extends AbstractSingleton
{

    const tableName = 'ReferralItemLogIp';

    protected $_itemInstance;

    public static function getInstance($data, $keyIndex = 'id'): self
    {
        if (is_numeric($data) === true) {
            $db = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getItemInstance(): Item
    {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = ItemContainer::isItem($this->getItemId());
        }
        return $this->_itemInstance;
    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getItemId() {
        return $this->_info['itemId'];
    }

    public function setItemId($var) {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getDate($format = 'Y-m-d') {
        $date = $this->_info['date'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setDate($var) {
        $this->_info['date'] = $var;
        $this->_changes['date'] = $this->_info['date'];
    }

    public function getIp() {
        return $this->_info['ip'];
    }

    public function setIp($var) {
        $this->_info['ip'] = $var;
        $this->_changes['ip'] = $this->_info['ip'];
    }

    public function getLogId() {
        return $this->_info['logId'];
    }

    public function setLogId($var) {
        $this->_info['logId'] = $var;
        $this->_changes['logId'] = $this->_info['logId'];
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


}