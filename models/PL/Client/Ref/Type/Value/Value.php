<?php
namespace PL\Models\Client\Ref\Type\Value;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Client\Ref\Type\Type as ClientRefType;

class Value extends AbstractSingleton {

    const tableName = 'ClientRefTypeValue';

    protected $_typeInstance;

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


    public function getId() {
        return $this->_info['id'];
    }


    public function getTypeId() {
        return $this->_info['typeId'];
    }

    public function setTypeId($typeId) {
        $this->_info['typeId']    = $typeId;
        $this->_changes['typeId'] = $this->_info['typeId'];
    }

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setTitle($title) {
        $this->_info['title']    = $title;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getValue() {
        return $this->_info['value'];
    }

    public function setValue($value) {
        $this->_info['value']    = $value;
        $this->_changes['value'] = $this->_info['value'];
    }

    public function getLanguageId() {
        return $this->_info['languageId'];
    }

    public function setLanguageId($languageId) {
        $this->_info['languageId']    = $languageId;
        $this->_changes['languageId'] = $this->_info['languageId'];
    }

    public function getLanguageCode() {
        return $this->_info['languageCode'];
    }

    public function setLanguageCode($languageCode) {
        $this->_info['languageCode']    = $languageCode;
        $this->_changes['languageCode'] = $this->_info['languageCode'];
    }

}