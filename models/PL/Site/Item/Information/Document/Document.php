<?php
namespace PL\Models\Site\Item\Information\Document;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Information\Information as Information;
use PL\Models\Site\Item\Information\Container as InformationContainer;

class Document extends AbstractSingleton {


    const tableName = 'SiteItemInformationDocument';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_managerInstance;
    protected $_informationInstance;

    protected $_widgetContainer;
    protected $_siteItemContainer;

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

    public function getInformationInstance() : Information {
        if (isset($this->_informationInstance) == false) {
            $this->_informationInstance = Information::getInstance($this->getInformationId());
            $this->_informationInstance = InformationContainer::isItem($this->getInformationId());
        }
        return $this->_informationInstance;
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

    public function getSiteId()
    {
        return $this->_info['siteId'];
    }

    public function setSiteId($var)
    {
        $this->_info['siteId'] = $var;
        $this->_changes['siteId'] = $this->_info['siteId'];
    }

    public function getInformationId()
    {
        return $this->_info['informationId'];
    }

    public function setInformationId($var)
    {
        $this->_info['informationId'] = $var;
        $this->_changes['informationId'] = $this->_info['informationId'];
    }

    public function getFileId()
    {
        return $this->_info['fileId'];
    }

    public function setFileId($var)
    {
        $this->_info['fileId'] = $var;
        $this->_changes['fileId'] = $this->_info['fileId'];
    }

    public function getRegIp()
    {
        return $this->_info['regIp'];
    }

    public function setRegIp($var)
    {
        $this->_info['regIp'] = $var;
        $this->_changes['regIp'] = $this->_info['regIp'];
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