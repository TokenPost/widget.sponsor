<?php
namespace PL\Models\Site\Widget;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;
use PL\Models\Site\Site;

class Widget extends AbstractSingleton {

    const tableName = 'SiteWidget';

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


    public function getId()
    {
        return $this->_info['id'];
    }

    public function getSiteId()
    {
        return $this->_info['siteId'];
    }

    public function setSiteId($var)
    {
        $this->_info['siteId'] = $var;
        $this->_changes['siteId'] = $this->_info['siteId'];
    }

    public function getVersion()
    {
        return $this->_info['version'];
    }

    public function setVersion($var)
    {
        $this->_info['version'] = $var;
        $this->_changes['version'] = $this->_info['version'];
    }

    public function getRevision()
    {
        return $this->_info['revision'];
    }

    public function setRevision($var)
    {
        $this->_info['revision'] = $var;
        $this->_changes['revision'] = $this->_info['revision'];
    }

    public function getRequest()
    {
        return $this->_info['request'];
    }

    public function setRequest($var)
    {
        $this->_info['request'] = $var;
        $this->_changes['request'] = $this->_info['request'];
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