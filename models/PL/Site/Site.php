<?php
namespace PL\Models\Site;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Container as SiteItemContainer;

class Site extends AbstractSingleton {
    
    /*
     * Site 관련 전체적인 통계 테이블
     * title = init 하나만 존재할 예정?
     * */

    const tableName = 'Site';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_managerInstance;

    protected $_widgetContainer;
    protected $_siteItemContainer;

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

    // siteItem
    public function getItemContainer() {
        if (isset($this->_siteItemContainer) == false) {
            $this->_siteItemContainer = new SiteItemContainer($this);
        }
        return $this->_siteItemContainer;
    }

    public function getManagerInstance() {
        if (isset($this->_managerInstance) == false) {
            $this->_managerInstance = Client::getInstance($this->getManagerId());
        }
        return $this->_managerInstance;
    }

    public function getWidgetContainer() : WidgetContainer {
        if (isset($this->_widgetContainer) == false) {
            $this->_widgetContainer = new WidgetContainer($this);
        }
        return $this->_widgetContainer;
    }

    public function getId() {
        return $this->_info['id'];
    }
    
    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($var) {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getCompanyName() {
        return $this->_info['companyName'];
    }

    public function setCompanyName($var) {
        $this->_info['companyName'] = $var;
        $this->_changes['companyName'] = $this->_info['companyName'];
    }

    public function getActive() {
        return $this->_info['active'];
    }

    public function setActive($var) {
        $this->_info['active'] = $var;
        $this->_changes['active'] = $this->_info['active'];
    }

    public function getInactive() {
        return $this->_info['inactive'];
    }

    public function setInactive($var) {
        $this->_info['inactive'] = $var;
        $this->_changes['inactive'] = $this->_info['inactive'];
    }

    public function getTotal() {
        return $this->_info['total'];
    }

    public function setTotal($var) {
        $this->_info['total'] = $var;
        $this->_changes['total'] = $this->_info['total'];
    }

}