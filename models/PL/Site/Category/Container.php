<?php
namespace PL\Models\Site\Category;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Site\Site;

class Container extends AbstractContainer {

    protected $_siteId;
    protected $_siteInstance;

    public function __construct(Site $siteInstance = null) {
        parent::__construct(Category::tableName);
        $this->setTableName(Category::tableName);

        if(is_null($siteInstance) == false){
            $this->setSiteInstance($siteInstance);
            $this->setSiteId($siteInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Category::tableName;
    }

    public static function getObjectInstanceStatic($date) : Category {
        return Category::getInstance($date);
    }

    public function getObjectInstance($date) : Category {
        return Category::getInstance($date);
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


}