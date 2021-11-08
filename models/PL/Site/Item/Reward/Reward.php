<?php
namespace PL\Models\Site\Item\Reward;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Item;
use PL\Models\Site\Item\Reward\Activity\Container as ActivityContainer;
use PL\Models\Client\Client;

use PL\Models\Site\Reward\Reward as SiteReward;
use PL\Models\Site\Reward\Container as SiteRewardContainer;

use PL\Models\Site\Item\Reward\UrlPattern\UrlPattern as urlPattern;
use PL\Models\Site\Item\Reward\UrlPattern\Container as urlPatternContainer;

class Reward extends AbstractSingleton {

    const tableName = 'SiteItemReward';

    const Status_Active     = 0;
    const Status_Inactive   = 1;
    const Status_Delete     = 2;

    // 토큰 ID
    const Token_NewsSatoshi  = 3;
    const Token_NewsKrw      = 4;
    const Token_TPC          = 4;
    const Token_NewsUsd      = 5;
    const Token_EOS          = 6;

    // V2 이용
    const Decimal_NewsSatoshi = 10000;
    const Decimal_TPC         = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsKrw     = 10; // 10원기준 0.5원의 리워드가 존재.
    const Decimal_NewsUsd     = 1000; // 두자리일경우 $0.01 리워드 최소 10원이기때문에 부담스럽다. 3~4자리로 수정

    const Token_TPC_Legacy   = 1; // decommissioned
    const Token_News         = 2; // decommissioned

    const Reward_Cert            = 1;
    const Reward_Referral        = 2;
    const Reward_Login           = 3;
    const Reward_ArticleView     = 4;
    const Reward_Comment         = 5;
    const Reward_Share           = 6;
    const Reward_Like            = 7;
    const Reward_ForumPost       = 8;
    const Reward_ConferencePost  = 9;
    const Reward_ReferralArticle = 10;

    const Method_Click      = 0;
    const Method_Auto       = 1;

    protected $_itemInstance;
    protected $_siteRewardInstance;

    protected $_activityContainer;
    protected $_patternContainer;

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

