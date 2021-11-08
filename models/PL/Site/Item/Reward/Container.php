<?php
namespace PL\Models\Site\Item\Reward;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Site\Item\Item;

use PL\Models\Site\Reward\Reward as SiteReward;
use PL\Models\Site\Reward\Container as SiteRewardContainer;

use PL\Models\Reward\Container as RewardContainer;



class Container extends AbstractContainer {

    protected $_itemInstance;
    protected $_itemId;

    protected $_typeId;

    public function __construct(Item $itemInstance = null) {
        parent::__construct(Reward::tableName);
        $this->setTableName(Reward::tableName);

        if(is_null($itemInstance) == false) {
            $this->setItemInstance($itemInstance);
        }
    }

    public static function getTableNameStatic(){
        return Reward::tableName;
    }

    public static function getObjectInstanceStatic($data) : Reward {
        return Reward::getInstance($data);
    }

    public function getObjectInstance($data) : Reward {
        return Reward::getInstance($data);
    }

    public function getItemInstance(){
        return $this->_itemInstance;
    }

    public function setItemInstance(Item $itemInstance){
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId) {
        $this->_itemId = $itemId;
    }

    public function lastSequence($itemId, $status){

        $query = "SELECT * FROM `" . self::getTableNameStatic() . "` WHERE `itemId` = ? AND `status` != ? ORDER BY `sequence` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $data = $db->query($query, array($itemId, $status))->fetch();

        if(is_array($data) == true) {
            return self::getObjectInstanceStatic($data);
        }
        return false;
    }

    // default reward 생성
    public function createDefaultReward($itemId, $assetId) {
        if(is_numeric($itemId) == false || $itemId < 1) return false;
        if(is_numeric($assetId) == false || $assetId < 1) return false;

        $rewardContainer = new SiteRewardContainer();
        $rewardContainer->setOrder("`id` ASC,");
        $reward = $rewardContainer->getItemsAll();

        foreach($reward as $idx => $val) {
            $defaultReward = array();
            $defaultReward['rewardId']      = $val->getId();
            $defaultReward['code']          = $val->getCode();
            $defaultReward['itemId']        = $itemId;      // 사이트 ID
            $defaultReward['assetId']       = $assetId;     // 자산 타입
            $defaultReward['regIp']         = getIP();
            $defaultReward['regDate']       = Util::getLocalTime();
            $defaultReward['status']        = SiteReward::Status_Inactive;

            $ret = $this->addNew($defaultReward);
            if($ret == false){
                return false;
                break;
            }
        }
        return true;
    }


    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getItemId()) == true) $where[] = '`itemId` = "' . $this->getItemId() . '"';
        return $where;
    }
}