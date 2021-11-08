<?php
namespace PL\Models\Client\Ref\Type;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Client\Ref\Type\Value\Container as ClientRefTypeValueContainer;

class Type extends AbstractSingleton {

    const tableName = 'ClientRefType';

    protected $_valueContainer;

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

    public function getValueContainer() {
        if (isset($this->_valueContainer) == false) {
            $this->_valueContainer = new ClientRefTypeValueContainer($this);
        }
        return $this->_valueContainer;
    }


    public function getId() {
        return $this->_info['id'];
    }

    public function getTitle() {
        return $this->_info['title'];
    }

    public function setValue($title) {
        $this->_info['title']    = $title;
        $this->_changes['title'] = $this->_info['title'];
    }

}