    public function getItemInstance() {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = Item::getInstance($this->getItemId());
        }
        return $this->_itemInstance;
    }

    public function getActivityContainer() {
        if (isset($this->_activityContainer) == false) {
            $this->_activityContainer = new ActivityContainer($this);
        }
        return $this->_activityContainer;
    }

    public function getPatternContainer() {
        if (isset($this->_patternContainer) == false) {
            $this->_patternContainer = new urlPatternContainer($this);
        }
        return $this->_patternContainer;
    }

    public function isMatchUrl($url){
        if($this->getUrlPattern() == '') return false;

        /*
         $pattern : 정규식 패턴
         $str : 추출할 문장
         $match : 추출된 값을 배열에 저장
        */
        preg_match($this->getUrlPattern(), $url, $match);

        if(sizeof($match) === 0) return false;
        return true;
    }

    public function getSiteRewardInstance() {
        if (isset($this->_siteRewardInstance) == false) {
            $this->_siteRewardInstance = SiteReward::getInstance($this->getRewardId());
        }
        return $this->_siteRewardInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
    }
    public function setId($var)
    {
        $this->_info['id'] = $var;
        $this->_changes['id'] = $this->_info['id'];
    }

    public function getRewardId()
    {
        return $this->_info['rewardId'];
    }

    public function setRewardId($var)
    {
        $this->_info['rewardId'] = $var;
        $this->_changes['rewardId'] = $this->_info['rewardId'];
    }

    public function getCode()
    {
        return $this->_info['code'];
    }

    public function setCode($var)
    {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
    }

    public function getItemId()
    {
        return $this->_info['itemId'];
    }

    public function setItemId($var)
    {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getAssetId()
    {
        return $this->_info['assetId'];
    }

    public function setAssetId($var)
    {
        $this->_info['assetId'] = $var;
        $this->_changes['assetId'] = $this->_info['assetId'];
    }

    public function getUrlPattern()
    {
        return $this->_info['urlPattern'];
    }

    public function setUrlPattern($var)
    {
        $this->_info['urlPattern'] = $var;
        $this->_changes['urlPattern'] = $this->_info['urlPattern'];
    }

    public function getReward()
    {
        return $this->_info['reward'];
    }

    public function getDisplayReward()
    {
        $reward = (int)$this->_info['reward'];
        return $reward;
    }

    public function setReward($var)
    {
        $this->_info['reward'] = $var;
        $this->_changes['reward'] = $this->_info['reward'];
    }

    public function getLimit()
    {
        return $this->_info['limit'];
    }

    public function setLimit($var)
    {
        $this->_info['limit'] = $var;
        $this->_changes['limit'] = $this->_info['limit'];
    }

    public function getLimitPerDay()
    {
        return $this->_info['limitPerDay'];
    }

    public function setLimitPerDay($var)
    {
        $this->_info['limitPerDay'] = $var;
        $this->_changes['limitPerDay'] = $this->_info['limitPerDay'];
    }

    public function getTodayReward()
    {
        return $this->_info['todayReward'];
    }

    public function setTodayReward($var)
    {
        $this->_info['todayReward'] = $var;
        $this->_changes['todayReward'] = $this->_info['todayReward'];
    }

    public function addTodayReward($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayReward` = `todayReward` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTodayReward($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayReward` = `todayReward` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotalReward()
    {
        return $this->_info['totalReward'];
    }

    public function setTotalReward($var)
    {
        $this->_info['totalReward'] = $var;
        $this->_changes['totalReward'] = $this->_info['totalReward'];
    }

    public function addTotalReward($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalReward` = `totalReward` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotalReward($i) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalReward` = `totalReward` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTodayActivity()
    {
        return $this->_info['todayActivity'];
    }

    public function setTodayActivity($var)
    {
        $this->_info['todayActivity'] = $var;
        $this->_changes['todayActivity'] = $this->_info['todayActivity'];
    }

    public function addTodayActivity($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayActivity` = `todayActivity` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTodayActivity($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayActivity` = `todayActivity` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotalActivity()
    {
        return $this->_info['totalActivity'];
    }

    public function setTotalActivity($var)
    {
        $this->_info['totalActivity'] = $var;
        $this->_changes['totalActivity'] = $this->_info['totalActivity'];
    }

    public function addTotalActivity($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalActivity` = `totalActivity` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotalActivity($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalActivity` = `totalActivity` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTodayClient()
    {
        return $this->_info['todayClient'];
    }

    public function setTodayClient($var)
    {
        $this->_info['todayClient'] = $var;
        $this->_changes['todayClient'] = $this->_info['todayClient'];
    }

    public function addTodayClient($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayClient` = `todayClient` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTodayClient($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `todayClient` = `todayClient` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotalClient()
    {
        return $this->_info['totalClient'];
    }

    public function setTotalClient($var)
    {
        $this->_info['totalClient'] = $var;
        $this->_changes['totalClient'] = $this->_info['totalClient'];
    }

    public function addTotalClient($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalClient` = `totalClient` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotalClient($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalClient` = `totalClient` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getSequence()
    {
        return $this->_info['sequence'];
    }

    public function setSequence($var)
    {
        $this->_info['sequence'] = $var;
        $this->_changes['sequence'] = $this->_info['sequence'];
    }

    public function getMemo()
    {
        return $this->_info['memo'];
    }

    public function setMemo($var)
    {
        $this->_info['memo'] = $var;
        $this->_changes['memo'] = $this->_info['memo'];
    }

    public function getRegId()
    {
        return $this->_info['regId'];
    }

    public function setRegId($var)
    {
        $this->_info['regId'] = $var;
        $this->_changes['regId'] = $this->_info['regId'];
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




}