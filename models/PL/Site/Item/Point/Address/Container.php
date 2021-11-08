<?php
namespace PL\Models\Site\Item\Point\Address;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Util\Util;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

use PL\Models\Site\Item\Item as SiteItem;


class Container extends AbstractContainer {

    protected $_itemId;
    protected $_itemInstance;

    protected $_typeId;
    protected $_pointId;
    protected $_tokenTypeId;
    protected $_tokenId;
    protected $_platformId;

    public function __construct(SiteItem $itemInstance = null) {

        if(is_null($itemInstance) != true){
            $this->setSiteItemInstance($itemInstance);
        }
        parent::__construct(Address::tableName);
        $this->setTableName(Address::tableName);
    }

    public static function getTableNameStatic(){
        return Address::tableName;
    }

    public static function getObjectInstanceStatic($date) {
        return Address::getInstance($date);
    }

    public function getObjectInstance($date) {
        return Address::getInstance($date);
    }

    public function getItemId(){
        return $this->_itemId;
    }

    public function setItemId($itemId){
        $this->_itemId = $itemId;
    }

    public function getSiteItemInstance(){
        return $this->_itemInstance;
    }

    public function setSiteItemInstance(SiteItem $itemInstance) {
        $this->_itemInstance = $itemInstance;
        $this->setItemId($itemInstance->getId());
    }

    public function getTokenTypeId(){
        return $this->_tokenTypeId;
    }

    public function setTokenTypeId($tokenTypeId){
        $this->_tokenTypeId = $tokenTypeId;
    }

    public function getTokenId(){
        return $this->_tokenId;
    }

    public function setTokenId($tokenId){
        $this->_tokenId = $tokenId;
    }

    public function getPlatformId(){
        return $this->_platformId;
    }

    public function setPlatformId($platformId){
        $this->_platformId = $platformId;
    }

    /**
     * fixme: 외부에서 내부입금은 고정 주소
     */
    public function create($itemId, $tokenTypeId, $tokenId, $platformId)
    {
        $this->setItemId($itemId);
        $this->setTokenTypeId($tokenTypeId);
        $this->setTokenId($tokenId);
        $this->setPlatformId($platformId);

        $activeItems = $this->getActiveItems();
        if(sizeof($activeItems) >= 1) return null;

        $address = Address::getDefaultTokenAddress($tokenTypeId, $tokenId, $platformId);
        if($address == '') $address = Util::generateRandomAlphanumericLowercase(32);

        // check exist secret

        $newItem = array();
        $newItem['itemId']    = $itemId;
        $newItem['tokenTypeId'] = $tokenTypeId;
        $newItem['tokenId']     = $tokenId;
        $newItem['platformId']   = $platformId;
        $newItem['address']     = $address;
        $newItem['secret']      = Util::generateRandomAlphanumericLowercase(16);
        $newItem['regIp']       = CLIENT_IP;
        $newItem['regDate']     = Util::getDbNow();
        $newItem['status']      = Address::Status_Active;
        $ret = $this->addNew($newItem);
        if($ret >= 1) return self::isItem($ret);
        return null;
    }

    public function findFirst($itemId, $tokenTypeId, $tokenId, $platformId)
    {
        $this->setItemId($itemId);
        $this->setTokenTypeId($tokenTypeId);
        $this->setTokenId($tokenId);
        $this->setPlatformId($platformId);

        $activeItems = $this->getActiveItems();
        if(sizeof($activeItems) >= 1){
            return $activeItems[0];
        } else {
            return null;
        }
    }

    public function firstOrCreate($itemId, $tokenTypeId, $tokenId, $platformId)
    {
        $this->setItemId($itemId);
        $this->setTokenTypeId($tokenTypeId);
        $this->setTokenId($tokenId);
        $this->setPlatformId($platformId);

        $activeItems = $this->getActiveItems();
        if(sizeof($activeItems) >= 1){
            return $activeItems[0];
        } else {
            return $this->create($itemId, $tokenTypeId, $tokenId, $platformId);
        }
    }




    public function getNextNonce($tokenTypeId, $tokenId, $platformId, $version = 1){
        // nonce는 1부터 시작하므로 1을 리턴해도 괜찮다.
        return ($this->getMaxNonce($tokenTypeId, $tokenId, $platformId, $version) + 1);
    }

    public function getMaxNonce($tokenTypeId, $tokenId, $platformId, $version = 1){
        $query = 'SELECT Max(`nonce`) FROM `' . $this->getTableName() . '` WHERE tokenTypeId = ? AND tokenId = ? AND platformId = ? AND version = ? ';

        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query,array($tokenTypeId, $tokenId, $platformId, $version))->fetch();

