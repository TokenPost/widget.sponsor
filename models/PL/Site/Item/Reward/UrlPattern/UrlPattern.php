<?php
namespace PL\Models\Site\Item\Reward\UrlPattern;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Reward\Reward as SiteReward;

class UrlPattern extends AbstractSingleton {

    const tableName = 'RewardUrlPattern';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_rewardInstance;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (Util::isInteger($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getRewardInstance() {
        if (isset($this->_rewardInstance) == false) {
            $this->_rewardInstance = SiteReward::getInstance($this->getSiteRewardId());
        }
        return $this->_rewardInstance;
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getSiteRewardId()
    {
        return $this->_info['siteRewardId'];
    }

    public function setSiteRewardId($var)
    {
        $this->_info['siteRewardId'] = $var;
        $this->_changes['siteRewardId'] = $this->_info['siteRewardId'];
    }

    public function getPattern()
    {
        return $this->_info['pattern'];
    }

    public function setPattern($var)
    {
        $this->_info['pattern'] = $var;
        $this->_changes['pattern'] = $this->_info['pattern'];
    }

//    public function getRef()
//    {
//        return $this->_info['ref'];
//    }
//
//    public function setRef($var)
//    {
//        $this->_info['ref'] = $var;
//        $this->_changes['ref'] = $this->_info['ref'];
//    }

    public function getRound()
    {
        return $this->_info['round'];
    }

    public function setRound($var)
    {
        $this->_info['round'] = $var;
        $this->_changes['round'] = $this->_info['round'];
    }

    public function getModId()
    {
        return $this->_info['modId'];
    }

    public function setModId($var)
    {
        $this->_info['modId'] = $var;
        $this->_changes['modId'] = $this->_info['modId'];
    }

    public function getModIp()
    {
        return $this->_info['modIp'];
    }

    public function setModIp($var)
    {
        $this->_info['modIp'] = $var;
        $this->_changes['modIp'] = $this->_info['modIp'];
    }

    public function getModDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['modDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setModDate($var)
    {
        $this->_info['modDate'] = $var;
        $this->_changes['modDate'] = $this->_info['modDate'];
    }

    public function getRegIp()
    {
        return $this->_info['regIp'];
    }

    public function setRegIp($var)
    {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var)
    {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function addRound() {
        $addCount = $this->getRound() + 1;
        $this->setRound($addCount);
    }

}