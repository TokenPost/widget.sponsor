<?php
namespace PL\Models\Client\Group\Item;


use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Client\Group\Group;
use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

class Item extends AbstractSingleton {

    /**
     * 상수 설정
     */

    const tableName = 'ClientGroupItem';

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

    protected $_groupInstance;
    protected $_clientInstance;

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

    public function getGroupInstance(){
        if (isset($this->_groupInstance) == false) {
            $this->_groupInstance = Group::getInstance($this->getGroupId());
        }
        return $this->_groupInstance;
    }


    public function getClientInstance(){
        $this->renewClient();
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function renewClient(){
        if($this->getClientId() < 1){
            // 회원있는지 확인한다.
            $clientInstance = ClientContainer::isItem($this->getEmail(), 'email');

            if($clientInstance instanceof Client == true){
                $this->setClientId($clientInstance->getId());
                $this->saveChanges();
            }
        }
    }


    public function getId()
    {
        return $this->_info['id'];
    }

    public function getGroupId()
    {
        return $this->_info['groupId'];
    }

    public function setGroupId($var)
    {
        $this->_info['groupId'] = $var;
        $this->_changes['groupId'] = $this->_info['groupId'];
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

    public function getEmail()
    {
        return $this->_info['email'];
    }

    public function setEmail($var)
    {
        $this->_info['email'] = $var;
        $this->_changes['email'] = $this->_info['email'];
    }

    public function getRenewDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['renewDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRenewDate($var)
    {
        $this->_info['renewDate'] = $var;
        $this->_changes['renewDate'] = $this->_info['renewDate'];
    }

    public function getRenewTimestamp($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['renewTimestamp']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRenewTimestamp($var)
    {
        $this->_info['renewTimestamp'] = $var;
        $this->_changes['renewTimestamp'] = $this->_info['renewTimestamp'];
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