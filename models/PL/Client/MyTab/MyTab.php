<?php
namespace PL\Models\Client\MyTab;

use Exception;
use PL\Models\Client\MyTab\Option\Option;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Client\MyTab\Option\Container as OptionContainer;


class MyTab extends AbstractSingleton {

    const Status_Active   = 0; // Active
    const Status_Inactive = 1; // Inactive

    public $_option;

    const tableName = 'ClientMyTab';

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

    public function getOptionContainer() {
        if (isset($this->_option) == false) {
            $this->_option = new OptionContainer($this);
        }
        return $this->_option;
    }

    public function isMatchClientId($clientId) {
        return ($clientId == $this->_info['clientId']);
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

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($title) {
        $this->_info['title']    = $title;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }


}