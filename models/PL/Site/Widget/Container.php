<?php
namespace PL\Models\Site\Widget;

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
        parent::__construct(Widget::tableName);
        $this->setTableName(Widget::tableName);

        if(is_null($siteInstance) == false){
            $this->setSiteInstance($siteInstance);
            $this->setSiteId($siteInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Widget::tableName;
    }

    public static function getObjectInstanceStatic($date) : Widget {
        return Widget::getInstance($date);
    }

    public function getObjectInstance($date) : Widget {
        return Widget::getInstance($date);
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