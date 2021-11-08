<?php
namespace PL\Models\Site\Item\Api\Token;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Item\Api\Api;
use PL\Models\Client\Client;

class Token extends AbstractSingleton {

    const tableName = 'SiteItemApiToken';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_apiInstance;

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

    public function getApiInstance() {
        if (isset($this->_apiInstance) == false) {
            $this->_apiInstance = Api::getInstance($this->getApiId());
        }
        return $this->_apiInstance;
    }



}