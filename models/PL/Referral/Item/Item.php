<?php
namespace PL\Models\Referral\Item;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

use PL\Models\Referral\Referral;
use PL\Models\Referral\Container as ReferralContainer;
use PL\Models\Referral\Item\Log\Container as LogContainer;
use PL\Models\Referral\Item\Log\Ip\Container as LogIpContainer;
use PL\Models\Referral\Statistic\Container as StatisticContainer;

use PL\Models\Site\Reward\Reward;
use PL\Models\Site\Reward\Container as RewardContainer;
use PL\Models\Site\Item\Reward\Reward as ItemReward; /* SiteItemReward*/
use PL\Models\Site\Item\Reward\Container as ItemRewardContainer;

class Item extends AbstractSingleton {

    const tableName = 'ReferralItem';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_referralInstance;
    protected $_clientInstance;
    protected $_rewardInstance;
    protected $_itemInstance;

    protected $_logContainer;
    protected $_logIpContainer;
    protected $_statisticContainer;

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

    public function getRewardInstance() : Reward
    {
        if (isset($this->_rewardInstance) == false) {
            $this->_rewardInstance = RewardContainer::isItem($this->getRewardId());
        }
        return $this->_referralInstance;
    }

    public function getItemInstance() : ItemReward
    {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = ItemRewardContainer::isItem($this->getItemId());
        }
        return $this->_referralInstance;
    }

    public function getClientInstance() :Client
    {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getLogContainer() {
        if (isset($this->_logContainer) == false) {
            $this->_logContainer = new LogContainer($this);
        }
        return $this->_logContainer;
    }

    public function getLogIpContainer() {
        if (isset($this->_logIpContainer) == false) {
            $this->_logIpContainer = new LogIpContainer($this);
        }
        return $this->_logIpContainer;
    }

    public function getStatisticContainer() {
        if (isset($this->_statisticContainer) == false) {
            $this->_statisticContainer = new StatisticContainer($this);
        }
        return $this->_statisticContainer;
    }


    public function recordLog($clientId, $regIp, $reward = 0, $activityId = 0, $activityDate) {
        return $this->getLogContainer()->recordLog($clientId, $regIp, $reward, $activityId, $activityDate);
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getReferralId() {
        return $this->_info['referralId'];
    }

    public function setReferralId($var) {
        $this->_info['referralId'] = $var;
        $this->_changes['referralId'] = $this->_info['referralId'];
    }

    /* SiteReward id */
    public function getRewardId() {
        return $this->_info['rewardId'];
    }

    public function setRewardId($var) {
        $this->_info['rewardId'] = $var;
        $this->_changes['rewardId'] = $this->_info['rewardId'];
    }

    /* SiteItemReward id */
    public function getItemId() {
        return $this->_info['referralId'];
    }

    public function setItemId($var) {
        $this->_info['referralId'] = $var;
        $this->_changes['referralId'] = $this->_info['referralId'];
    }

    public function getTargetId() {
        return $this->_info['targetId'];
    }

    public function setTargetId($var) {
        $this->_info['targetId'] = $var;
        $this->_changes['targetId'] = $this->_info['targetId'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($var) {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getCode() {
        return $this->_info['code'];
    }

    public function setCode($var) {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
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

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var) {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }


}