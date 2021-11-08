<?php
namespace PL\Models\Site\Item\Point\Charge;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;

use PL\Models\Site\Item\Item as SiteItem;

use PL\Models\Admin\Admin;


class Charge extends AbstractSingleton {

    const tableName = 'SiteItemPointCharge';

    const Status_Completed      = 0;    // 완료
    const Status_Request        = 1;    // 요청
    const Status_Processing     = 2;    // 처리중


    protected $_siteItemInstance;
    protected $_adminInstance;

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


    /* SiteItem Instance */
    public function getSiteItemInstance() {
        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItem::getInstance($this->getItemId());
        }
        return $this->_siteItemInstance;
    }

    /* SiteItem Instance */
    public function getAdminInstance() {
        if (isset($this->_adminInstance) == false) {
            $this->_adminInstance = Admin::getInstance($this->getRequesterId());
        }
        return $this->_adminInstance;
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

    public function getTokenId()
    {
        return $this->_info['tokenId'];
    }

    public function setTokenId($var)
    {
        $this->_info['tokenId'] = $var;
        $this->_changes['tokenId'] = $this->_info['tokenId'];
    }

    public function getRequesterId()
    {
        return $this->_info['requesterId'];
    }

    public function setRequesterId($var)
    {
        $this->_info['requesterId'] = $var;
        $this->_changes['requesterId'] = $this->_info['requesterId'];
    }

    public function getRequestDate($format = 'Y-m-d')
    {
        $date = $this->_info['requestDate'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setRequestDate($var)
    {
        $this->_info['requestDate'] = $var;
        $this->_changes['requestDate'] = $this->_info['requestDate'];
    }

    public function getRequestPoint()
    {
        return $this->_info['requestPoint'];
    }

    public function getRequestPointStr()
    {
        return number_format($this->getRequestPoint(), 0);
    }

    public function setRequestPoint($var)
    {
        $this->_info['requestPoint'] = $var;
        $this->_changes['requestPoint'] = $this->_info['requestPoint'];
    }

    public function getRequestKrw()
    {
        return $this->_info['requestKrw'];
    }

    public function setRequestKrw($var)
    {
        $this->_info['requestKrw'] = $var;
        $this->_changes['requestKrw'] = $this->_info['requestKrw'];
    }

    public function getCompletedDate($format = 'Y-m-d')
    {
        $date = $this->_info['completedDate'];
        if($date == '1970-01-01' ||$date == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setCompletedDate($var)
    {
        $this->_info['completedDate'] = $var;
        $this->_changes['completedDate'] = $this->_info['completedDate'];
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

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function getStatusStr()
    {
        if($this->getStatus() == 0){
            return '충전 완료';
        } else if ($this->getStatus() == 1) {
            return '충전 대기';
        } else if ($this->getStatus() == 2) {
            return '충전 대기';
        }

        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }



}