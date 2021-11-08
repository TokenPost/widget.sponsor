<?php
namespace PL\Models\Client\Ref;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Client\Client;
use PL\Models\Client\Ref\Type\Type as ClientRefType;

class Ref extends AbstractSingleton {

    const tableName = 'ClientRef';

    protected $_typeInstance;
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


    public function getTypeInstance() {
        if (isset($this->_typeInstance) == false) {
            $this->_typeInstance = ClientRefType::getInstance($this->getTypeId());
        }
        return $this->_typeInstance;
    }

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }


    public function modify($item){
        $this->setTypeId($item['key']);
        $this->setValue($item['value']);
        $this->saveChanges();
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

    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($typeId) {
        $this->_info['typeId']    = $typeId;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

    public function getValue() {
        return $this->_info['value'];
    }

    public function setValue($value) {
        $this->_info['value']    = $value;
        $this->_changes['value'] = $this->_info['value'];
    }

}