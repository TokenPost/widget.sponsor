<?php
namespace PL\Models\Client\Activity\Calculate;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Client\Container as ClientContainer;

class Calculate extends AbstractSingleton {

    const tableName = 'ClientActivityCalculate';

    const Status_Active = 0;
    const Status_Error  = 1;
    const Status_Processing = 2;

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



    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = ClientContainer::isItem($this->getClientId());
        }
        return $this->_clientInstance;
    }



    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function getDate($format = 'Y-m-d') {
        $date = $this->_info['date'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function getActivity() {
        return $this->_info['activity'];
    }

    public function getPoint() {
        return $this->_info['point'];
    }

    public function getMessage() {
        return $this->_info['message'];
    }

    public function setMessage($message) {
        $this->_info['message'] = $message;
        $this->_changes['message'] = $this->_info['message'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        $date = $this->_info['regDate'];
        if(date('Y-m-d H:i:s', strtotime($date)) == '1970-01-01 00:00:00' || date('Y-m-d H:i:s', strtotime($date)) == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status'] = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

}