<?php
namespace PL\Models\Client\Point\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Admin\Admin;
use PL\Models\Client\Client;

class Log extends AbstractSingleton {

    const Type_TPC = 1;

    const RequesterType_Self  = 1;
    const RequesterType_Admin = 2;
    const RequesterType_Bot   = 3;

    const Position_Add = 1;
    const Position_Subtract = 2;

    const tableName = 'ClientPointLog';


    protected $_requesterInstance = null;

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

    public function getRequesterInstance() {
        if(is_numeric($this->getRequesterId()) == false || $this->getRequesterId() < 1) return null;
        if (isset($this->_requesterInstance) == false) {
            switch ($this->getRequesterType()){
                case self::RequesterType_Self:
                    $this->_requesterInstance = Client::getInstance($this->getRequesterId());
                    break;
                case self::RequesterType_Admin:
                    $this->_requesterInstance = Admin::getInstance($this->getRequesterId());
                    break;
            }

        }
        return $this->_requesterInstance;
    }

    public function getRequesterName() {
        if($this->getRequesterType() == self::RequesterType_Bot) return 'Bot';
        if(is_numeric($this->getRequesterId()) == false || $this->getRequesterId() < 1) return '';
        switch ($this->getRequesterType()){
            case self::RequesterType_Self:
            case self::RequesterType_Admin:
                $requesterInstance = $this->getRequesterInstance();
                if($requesterInstance) return $requesterInstance->getName();
                break;
            default:
            case self::RequesterType_Bot:
                return 'bot';
                break;
        }
        return '';
    }


    public function getId() {
        return $this->_info['id'];
    }

    /*
    public function getPointId() {
        return $this->_info['pointId'];
    }*/

    public function getRequesterTypeName() {
        switch ($this->getRequesterType()) {
            case self::RequesterType_Self:
                return 'self';
                break;

            case self::RequesterType_Admin:
                return 'admin';
                break;

            case self::RequesterType_Bot:
                return 'bot';
                break;
            default:
                return '(???)';
        }
    }

    public function getRequesterType() {
        return $this->_info['requesterType'];
    }

    public function getRequesterId() {
        return $this->_info['requesterId'];
    }

    public function getApproverId() {
        return $this->_info['approverId'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function getPositionName() {
        switch ($this->getPosition()) {
            case self::Position_Add:
                return 'add';
                break;
            case self::Position_Subtract:
                return 'subtract';
                break;
            default:
                return '(???)';
        }
    }

    public function getPosition() {
        return $this->_info['position'];
    }

//    public function getDisplayPoint() {
//        $point = Util::numberFormat($this->getPoint());
//        if($this->getPoint() > 0) $point = '+' . $point;
//        return $point;
//    }

    public function getDisplayPoint() {
        if($this->getPoint() == 0){
            return 0;
        }
        $point = $this->getPoint();
        //$point = Point::castDisplayPoint($point, $this->getTokenId());
        $point = Util::numberFormat($point);
        if($point > 0) $point = '+' . $point;
        //$point = Util::numberFormatSigned($point);
        return $point;
    }

    public function getPoint() {
        return $this->_info['point'];
    }

    public function getBefore() {
        return $this->_info['before'];
    }

    public function getAfter() {
        return $this->_info['after'];
    }

    public function getLog() {
        return $this->_info['log'];
    }

    public function getComment() {
        return $this->_info['comment'];
    }

    public function getRegIp() {
        return $this->_info['regIp'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    /**
     * Secu server timestamp format : Unix time integer
     */
    public function getRegTimestamp($format = 'Y-m-d H:i:s') {
        return date($format, $this->_info['regTimestamp']);
    }

    public function getTimestamp($format = 'Y-m-d H:i:s') {
        return date($format, $this->_info['timestamp']);
    }

}