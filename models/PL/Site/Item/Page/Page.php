<?php
namespace PL\Models\Site\Item\Page;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Item;
use PL\Models\Client\Client;

class Page extends AbstractSingleton {

    const tableName = 'SiteItemPage';

    protected $_itemInstance;

    public static function getInstance($data, $keyIndex = 'id') : self {
        if (Util::isInteger($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));
            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getItemInstance() {
        if (isset($this->_itemInstance) == false) {
            $this->_itemInstance = Item::getInstance($this->getItemId());
        }
        return $this->_itemInstance;
    }


    public function getId()
    {
        return $this->_info['id'];
    }

    public function getItemId()
    {
        return $this->_info['itemId'];
    }

    public function setItemId($var)
    {
        $this->_info['itemId'] = $var;
        $this->_changes['itemId'] = $this->_info['itemId'];
    }

    public function getHost()
    {
        return $this->_info['host'];
    }

    public function setHost($var)
    {
        $this->_info['host'] = $var;
        $this->_changes['host'] = $this->_info['host'];
    }

    public function getPath()
    {
        return $this->_info['path'];
    }

    public function setPath($var)
    {
        $this->_info['path'] = $var;
        $this->_changes['path'] = $this->_info['path'];
    }

    public function getTitle()
    {
        return $this->_info['title'];
    }

    public function setTitle($var)
    {
        $this->_info['title'] = $var;
        $this->_changes['title'] = $this->_info['title'];
    }

    public function getImage()
    {
        return $this->_info['image'];
    }

    public function setImage($var)
    {
        $this->_info['image'] = $var;
        $this->_changes['image'] = $this->_info['image'];
    }

    public function getDescription()
    {
        return $this->_info['description'];
    }

    public function setDescription($var)
    {
        $this->_info['description'] = $var;
        $this->_changes['description'] = $this->_info['description'];
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