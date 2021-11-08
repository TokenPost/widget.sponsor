<?php

namespace PL\Models\Client\Company;

use PL\Models\Client\Client;
use Exception;
use Phalcon\Db;
use Phalcon\Di;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Recruit\Item\Item;
use PL\Models\Recruit\Item\Container as ItemContainer;

use PL\Models\File\File;
use PL\Models\Recruit\Item\Apply\Apply;
use PL\Models\Recruit\Item\Apply\Container as ApplyContainer;

use PL\Models\File\Container as FileContainer;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 2018-01-24
 * Time: 오후 4:48
 */
class Company extends AbstractSingleton
{
    const tableName = 'RecruitCompany';

    protected $_logoInstance;
    protected $_licenseInstance;
    protected $_clientInstance;

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

    public static function getTableNameStatic(){
        return self::tableName;
    }

    public static function getObjectInstanceStatic($date) : self {
        return self::getInstance($date);
    }

    public function getObjectInstance($date) : self {
        return self::getInstance($date);
    }


    public function getLogoInstance() {
        if (isset($this->_logoInstance) == false) {
            if ( $this->getLogoId() > 0 ) {
                $this->_logoInstance = FileContainer::isItem($this->getLogoId());
            } else {
                $this->_logoInstance = null;
            }
        }

        return $this->_logoInstance;
    }

    public function getLicenseInstance() {
        if (isset($this->_licenseInstance) == false) {
            if ( $this->getLicenseFileId() > 0 ) {
                $this->_licenseInstance = FileContainer::isItem($this->getLicenseFileId());
            } else {
                $this->_licenseInstance = null;
            }
        }
        return $this->_licenseInstance;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId'] = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getClientInstance(){
        if (isset($this->_clientInstance) == false) {
            if ( $this->getClientId() > 0 ) {
                $this->_clientInstance = Client::getInstance($this->getClientId());
            } else {
                $this->_clientInstance = null;
            }
        }
        return $this->_clientInstance;
    }

    public function getName() {
        return $this->_info['name'];
    }

    public function setName($name) {
        $this->_info['name']    = $name;
        $this->_changes['name'] = $this->_info['name'];
    }

    public function getPresident() {
        return $this->_info['president'];
    }

    public function setPresident($president) {
        $this->_info['president']    = $president;
        $this->_changes['president'] = $this->_info['president'];
    }

    public function getAddress() {
        return $this->_info['address'];
    }

    public function setAddress($address) {
        $this->_info['address']    = $address;
        $this->_changes['address'] = $this->_info['address'];
    }

    public function getAddressDetail() {
        return $this->_info['addressDetail'];
    }

    public function setAddressDetail($addressDetail) {
        $this->_info['addressDetail']    = $addressDetail;
        $this->_changes['addressDetail'] = $this->_info['addressDetail'];
    }

    public function getTelephone() {
        return $this->_info['telephone'];
    }

    public function setTelephone($telephone) {
        $this->_info['telephone']    = $telephone;
        $this->_changes['telephone'] = $this->_info['telephone'];
    }

    public function getHomepage() {
        return $this->_info['homepage'];
    }

    public function setHomepage($homepage) {
        $this->_info['homepage']    = $homepage;
        $this->_changes['homepage'] = $this->_info['homepage'];
    }

    public function getLogoId() {
        return $this->_info['logoId'];
    }

    public function setLogoId($logoId) {
        $this->_info['logoId']    = $logoId;
        $this->_changes['logoId'] = $this->_info['logoId'];
    }

    public function getLicenseFileId() {
        return $this->_info['licenseFileId'];
    }

    public function setLicenseFileId($licenseFileId) {
        $this->_info['licenseFileId']    = $licenseFileId;
        $this->_changes['licenseFileId'] = $this->_info['licenseFileId'];
    }

    public function getManagerName() {
        return $this->_info['managerName'];
    }

    public function setManagerName($managerName) {
        $this->_info['managerName']    = $managerName;
        $this->_changes['managerName'] = $this->_info['managerName'];
    }

    public function getManagerDepartment() {
        return $this->_info['managerDepartment'];
    }

    public function setManagerDepartment($managerDepartment) {
        $this->_info['managerDepartment']    = $managerDepartment;
        $this->_changes['managerDepartment'] = $this->_info['managerDepartment'];
    }

    public function getManagerPhone() {
        return $this->_info['managerPhone'];
    }

    public function setManagerPhone($managerPhone) {
        $this->_info['managerPhone']    = $managerPhone;
        $this->_changes['managerPhone'] = $this->_info['managerPhone'];
    }

    public function getManagerEmail() {
        return $this->_info['managerEmail'];
    }

    public function setManagerEmail($managerEmail) {
        $this->_info['managerEmail']    = $managerEmail;
        $this->_changes['managerEmail'] = $this->_info['managerEmail'];
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }

    public function getLicense() {
        return $this->_info['license'];
    }

    public function setLicense($license) {
        $this->_info['license']    = $license;
        $this->_changes['license'] = $this->_info['license'];
    }

    public function getStatus() {
        return $this->_info['status'];
    }

    public function setStatus($status) {
        $this->_info['status']    = $status;
        $this->_changes['status'] = $this->_info['status'];
    }

    public function getLogoUrl(){
        $logoId = $this->getLogoId();
        $fileInstance = FileContainer::isItem($logoId);

        if ($fileInstance == false) return '';
        return $fileInstance->getFullLinkUrl();
    }

    public function getItemByKey($key){
        switch(trim($key)){
            case 'getLogoUrl':
                return $this->getLogoUrl();
                break;
            default:
            return $this->_info[$key];
            break;
        }
    }

}