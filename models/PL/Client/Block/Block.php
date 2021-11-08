<?php
namespace PL\Models\Client\Block;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Client;
use PL\Models\Block\Item\Item;
use PL\Models\Block\Reason\Reason;

class Block extends AbstractSingleton {

    const tableName = 'ClientBlock';

    // ??
    const Type_Board   = 1;
    const Type_Comment = 2;
    const Type_Article = 3;
    const Type_All     = 4;

    const Status_Active  = 0;
    const Status_Expired = 1;

    protected $_clientInstance;
    protected $_blockInstance;
    protected $_reasonInstance;

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

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getBlockInstance() {
        if (isset($this->_blockInstance) == false) {
            $this->_blockInstance = Item::getInstance($this->getBlockId());
        }
        return $this->_blockInstance;
    }

    public function getReasonInstance() {
        if (isset($this->_reasonInstance) == false) {
            $this->_blockInstance = Reason::getInstance($this->getReasonId());
        }
        return $this->_blockInstance;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId']    = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getBlockId() {
        return $this->_info['blockId'];
    }

    public function setBlockId($blockId) {
        $this->_info['blockId']    = $blockId;
        $this->_changes['blockId'] = $this->_info['blockId'];
    }

    public function getReasonId() {
        return $this->_info['ReasonId'];
    }

    public function setReasonId($ReasonId) {
        $this->_info['ReasonId']    = $ReasonId;
        $this->_changes['ReasonId'] = $this->_info['ReasonId'];
    }

    public function getRef() {
        return $this->_info['ref'];
    }

    public function setRef($value) {
        $this->_info['ref']    = $value;
        $this->_changes['ref'] = $this->_info['ref'];
    }

    public function getDescription() {
        return $this->_info['description'];
    }

    public function setDescription($description) {
        $this->_info['description']    = $description;
        $this->_changes['description'] = $this->_info['description'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($typeId) {
        $this->_info['typeId']    = $typeId;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }


    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName($mode = '') {
        switch ($this->_info ['status']) {
            case self::Status_Active:
                $statusName =  'active';
                break;
            case self::Status_Expired:
                $statusName =  'expired';
                break;
            default:
                $statusName =  '(????)';
        }

        if($mode == 'class'){
            return '<span class="status_' . $statusName . '">' . $statusName . '</span>';
        } else {
            return $statusName;
        }
    }



}