<?php
namespace PL\Models\Site\Item\Information;

use Exception;
use Phalcon\DI;
use Phalcon\Db;


use PL\Models\Util\Util;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Site\Item\Item as SiteItem;
use PL\Models\Site\Item\Container as SiteItemContainer;

use PL\Models\Site\Item\Information\Document\Container as DocumentContainer;

use PL\Models\Site\Item\File\File;
use PL\Models\Site\Item\File\Container as FileContainer;


class Information extends AbstractSingleton {

    const tableName = 'SiteItemInformation';

    const Status_Active   = 0;
    const Status_Inactive = 1;

    protected $_siteInstance;
    protected $_siteItemInstance;
    protected $_fileInstance;

    protected $_rewardContainer;
    protected $_documentContainer;

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

    public function getSiteItemInstance() : SiteItem {
        if (isset($this->_siteItemInstance) == false) {
            $this->_siteItemInstance = SiteItem::getInstance($this->getItemId());
            $this->_siteItemInstance = SiteItemContainer::isItem($this->getItemId());
        }
        return $this->_siteItemInstance;
    }

    public function getFileInstance() : File {
        if (isset($this->_fileInstance) == false) {
            $this->_fileInstance = File::getInstance($this->getLogoId());
            $this->_fileInstance = FileContainer::isItem($this->getLogoId());
        }
        return $this->_fileInstance;
    }

    public function getDocumentContainer() : DocumentContainer {
        if (isset($this->_documentContainer) == false) {
            $this->_documentContainer = new DocumentContainer($this);
        }
        return $this->_documentContainer;
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

    public function getSiteName()
    {
        return $this->_info['siteName'];
    }

    public function setSiteName($var)
    {
        $this->_info['siteName'] = $var;
        $this->_changes['siteName'] = $this->_info['siteName'];
    }

    public function getRepresentative()
    {
        return $this->_info['representative'];
    }

    public function setRepresentative($var)
    {
        $this->_info['representative'] = $var;
        $this->_changes['representative'] = $this->_info['representative'];
    }

    public function getEditor()
    {
        return $this->_info['editor'];
    }

    public function setEditor($var)
    {
        $this->_info['editor'] = $var;
        $this->_changes['editor'] = $this->_info['editor'];
    }

    public function getLogoId()
    {
        return $this->_info['logoId'];
    }

    public function setLogoId($var)
    {
        $this->_info['logoId'] = $var;
        $this->_changes['logoId'] = $this->_info['logoId'];
    }

    public function getRegistrationNumber()
    {
        return $this->_info['registrationNumber'];
    }

    public function setRegistrationNumber($var)
    {
        $this->_info['registrationNumber'] = $var;
        $this->_changes['registrationNumber'] = $this->_info['registrationNumber'];
    }

    public function getRegistrationDate($format = 'Y-m-d')
    {
        $date = $this->_info['registrationDate'];
        if(date('Y-m-d', strtotime($date)) == '1970-01-01' || date('Y-m-d', strtotime($date)) == '0000-00-00') return '';
        return date($format, strtotime($date));
    }

    public function setRegistrationDate($var)
    {
        $this->_info['registrationDate'] = $var;
        $this->_changes['registrationDate'] = $this->_info['registrationDate'];
    }

    public function getRegistrationTel()
    {
        return $this->_info['registrationTel'];
    }

    public function setRegistrationTel($var)
    {
        $this->_info['registrationTel'] = $var;
        $this->_changes['registrationTel'] = $this->_info['registrationTel'];
    }

    public function getAddress()
    {
        return $this->_info['address'];
    }

    public function setAddress($var)
    {
        $this->_info['address'] = $var;
        $this->_changes['address'] = $this->_info['address'];
    }

    public function getHomepage()
    {
        return $this->_info['homepage'];
    }

    public function setHomepage($var)
    {
        $this->_info['homepage'] = $var;
        $this->_changes['homepage'] = $this->_info['homepage'];
    }

    public function getDocuments()
    {
        return $this->_info['documents'];
    }

    public function setDocuments($var)
    {
        $this->_info['documents'] = $var;
        $this->_changes['documents'] = $this->_info['documents'];
    }

    public function getRegId()
    {
        return $this->_info['regId'];
    }

    public function setRegId($var)
    {
        $this->_info['regId'] = $var;
        $this->_changes['regId'] = $this->_info['regId'];
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