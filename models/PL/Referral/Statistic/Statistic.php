<?php
namespace PL\Models\Referral\Statistic;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Referral\Referral;
use PL\Models\Referral\Container as ReferralContainer;

class Statistic extends AbstractSingleton {

    const tableName = 'ReferralStatistic';

    protected $_referralInstance;

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

    public function getReferralInstance() :Referral
    {
        if (isset($this->_referralInstance) == false) {
            $this->_referralInstance = ReferralContainer::isItem($this->getReferralId());
        }
        return $this->_referralInstance;
    }


    public function getId() {
        return $this->_info["id"];
    }

    public function getReferralId() {
        return $this->_info['referralId'];
    }

    public function setReferralId($var) {
        $this->_info['referralId'] = $var;
        $this->_changes['referralId'] = $this->_info['referralId'];
    }

    public function getDate() {
        return $this->_info['date'];
    }

    public function setDate($var) {
        $this->_info['date'] = $var;
        $this->_changes['date'] = $this->_info['date'];
    }

    public function getItem() {
        return $this->_info['item'];
    }

    public function setItem($var) {
        $this->_info['item'] = $var;
        $this->_changes['item'] = $this->_info['item'];
    }

    public function addItem($i = 1) {
        if(Util::isInteger($i) != true) return false;

        $this->_info['item'] = $this->_info['item'] + $i;
        $this->db->query('UPDATE `' . $this->getTableName() . '` set `item` = `item` + ' . $i . ' WHERE id = ?', array($this->getId()));
    }

    public function getLog() {
        return $this->_info['log'];
    }

    public function setLog($var) {
        $this->_info['log'] = $var;
        $this->_changes['log'] = $this->_info['log'];
    }

    public function addLog($i = 1) {
        if(Util::isInteger($i) != true) return false;

        $this->_info['log'] = $this->_info['log'] + $i;
        $this->db->query('UPDATE `' . $this->getTableName() . '` set `log` = `log` + ' . $i . ' WHERE id = ?', array($this->getId()));
    }

    public function getLogIp() {
        return $this->_info['logIp'];
    }

    public function setLogIp($var) {
        $this->_info['logIp'] = $var;
        $this->_changes['logIp'] = $this->_info['logIp'];
    }

    public function addLogIp($i = 1) {
        if(Util::isInteger($i) != true) return false;

        $this->_info['logIp'] = $this->_info['logIp'] + $i;
        $this->db->query('UPDATE `' . $this->getTableName() . '` set `logIp` = `logIp` + ' . $i . ' WHERE id = ?', array($this->getId()));
    }

    public function getReward() {
        return $this->_info['reward'];
    }

    public function setReward($var) {
        $this->_info['reward'] = $var;
        $this->_changes['reward'] = $this->_info['reward'];
    }

    public function addReward($i = 1) {
        if(Util::isInteger($i) != true) return false;

        $this->_info['reward'] = $this->_info['reward'] + $i;
        $this->db->query('UPDATE `' . $this->getTableName() . '` set `reward` = `reward` + ' . $i . ' WHERE id = ?', array($this->getId()));
    }

    public function getRewardTotal() {
        return $this->_info['rewardTotal'];
    }

    public function setRewardTotal($var) {
        $this->_info['rewardTotal'] = $var;
        $this->_changes['rewardTotal'] = $this->_info['rewardTotal'];
    }

    public function addRewardTotal($i = 0) {
        if(is_numeric($i) != true) return false;

        $this->_info['rewardTotal'] = $this->_info['rewardTotal'] + $i;
        $this->db->query('UPDATE `' . $this->getTableName() . '` set `rewardTotal` = `rewardTotal` + ' . $i . ' WHERE id = ?', array($this->getId()));
    }


}