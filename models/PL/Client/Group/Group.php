<?php
namespace PL\Models\Client\Group;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Util\Util;
use PL\Models\Client\Group\Item\Container as ItemContainer;

class Group extends AbstractSingleton {

    /**
     * 상수 설정
     */

    const tableName = 'ClientGroup';

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

    const Group_Subscription = 1;
    const Group_Newsletter   = 2;

    protected $_itemContainer;


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

    public static final function getGroupId($title = 'subscription') : Int {
        $storeArray = array();
        $storeArray['subscription'] = 1;
        $storeArray['newsletter']   = 2;

        if(isset($storeArray[$title]) == true){
            return $storeArray[$title];
        }
        return 0;
    }

    public function getItemContainer(){
        if (isset($this->_itemContainer) == false) {
            $this->_itemContainer = new ItemContainer($this);
        }
        return $this->_itemContainer;
    }

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getTitle()
    {
        return $this->_info['title'];
    }

    public function setTitle($var)
    {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
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

    public function addActive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `active` = `active` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractActive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `active` = `active` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getActive()
    {
        return $this->_info['active'];
    }

    public function setActive($var)
    {
        $this->_info['active'] = $var;
        $this->_changes['active'] = $this->_info['active'];
    }

    public function addTotal($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `total` = `total` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotal($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `total` = `total` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotal()
    {
        return $this->_info['total'];
    }

    public function setTotal($var)
    {
        $this->_info['total'] = $var;
        $this->_changes['total'] = $this->_info['total'];
    }

    public function getLastUpdate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['lastUpdate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastUpdate($var)
    {
        $this->_info['lastUpdate'] = $var;
        $this->_changes['lastUpdate'] = $this->_info['lastUpdate'];
    }

    public function getLastUpdateTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['lastUpdateTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastUpdateTimestamp($var)
    {
        $this->_info['lastUpdateTimestamp'] = $var;
        $this->_changes['lastUpdateTimestamp'] = $this->_info['lastUpdateTimestamp'];
    }

    public function getLastActiveDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['lastActiveDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastActiveDate($var)
    {
        $this->_info['lastActiveDate'] = $var;
        $this->_changes['lastActiveDate'] = $this->_info['lastActiveDate'];
    }

    public function getLastActiveTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['lastActiveTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setLastActiveTimestamp($var)
    {
        $this->_info['lastActiveTimestamp'] = $var;
        $this->_changes['lastActiveTimestamp'] = $this->_info['lastActiveTimestamp'];
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

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName($mode = '') {

        $statusNameArray = array();
        $statusNameArray['en'][self::Status_Active]   = 'Active';
        $statusNameArray['ko'][self::Status_Active]   = '활성';
        $statusNameArray['en'][self::Status_Inactive] = 'Inactive';
        $statusNameArray['ko'][self::Status_Inactive] = '비활성';

        // 현재는 영어, 한국어만 지원
        if (USER_LANGUAGE_CODE){
            $languageCode = USER_LANGUAGE_CODE;
        } else {
            $languageCode = SITE_LANGUAGE_CODE;
        }

        switch ($languageCode) {
            case 'ko':
                break;
            case 'en':
            default:
                $languageCode = 'en';
                break;
        }
        $statusName = $statusNameArray[$languageCode][$this->getStatus()];

        if($mode == 'class'){
            return '<span class="status_' . strtolower($statusNameArray['en'][$this->getStatus()]) . '">' . $statusName . '</span>';
        } else {
            return $statusName;
        }
    }

}