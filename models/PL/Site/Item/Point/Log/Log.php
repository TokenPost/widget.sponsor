<?php
namespace PL\Models\Site\Item\Point\Log;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Point\Point as SiteItemPoint;
use PL\Models\Site\Item\Point\Container as SiteItemPointContainer;

class Log extends AbstractSingleton {

    const tableName = 'SiteItemPointLog';

    const Type_PPOINT = 1;
    const Type_NEWS = 2;

    const RequesterType_Self  = 1;
    const RequesterType_Admin = 2;
    const RequesterType_Bot   = 3;

    const Position_Add = 1;
    const Position_Subtract = 2;

    protected $_siteItemPointInstance;
    protected $_siteItemPointContainer;

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

    /* SiteItemPoint Instance */
    public function getSiteItemPointInstance() {
        if (isset($this->_siteItemPointInstance) == false) {
            $this->_siteItemPointInstance = SiteItemPoint::getInstance($this->getPointId());
        }
        return $this->_siteItemPointInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
    }

    public function getPointId()
    {
        return $this->_info['pointId'];
    }

    public function setPointId($var)
    {
        $this->_info['pointId'] = $var;
        $this->_changes['pointId'] = $this->_info['pointId'];
    }

    public function getRequesterType()
    {
        return $this->_info['requesterType'];
    }

    public function setRequesterType($var)
    {
        $this->_info['requesterType'] = $var;
        $this->_changes['requesterType'] = $this->_info['requesterType'];
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

    public function getApproverId()
    {
        return $this->_info['approverId'];
    }

    public function setApproverId($var)
    {
        $this->_info['approverId'] = $var;
        $this->_changes['approverId'] = $this->_info['approverId'];
    }

    public function getTypeId()
    {
        return $this->_info['typeId'];
    }

    public function setTypeId($var)
    {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

    public function getPosition()
    {
        return $this->_info['position'];
    }

    public function setPosition($var)
    {
        $this->_info['position'] = $var;
        $this->_changes['position'] = $this->_info['position'];
    }

    public function getPoint()
    {
        return $this->_info['point'];
    }

    public function setPoint($var)
    {
        $this->_info['point'] = $var;
        $this->_changes['point'] = $this->_info['point'];
    }

    public function getDate($format = 'Y-m-d')
    {
        $date = $this->_info['date'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setDate($var)
    {
        $this->_info['date'] = $var;
        $this->_changes['date'] = $this->_info['date'];
    }

    public function getBefore()
    {
        return $this->_info['before'];
    }

    public function setBefore($var)
    {
        $this->_info['before'] = $var;
        $this->_changes['before'] = $this->_info['before'];
    }

    public function getAfter()
    {
        return $this->_info['after'];
    }

    public function setAfter($var)
    {
        $this->_info['after'] = $var;
        $this->_changes['after'] = $this->_info['after'];
    }

    public function getLog()
    {
        return $this->_info['log'];
    }

    public function setLog($var)
    {
        $this->_info['log'] = $var;
        $this->_changes['log'] = $this->_info['log'];
    }

    public function getComment()
    {
        return $this->_info['comment'];
    }

    public function setComment($var)
    {
        $this->_info['comment'] = $var;
        $this->_changes['comment'] = $this->_info['comment'];
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



}