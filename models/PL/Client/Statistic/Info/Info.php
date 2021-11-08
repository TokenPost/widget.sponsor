<?php
namespace PL\Models\Client\Statistic\Info;

use Exception;
use Phalcon\DI;
use Phalcon\Db;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;

class Info extends AbstractSingleton {

    const tableName = 'ClientStatisticInfo';

    const Gender_Male     = 'M';
    const Gender_Female   = 'F';

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

    public function getId()
    {
        return $this->_info['id'];
    }

    public function getYear()
    {
        return $this->_info['year'];
    }

    public function setYear($var)
    {
        $this->_info['year'] = $var;
        $this->_changes['year'] = $this->_info['year'];
    }

    public function getCode()
    {
        return $this->_info['code'];
    }

    public function setCode($var)
    {
        $this->_info['code'] = $var;
        $this->_changes['code'] = $this->_info['code'];
    }

    public function getCount()
    {
        return $this->_info['count'];
    }

    public function setCount($var)
    {
        $this->_info['count'] = $var;
        $this->_changes['count'] = $this->_info['count'];
    }

    public function getMale()
    {
        return $this->_info['male'];
    }

    public function setMale($var)
    {
        $this->_info['male'] = $var;
        $this->_changes['male'] = $this->_info['male'];
    }

    public function getFemale()
    {
        return $this->_info['female'];
    }

    public function setFemale($var)
    {
        $this->_info['female'] = $var;
        $this->_changes['female'] = $this->_info['female'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s')
    {
        $date = trim($this->_info['regDate']);
        if($date == '' || $date == '1970-01-01 00:00:00' || $date == '0000-00-00 00:00:00') return '';
        return date($format, strtotime($date));
    }

    public function setRegDate($var)
    {
        $this->_info['regDate'] = $var;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }


}