        if($data[0] >= 1) {
            return $data[0];
        } else {
            return 0;
        }
    }

    public function checkNonceDuplication($tokenTypeId, $tokenId, $platformId, $version, $nonce){
        $query = 'SELECT COUNT(`id`) FROM `' . $this->getTableName() . '` WHERE tokenTypeId = ? AND tokenId = ? AND platformId = ? AND version = ? AND nonce = ? ';

        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query,array($tokenTypeId, $tokenId, $platformId, $version, $nonce))->fetch();

        if($data[0] >= 1) {
            return $data[0];
        } else {
            return 0;
        }
    }


    public static function displayHyphen($string){
        // 주소용으 변환


        //@todo:반대로 수정.
        $string = str_replace('-', '', $string);
        $string = str_replace('_', '', $string);
        $string = str_replace(',', '', $string);
        $string = str_replace('.', '', $string);
        return $string;
    }

    public function _isTokenAddress($tokenTypeId, $tokenId, $platformId, $address, $status = '')
    {

        $this->setTokenTypeId($tokenTypeId);
        $this->setTokenId($tokenId);
        $this->setPlatformId($platformId);

        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('address', '=', '"' . Util::sanitizeAlphanumeric($address) . '"'));
        $this->setFilterContainer($filterContainer);

        if($status != ''){
            $items = $this->getActiveItems($status);
        } else {
            $items = $this->getItems();
        }

        if(sizeof($items) >= 1){
            return $items[0];
        }
        return null;
    }



    public static function isTokenSecretFormat($tokenTypeId, $tokenId, $secret){
        if($secret != Util::sanitizeAlphanumeric($secret)) return false;
        return true;
    }

    public function _isTokenSecret($tokenTypeId, $tokenId, $platformId, $secret, $status = '')
    {
        $this->setTokenTypeId($tokenTypeId);
        $this->setTokenId($tokenId);
        $this->setPlatformId($platformId);

        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('secret', '=', '"' . Util::sanitizeAlphanumeric($secret) . '"'));
        $this->setFilterContainer($filterContainer);

        if($status != ''){
            $items = $this->getActiveItems($status);
        } else {
            $items = $this->getItems();
        }

        if(sizeof($items) >= 1){
            return $items[0];
        }
        return null;
    }

    public static function isTokenSecret($tokenTypeId, $tokenId, $platformId, $secret = '')
    {

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `tokenTypeId` = ? AND `tokenId` = ? AND `platformId` = ? AND `secret` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db_master');
        //$db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $data = $db->query($query, array($tokenTypeId, $tokenId, $platformId, Util::sanitizeAlphanumeric($secret)))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }


    public static function isTokenAddress($tokenTypeId, $tokenId, $platformId, $address, $version = 0)
    {
        $address = trim(str_replace('-','', $address));
        $address = Util::sanitizeAlphanumeric($address);

        if($version >= 1){
            $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `tokenTypeId` = ? AND `tokenId` = ? AND `platformId` = ? AND `address` = ? AND `version` = ? LIMIT 1";
        } else {
            $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `tokenTypeId` = ? AND `tokenId` = ? AND `platformId` = ? AND `address` = ? LIMIT 1";
        }

        $db     = DI::getDefault()->getShared('db_master');
        //$db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');

        $condition = array($tokenTypeId, $tokenId, $platformId, $address);
        if($version >= 1) $condition[] = $version;

        var_dump($query);
        var_dump($condition);
        $data = $db->query($query, $condition)->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }

    public static function isTokenAddressSecret($tokenTypeId, $tokenId, $platformId, $address, $secret = '')
    {
        $address = trim(str_replace('-','', $address));
        $address = Util::sanitizeAlphanumeric($address);
        $secret = trim(str_replace('-','', $secret));
        $secret = Util::sanitizeAlphanumeric($secret);

        $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `tokenTypeId` = ? AND `tokenId` = ? AND `platformId` = ? AND `address` = ? AND `secret` = ? LIMIT 1";
        $db     = DI::getDefault()->getShared('db_master');
        //$db     = DI::getDefault()->getShared('db');

        //$db     = DI::getDefault()->get('master/slave db');
        $data = $db->query($query, array($tokenTypeId, $tokenId, $platformId, $address, $secret))->fetch();

        if(is_array($data) == true) {
            return static::getObjectInstanceStatic($data);
        }
        return null;
    }

    public function getItemsPBF() {
        $where = array();

        if (Util::isInteger($this->getItemId()) == true) $where[] = 'itemId = ' . $this->getItemId();
        if (Util::isInteger($this->getTokenTypeId()) == true) $where[] = 'tokenTypeId = ' . $this->getTokenTypeId();
        if (Util::isInteger($this->getTokenId()) == true) $where[] = 'tokenId = ' . $this->getTokenId();
        if (Util::isInteger($this->getPlatformId()) == true) $where[] = 'platformId = ' . $this->getPlatformId();

        return $where;
    }

}
