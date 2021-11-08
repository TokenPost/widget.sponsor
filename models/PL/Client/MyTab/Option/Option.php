<?php
namespace PL\Models\Client\MyTab\Option;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;


class Option extends AbstractSingleton {

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

    const tableName = 'ClientMyTabOption';

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

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientMyTabId() {
        return $this->_info['clientMyTabId'];
    }

    public function setClientMyTabId($clientMyTabId) {
        $this->_info['clientMyTabId']    = $clientMyTabId;
        $this->_changes['clientMyTabId'] = $this->_info['clientMyTabId'];
    }

    public function getField() {
        return $this->_info['field'];
    }

    public function setField($field) {
        $this->_info['field']    = $field;
        $this->_changes['field'] = $this->_info['field'];
    }

    public function getCount() {
        return $this->_info['count'];
    }

    public function setCount($count) {
        $this->_info['count']    = $count;
        $this->_changes['count'] = $this->_info['count'];
    }

    public function getOption() {
        return $this->_info['option'];
    }

    public function setOption($option) {
        $this->_info['option']    = $option;
        $this->_changes['option'] = $this->_info['option'];
    }

    public function getValue() {
        return $this->_info['value'];
    }

    public function setValue($value) {
        $this->_info['value']    = $value;
        $this->_changes['value'] = $this->_info['value'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }


}