<?php
namespace PL\Models\Site\Item\Point\Transaction\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;
use PL\Models\Exchange\Token\Token;

class Log extends AbstractSingleton {


    const Type_Withdrawal = 1;
    const Type_Receive    = 2;

    const TargetType_Internal = 1;
    const TargetType_External = 2;

    const Status_Complete  = 0;
    const Status_Pending   = 1;
    const Status_Approved  = 2;
    const Status_Rejected  = 3;
    const Status_Error     = 4;
    const Status_Cancel    = 5;
    const Status_Initial   = 10;

    const tableName = 'SiteItemPointTransactionLog';


    protected $_clientInstance;
    protected $_addressInstance;
    protected $_transactionInstance;
    protected $_targetInstance;
    protected $_tokenInstance;

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
            $this->_clientInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getAddressInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getTransactionInstance() {
        if (isset($this->_transactionInstance) == false) {
            $this->_transactionInstance = Client::getInstance($this->getClientId());
        }
        return $this->_transactionInstance;
    }

    public function getTargetInstance() {
        if($this->getTargetTypeId() != self::TargetType_Internal) return null;

        if (isset($this->_targetInstance) == false) {
            $this->_targetInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_targetInstance;
    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function getAddressId() {
        return $this->_info['addressId'];
    }

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function getTransactionId() {
        return $this->_info['transactionId'];
    }

    public function getPointTokenId() {
        return $this->_info['pointTokenId'];
    }

    public function getPointTokenQuantity() {
        return $this->_info['pointTokenQuantity'];
    }

    public function getPointLogId() {
        return $this->_info['pointLogId'];
    }

    public function setPointLogId($var) {
        $this->_info['pointLogId'] = $var;
        $this->_changes['pointLogId'] = $this->_info['pointLogId'];
    }

    public function getTargetTypeId() {
        return $this->_info['targetTypeId'];
    }

    public function getTargetId() {
        return $this->_info['targetId'];
    }

    public function getTargetAddressId() {
        return $this->_info['targetAddressId'];
    }

    public function setTargetAddressId($var) {
        $this->_info['targetAddressId'] = $var;
        $this->_changes['targetAddressId'] = $this->_info['targetAddressId'];
    }

    public function getTxId() {
        return $this->_info['txId'];
    }

    public function setTxId($var) {
        $this->_info['txId'] = $var;
        $this->_changes['txId'] = $this->_info['txId'];
    }

    public function getTxMessage() {
        return $this->_info['txMessage'];
    }

    public function setTxMessage($var) {
        $this->_info['txMessage'] = $var;
        $this->_changes['txMessage'] = $this->_info['txMessage'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var) {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getRegTime($format = 'Y-m-d H:i:s') {
        $date = trim($this->_info['regTime']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegTime($var) {
        $this->_info['regTime'] = $var;
        $this->_changes['regTime'] = $this->_info['regTime'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($var) {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getStatusName() {
        switch ($this->_info['status']) {
            case self::Status_Complete:
                return 'complete';
                break;
            case self::Status_Pending:
                return 'pending';
                break;
            case self::Status_Approved:
                return 'approved';
                break;
            case self::Status_Error:
                return 'error';
                break;
            case self::Status_Cancel:
                return 'cancel';
                break;
            default:
                return '';
        }
    }

}