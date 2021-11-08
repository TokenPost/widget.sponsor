<?php
namespace PL\Models\Referral\Item\Log;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Referral\Item\Item;
use PL\Models\Referral\Item\Container as ItemContainer;
use PL\Models\Referral\Container as ReferralContainer;

class Log extends AbstractSingleton
{

    const tableName = 'ReferralItemLog';

    const Is_Reward = 0;
    const Is_Refuse = 1;

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

    public function getItemInstance() :Item
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

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($var) {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getReward() {
        return $this->_info['reward'];
    }

    public function setReward($var) {
        $this->_info['reward'] = $var;
        $this->_changes['reward'] = $this->_info['reward'];
    }

    public function getIsReward()
    {
        return $this->_info['isReward'];
    }

    public function setIsReward($var)
    {
        $this->_info['isReward'] = $var;
        $this->_changes['isReward'] = $this->_info['isReward'];
    }

    public function getDate($format = 'Y-m-d')
    {
        $date = $this->_info['date'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setDate($var)
    {
        $this->_info['date'] = $var;
        $this->_changes['date'] = $this->_info['date'];
    }

    public function getActivityId() {
        return $this->_info['activityId'];
    }

    public function setActivityId($var) {
        $this->_info['activityId'] = $var;
        $this->_changes['activityId'] = $this->_info['activityId'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($var) {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
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