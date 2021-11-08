<?php
namespace PL\Models\Client\Point\Snapshot\Item;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Admin\Admin;
use PL\Models\Client\Point\Snapshot\Snapshot;

class Item extends AbstractSingleton {

    const Status_Complete   = 0;
    const Status_Standby    = 1;
    const Status_Cancel     = 2;
    const Status_Error      = 4;
    const Status_Processing = 5;

    const tableName = 'ClientPointSnapshotItem';


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


    public function getItemContainer() {
        if (isset($this->_itemContainer) == false) {
            $this->_itemContainer = new ItemContainer($this);
        }
        return $this->_itemContainer;
    }

    public function getId() {
        return $this->_info['id'];
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

    public function getDisplayPoint() {
        $point = Util::numberFormat($this->getPoint());
        if($this->getPoint() > 0) $point = '+' . $point;
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

}