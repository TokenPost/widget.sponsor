<?php
namespace PL\Models\Referral;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Referral\Item\Container as ItemContainer;
use PL\Models\Referral\Statistic\Container as StatisticContainer;
//use PL\Models\Reward\Container as RewardContainer;

use PL\Models\Site\Reward\Reward as SiteReward;
use PL\Models\Site\Reward\Container as SiteRewardContainer;



class Referral extends AbstractSingleton {

    const tableName = 'Referral';

    const Referral_Signup         = 1;
    const Referral_Article        = 2;
    const Referral_Board          = 3;

    const Signup_BonusRate          = 0.01;

    protected $_rewardInstance;
    protected $_statisticInstance;

    protected $_itemContainer;
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

    /* SiteReward 정보 가져오기 */
    public function getRewardInstance() {
        if (isset($this->_rewardInstance) == false) {
            $this->_rewardInstance = SiteRewardContainer::isItem($this->getRewardId(), 'rewardId');
        }
        return $this->_rewardInstance;
    }

    public function getStatisticInstance() {
        if (isset($this->_statisticInstance) == false) {
            $this->_statisticInstance = $this->getStatisticContainer()->firstOrCreate(Util::getLocalTime('Y-m-d'));
        }
        return $this->_statisticInstance;
    }


    public function getItemContainer() {
        if (isset($this->_itemContainer) == false) {
            $this->_itemContainer = new ItemContainer($this);
        }
        return $this->_itemContainer;
    }

    public function getStatisticContainer() {
        if (isset($this->_statisticContainer) == false) {
            $this->_statisticContainer = new StatisticContainer($this);
        }
        return $this->_statisticContainer;
    }




    public function getId() {
        return $this->_info["id"];
    }

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($var) {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getCode() {
        return $this->_info['code'];
    }

    public function setCode($var) {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
    }

    public function getRewardId() {
        return $this->_info['rewardId'];
    }

    public function setRewardId($var) {
        $this->_info['rewardId'] = $var;
        $this->_changes['rewardId'] = $this->_info['rewardId'];
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