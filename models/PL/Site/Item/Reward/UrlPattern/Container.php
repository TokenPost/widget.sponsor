<?php
namespace PL\Models\Site\Item\Reward\UrlPattern;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Site\Item\Reward\Reward as SiteItemReward;
use PL\Models\Util\Util;

use PL\Models\Site\Item\Reward\UrlPattern\UrlPattern;

class Container extends AbstractContainer {

    protected $_siteRewardId;

    protected $_itemRewardInstance;

    public function __construct(SiteItemReward $itemRewardInstance = null) {
        parent::__construct(UrlPattern::tableName);
        $this->setTableName(UrlPattern::tableName);

        if(is_null($itemRewardInstance) == false) {
            $this->setItemRewardInstance($itemRewardInstance);
        }
    }

    public static function getTableNameStatic(){
        return UrlPattern::tableName;
    }

    public static function getObjectInstanceStatic($date) : UrlPattern {
        return UrlPattern::getInstance($date);
    }

    public function getObjectInstance($date) : UrlPattern {
        return UrlPattern::getInstance($date);
    }

    public function getItemRewardInstance(){
        return $this->_itemRewardInstance;
    }

    public function setItemRewardInstance(SiteItemReward $itemRewardInstance){
        $this->_itemRewardInstance = $itemRewardInstance;
        $this->setSiteRewardId($itemRewardInstance->getId());
    }

    public function getSiteRewardId(){
        return $this->_siteRewardId;
    }

    public function setSiteRewardId($siteRewardId) {
        $this->_siteRewardId = $siteRewardId;
    }

    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getSiteRewardId()) == true) $where[] = '`siteRewardId` = "' . $this->getSiteRewardId() . '"';
        $where[] = '`status` = "0"';
        return $where;
    }

    public function selectPattern($siteRewardId=0)
    {
        if ($siteRewardId == '') return false;

        $db = DI::getDefault()->getShared('db');
        $result = $db->query('SELECT * FROM `' . self::getTableNameStatic() . '` WHERE `status` = ? and `siteRewardId` = ?', array(0, $this->getSiteRewardId()));
        $data = $result->fetch();
        if($data == false) {
            return array();
        } else {
            return $this->getObjectInstanceStatic($data);
        }
    }
}