<?php
namespace PL\Models\Site\Item\Widget;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Item;
use PL\Models\Client\Client;

class Widget extends AbstractSingleton {

    const tableName = 'SiteItemWidget';

    // 위젯 스타일
    const Color_Original    = 1;
    const Color_Light       = 2;
    const Color_Dark        = 3;

    const Height_Type1    = 82;     // 위젯 스타일 Original
    const Height_Type2    = 77;     // 위젯 스타일 Light / Dark

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_itemInstance;

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

    // SiteItem
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

    public function getRegionId()
    {
        return $this->_info['regionId'];
    }

    public function setRegionId($var)
    {
        $this->_info['regionId'] = $var;
        $this->_changes['regionId'] = $this->_info['regionId'];
    }

    public function getServerId()
    {
        return $this->_info['serverId'];
    }

    public function setServerId($var)
    {
        $this->_info['serverId'] = $var;
        $this->_changes['serverId'] = $this->_info['serverId'];
    }

    public function getTypeId()
    {
        return $this->_info['typeId'];
    }

    public function setTypeId($var)
    {
        $this->_info['typeId'] = $var;
        $this->_changes['typeId'] = $this->_info['typeId'];
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

    public function getColorId()
    {
        return $this->_info['colorId'];
    }

    public function setColorId($var)
    {
        $this->_info['colorId'] = $var;
        $this->_changes['colorId'] = $this->_info['colorId'];
    }

    public function getWidth()
    {
        return $this->_info['width'];
    }

    public function setWidth($var)
    {
        $this->_info['width'] = $var;
        $this->_changes['width'] = $this->_info['width'];
    }

    public function getHeight()
    {
        return $this->_info['height'];
    }

    public function setHeight($var)
    {
        $this->_info['height'] = $var;
        $this->_changes['height'] = $this->_info['height'];
    }

    public function getStatus()
    {
        return $this->_info['status'];
    }

    public function setStatus($var)
    {
        $this->_info['status'] = $var;
        $this->_changes['status'] = $this->_info['status'];
    }





}