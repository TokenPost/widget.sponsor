<?php
namespace PL\Models\Site\Reward;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Site\Site;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_siteId;
    protected $_siteInstance;

    public function __construct(Site $siteInstance = null) {
        parent::__construct(Reward::tableName);
        $this->setTableName(Reward::tableName);

        if(is_null($siteInstance) == false){
            $this->setSiteInstance($siteInstance);
            $this->setSiteId($siteInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Reward::tableName;
    }

    public static function getObjectInstanceStatic($date) : Reward {
        return Reward::getInstance($date);
    }

    public function getObjectInstance($date) : Reward {
        return Reward::getInstance($date);
    }

    public function getSiteInstance(){
        return $this->_siteInstance;
    }

    public function setSiteInstance($siteInstance){
        $this->_siteInstance = $siteInstance;
    }

    public function getSiteId(){
        return $this->_siteId;
    }

    public function setSiteId($siteId){
        $this->_siteId = $siteId;
    }


    public function getItemsPBF(){
        $where = array();
        if(Util::isInteger($this->getSiteId()) == true) $where[] = '`siteId` = '. $this->getSiteId();
        return $where;
    }
}