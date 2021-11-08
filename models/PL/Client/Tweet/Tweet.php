<?php
namespace PL\Models\Client\Tweet;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Admin\Admin;
use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

use PL\Models\Util\Util;

class Tweet extends AbstractSingleton {

    const tableName = 'ClientTweet';

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


    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = ClientContainer::isItem($this->getId());
        }
        return $this->_clientInstance;
    }

    public static function isItem($itemId, $field = 'id') {
        if(is_numeric($itemId) == true){
            $query = "SELECT * FROM `" . static::getTableNameStatic() . "` WHERE `" . $field . "` = ? LIMIT 1";
            $db     = DI::getDefault()->getShared('db');

            //$db     = DI::getDefault()->getShared('master/slave db');
            $data = $db->query($query, array($itemId))->fetch();

            if(is_array($data) == true) {
                return self::getObjectInstance($data);
            }
        }
        return false;
    }

    public static function create($clientId) {
        if(is_numeric($clientId) == false || $clientId < 1) return false;


        $item['id']                  = $clientId;
        $item['active']              = 0;
        $item['inactive']            = 0;
        $item['total']               = 0;
        $item['lastUpdate']          = "0000-00-00 00:00:00";
        $item['lastUpdateTimestamp'] = "0000-00-00 00:00:00";


        $db        = DI::getDefault()->getShared('db');
        $db_master = DI::getDefault()->getShared('db_master');

        $result = $db_master->insert(self::tableName, array_values($item), array_keys($item));
        return $result;
    }




    public function getId() {
        return $this->_info['id'];
    }

    public function getActive() {
        return $this->_info['active'];
    }

    public function setActive($active) {
        $this->_info['active']    = $active;
        $this->_changes['active'] = $this->_info['active'];
    }

    public function addActive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `active` = `active` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractActive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `active` = `active` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getInactive() {
        return $this->_info['inactive'];
    }

    public function setInactive($inactive) {
        $this->_info['inactive']    = $inactive;
        $this->_changes['inactive'] = $this->_info['inactive'];
    }

    public function addInactive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `inactive` = `inactive` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractInactive($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `inactive` = `inactive` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotal() {
        return $this->_info['total'];
    }

    public function setTotal($total) {
        $this->_info['total']    = $total;
        $this->_changes['total'] = $this->_info['total'];
    }

    public function addTotal($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `total` = `total` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotal($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `total` = `total` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getActiveReply() {
        return $this->_info['activeReply'];
    }

    public function setActiveReply($active) {
        $this->_info['activeReply']    = $active;
        $this->_changes['activeReply'] = $this->_info['activeReply'];
    }

    public function addActiveReply($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `activeReply` = `activeReply` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractActiveReply($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `activeReply` = `activeReply` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getTotalReply() {
        return $this->_info['totalReply'];
    }

    public function setTotalReply($total) {
        $this->_info['totalReply']    = $total;
        $this->_changes['totalReply'] = $this->_info['totalReply'];
    }

    public function addTotalReply($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalReply` = `totalReply` + ? WHERE id = ?', array($i, $this->getId()));
    }

    public function subtractTotalReply($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `totalReply` = `totalReply` - ? WHERE id = ?', array($i, $this->getId()));
    }

    public function getLastUpdate($format = 'Y-m-d H:i:s') {
        if($this->_info['lastUpdate'] == '' || $this->_info['lastUpdate'] == '0000-00-00 00:00:00') return '';

        return date($format, strtotime($this->_info['lastUpdate']));
    }

    public function setLastUpdate($lastUpdate) {
        $this->_info['lastUpdate']    = $lastUpdate;
        $this->_changes['lastUpdate'] = $this->_info['lastUpdate'];
    }

    public function getLastUpdateTimestamp($format = 'Y-m-d H:i:s') {
        if($this->_info['lastUpdateTimestamp'] == '' || $this->_info['lastUpdateTimestamp'] == '0000-00-00 00:00:00') return '';

        return date($format, strtotime($this->_info['lastUpdateTimestamp']));
    }

    public function setLastUpdateTimestamp($lastUpdateTimestamp) {
        $this->_info['lastUpdateTimestamp']    = $lastUpdateTimestamp;
        $this->_changes['lastUpdateTimestamp'] = $this->_info['lastUpdateTimestamp'];
    }




}