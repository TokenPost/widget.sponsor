<?php
namespace PL\Models\Client\Ref\Type\Value;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Ref\Type\Type;
use PL\Models\Client\Ref\Type\Container as ClientRefTypeContainer;

class Container extends AbstractContainer {

    protected $_sortedItems;

    protected $_typeId;
    protected $_typeInstance;

    public function __construct(Type $typeInstance = null) {
        parent::__construct(Value::tableName);
        $this->setTableName(Value::tableName);
        $this->setOrder('`id` asc, ');

        if(is_null($typeInstance) == false){
            $this->setTypeInstance($typeInstance);
            $this->setTypeId($typeInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Value::tableName;
    }

    public static function getObjectInstanceStatic($date) : Value {
        return Value::getInstance($date);
    }

    public function getObjectInstance($date) : Value {
        return Value::getInstance($date);
    }



    public function getTypeId(){
        return $this->_typeId;
    }

    public function setTypeId($typeId){
        $this->_typeId = $typeId;
    }

    public function getTypeInstance(){
        return $this->_typeInstance;
    }

    public function setTypeInstance($typeInstance){
        $this->_typeInstance = $typeInstance;
    }

    public static function getValueTranslate($typeId, $value, $languageCode, $result = 'title'){
        if(is_numeric($languageCode) === true){
            $query = 'SELECT * FROM `' . self::getTableNameStatic() . '` WHERE typeId = ?  AND `value` = ? AND languageId = ?';
        } else {
            $query = 'SELECT * FROM `' . self::getTableNameStatic() . '` WHERE typeId = ?  AND `value` = ? AND languageCode = ?';
        }

        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query, array($typeId, $value, $languageCode))->fetch();

        if(is_array($data) == true) {
            if($result == 'title'){
                return $data['title'];
            } else {
                return Value::getInstance($data);
            }
        } else {
            return false;
        }
    }

    public static function isValueTitles($title, $typeId = null, $language = null){
        $query = 'SELECT * FROM `' . self::getTableNameStatic() . '`';

        $query .= ' WHERE ';
        if(is_numeric($typeId) === true && $typeId >= 1){
            $query .= 'typeId = ' . $typeId . ' AND ';
        }
        if(is_numeric($language) === true && $language >= 1){
            $query .= 'languageId = ' . $language . ' AND ';
        }elseif(is_string($language) === true && $language != ''){
            $query .= 'languageCode = "' . $language . '" AND ';
        }

        $query .= ' title LIKE "%' . addslashes($title) . '%"';

        $db   = DI::getDefault()->getShared('db');
        $result = $db->query($query)->fetchAll();

        if ($result === false) return array();
        $return = array();
        foreach ($result as $var) {
            $return[] = Value::getInstance($var);
        }
        return $return;

    }

}
