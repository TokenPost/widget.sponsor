<?php
namespace PL\Models\Client\Activity;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Container as ClientContainer;
use PL\Models\Reward\Container as RewardContainer;
use PL\Models\Site\Item\Reward\Container as SiteItemRewardContainer;
use PL\Models\Donation\Item\Pay\Container as DonationItemPayContainer;

use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Page\Container as SiteItemPageContainer;
use PL\Models\Site\Item\Reward\Activity\Container as SiteItemRewardActivityContainer;

class Activity extends AbstractSingleton {

    const tableName = 'ClientActivity';

    protected $_clientInstance;
    protected $_rewardInstance;
    protected $_siteInstance;
    protected $_siteRewardInstance;
    protected $_donationItemPayInstance;
    protected $_articleInstance;
    protected $_pageInstance;
    protected $_siteActivityInstance;
    protected $_siteItemInstance;
    protected $_siteItemPageInstance;

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

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getRewardInstance() {
        if (isset($this->_rewardInstance) == false) {
            $this->_rewardInstance = RewardContainer::isItem($this->getRewardId());
        }
        return $this->_rewardInstance;
    }

    public function getSiteRewardInstance() {
        if (isset($this->_siteRewardInstance) == false) {
            $this->_siteRewardInstance = SiteItemRewardContainer::isItem($this->getRewardId());
        }
        return $this->_siteRewardInstance;
    }

    /*
     * 활동한 사이트 정보 가져오기
     * */
//    public function getPageInstance() {
//        if (isset($this->_pageInstance) == false) {
//            $this->_pageInstance = PageContainer::isItem($this->getTargetId());
//        }
//        return $this->_pageInstance;
//    }

    public function getSiteActivityInstance() {
        if (isset($this->_siteActivityInstance) == false) {
            $this->_siteActivityInstance = SiteItemRewardActivityContainer::isItem($this->getTargetId());
        }
        return $this->_siteActivityInstance;
    }

    public function getSiteItemInstance() {
        $this->_siteActivityInstance = $this->getSiteActivityInstance();

        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItemContainer::isItem($this->_siteActivityInstance->getItemId());
        }
        return $this->_siteItemInstance;
    }

    public function getSiteItemPageInstance() {
        $this->_siteActivityInstance = $this->getSiteActivityInstance();

        if (isset($this->_siteItemPageInstance) == false) {
            $this->_siteItemPageInstance = SiteItemPageContainer::isItem($this->_siteActivityInstance->getIdentifier(), 'path');
        }
        return $this->_siteItemPageInstance;
    }

    public function getDonationItemPayInstance() {
        if (isset($this->_donationItemPayInstance) == false) {
            $this->_donationItemPayInstance = DonationItemPayContainer::isItem($this->getTargetId());
        }
        return $this->_donationItemPayInstance;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($var) {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getSiteId() {
        return $this->_info['siteId'];
    }

    public function setSiteId($var) {
        $this->_info['siteId'] = $var;
        $this->_changes['siteId'] = $this->_info['siteId'];
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

    public function getRewardId() {
        return $this->_info['rewardId'];
    }

    public function setRewardId($var) {
        $this->_info['rewardId'] = $var;
        $this->_changes['rewardId'] = $this->_info['rewardId'];
    }

    public function getRewardTitle(){
        if($this->getRewardId() >= 1 && $this->getrewardInstance()){
            return $this->getrewardInstance()->getTitle();
        }
        return '';
    }

    public function getRewardPoint(){
        if($this->getRewardId() >= 1 && $this->getrewardInstance()){
            return $this->getrewardInstance()->getPoint();
        }
        return '';
    }

    // SiteItemReward
    public function getSiteRewardPoint(){
        if($this->getRewardId() >= 1 && $this->getSiteRewardInstance()){
            return $this->getSiteRewardInstance()->getReward();
        }
        return '';
    }

    public function getTargetId() {
        return $this->_info['targetId'];
    }

    public function setTargetId($var) {
        $this->_info['targetId'] = $var;
        $this->_changes['targetId'] = $this->_info['targetId'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function setRegIp($var) {
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

    public function getRegTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTimestamp($var)
    {
        $this->_info['regTimestamp'] = $var;
        $this->_changes['regTimestamp'] = $this->_info['regTimestamp'];
    }

    public function getRegDateFormat($format = 'Y-m-d')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function getRegDateFormat2($format = 'Y-m-d H:i')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function getRewardPointWithNumberFormat($n){
        if($this->getRewardId() >= 1 && $this->getSiteRewardInstance()){
            return number_format($this->getSiteRewardInstance()->getReward(), $n);
        }
        return '';
    }
}