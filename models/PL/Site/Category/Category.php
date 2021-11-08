<?php
namespace PL\Models\Site\Category;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Site;
use PL\Models\Client\Client;

class Category extends AbstractSingleton {

    const tableName = 'SiteCategory';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_siteInstance;

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

    public function getSiteInstance() {
        if (isset($this->_siteInstance) == false) {
            $this->_siteInstance = Site::getInstance($this->getSiteId());
        }
        return $this->_siteInstance;
    }



}