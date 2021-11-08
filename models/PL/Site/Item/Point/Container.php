<?php
namespace PL\Models\Site\Item\Point;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Site\Item\Item as SiteItem;


class Container extends AbstractContainer
{

    protected $_siteItemId;
    protected $_tokenId;

    protected $_siteItemInstance;

    public function __construct()
    {
        parent::__construct(Point::tableName);
        $this->setTableName(Point::tableName);

//        if (is_null($siteItemInstance) == false) {
//            $this->setSiteItemInstance($siteItemInstance);
//        }
    }

    public static function getTableNameStatic()
    {
        return Point::tableName;
    }

    public static function getObjectInstanceStatic($data): Point
    {
        return Point::getInstance($data);
    }

    public function getObjectInstance($data): Point
    {
        return Point::getInstance($data);
    }

    public function getSiteItemInstance()
    {
        return $this->_siteItemInstance;
    }

    public function setSiteItemInstance(SiteItem $siteItemInstance)
    {
        $this->_siteItemInstance = $siteItemInstance;
        $this->setSiteItemId($siteItemInstance->getId());
    }

    public function getSiteItemId()
    {
        return $this->_siteItemId;
    }

    public function setSiteItemId($siteItemId)
    {
        $this->_siteItemId = $siteItemId;
    }

    public function getTokenId()
    {
        return $this->_tokenId;
    }

    public function setTokenId($tokenId)
    {
        $this->_tokenId = $tokenId;
    }

    public function getItemsPBF()
    {
        $where = array();
        if (Util::isInteger($this->getSiteItemId()) == true) $where[] = '`siteItemId` = "' . $this->getSiteItemId() . '"';
        return $where;
    }

    public function firstOrCreate($targetId, $tokenId)
    {
        if(Util::isInteger($tokenId) != true) return null;
        if(Util::isInteger($targetId) != true) return null;

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `siteItemId` = ? AND `tokenId` = ? ORDER BY `id` DESC LIMIT 1";
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query, array($targetId, $tokenId))->fetch();

        if(is_array($data) == true) {
            // 해당 페이지 referral code 존재하는 경우
            return static::getObjectInstanceStatic($data);
        } else {
            // 해당 페이지 referral code 존재안하는 경우
            // 새로 생성
            $newItem = array();
            $newItem['siteItemId']  = $targetId;
            $newItem['tokenId']     = $tokenId;
            $newItem['log']         = 0;
            $newItem['plus']        = 0;
            $newItem['subtract']    = 0;
            $newItem['point']       = 0;
            $newItem['regDate']     = Util::getDbNow();
            $newItem['status']      = Point::Status_Active;

            $ret = $this->addNew($newItem);
            if($ret >= 1){
                return self::isItem($ret);
            }
            // 이 경우는 없겠지만
            return null;
        }


    }


}