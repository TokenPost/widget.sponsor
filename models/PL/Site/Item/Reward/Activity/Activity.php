<?php
namespace PL\Models\Site\Item\Reward\Activity;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Site\Item\Reward\Reward;
use PL\Models\Site\Item\Item;
use PL\Models\Site\Item\Page\Container as PageContainer;

class Activity extends AbstractSingleton {

    const tableName = 'SiteItemRewardActivity';

    const Reward_Receive = 0;   // 리워드 받음
    const Reward_Refuse = 1;    // 리워드 안받음

    const Type_General  = 0;    // 일반
    const Type_Referral = 1;    // 리퍼럴로 들어온 경우


    protected $_rewardInstance;
    protected $_itemInstance;
    protected $_pageInstance;

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
            $this->_rewardInstance = Reward::getInstance($this->getRewardId());
        }
        return $this->_rewardInstance;
    }

    public function getItemInstance() {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = Item::getInstance($this->getItemId());
        }
        return $this->_itemInstance;
    }

    public function getPageInstance() {
        if (isset($this->_pageInstance) == false) {
            $this->_pageInstance = PageContainer::isItem($this->getUrl(), 'path');
        }
        return $this->_pageInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
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

    public function getRewardId()
    {
        return $this->_info['rewardId'];
    }

    public function setRewardId($var)
    {
        $this->_info['rewardId'] = $var;
        $this->_changes['rewardId'] = $this->_info['rewardId'];
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

    public function getReward()
    {
        return $this->_info['reward'];
    }

    public function setReward($var)
    {
        $this->_info['reward'] = $var;
        $this->_changes['reward'] = $this->_info['reward'];
    }

    public function getClientId()
    {
        return $this->_info['clientId'];
    }

    public function setClientId($var)
    {
        $this->_info['clientId'] = $var;
        $this->_changes['clientId'] = $this->_info['clientId'];
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

    public function getIsReward()
    {
        return $this->_info['isReward'];
    }

    public function setIsReward($var)
    {
        $this->_info['isReward'] = $var;
        $this->_changes['isReward'] = $this->_info['isReward'];
    }

    public function getLogId()
    {
        return $this->_info['logId'];
    }

    public function setLogId($var)
    {
        $this->_info['logId'] = $var;
        $this->_changes['logId'] = $this->_info['logId'];
    }

    public function getUrl()
    {
        return $this->_info['url'];
    }

    public function setUrl($var)
    {
        $this->_info['url'] = $var;
        $this->_changes['url'] = $this->_info['url'];
    }

    public function getIdentifier()
    {
        return $this->_info['identifier'];
    }

    public function setIdentifier($var)
    {
        $this->_info['identifier'] = $var;
        $this->_changes['identifier'] = $this->_info['identifier'];
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

    public function getLastOne(){
        $this->setOrder('`id` DESC, ');
        $itemInstance = $this->getOne();
        if($itemInstance instanceof History == true) return $itemInstance;
        return null;
    }

    public function getRegDateFormat2($format = 'Y-m-d H:i')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }


}