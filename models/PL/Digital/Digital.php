<?php
namespace PL\Models\Digital;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;
use PL\Models\Digital\Asset\Container as AssetContainer;

use PL\Models\Util\Util;

class Digital extends AbstractSingleton {

    const tableName = 'Digital';

    protected $_assetContainer;

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

    // siteItem
    public function getAssetContainer() {
        if (isset($this->_assetContainer) == false) {
            $this->_assetContainer = new AssetContainer($this);
        }
        return $this->_assetContainer;
    }

    public function getId()
    {
        return $this->_info['id'];
    }
    public function setId($var)
    {
        $this->_info['id'] = $var;
        $this->_changes['id'] = $this->_info['id'];
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

}