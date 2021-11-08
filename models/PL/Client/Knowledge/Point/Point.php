<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019-11-25
 * Time: 오후 6:06
 */

namespace PL\Models\Client\Knowledge\Point;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Knowledge\Knowledge;

use PL\Models\Knowledge\Item\Item as KnowledgeItem;
use PL\Models\Knowledge\Item\Container as KnowledgeItemContainer;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;

class Point extends AbstractSingleton {

    const tableName = 'KnowledgePoint';

    const Status_Active     = 0;  // Active
    const Status_Inactive   = 1;  // Inactive

    protected $_knowledgeInstance;
    protected $_clientInstance;

    public static function getInstance($data, $keyIndex = 'id') {
        if (is_numeric($data) === true) {
            $db     = DI::getDefault()->getShared('db');
            $result = $db->query('SELECT * FROM `' . self::tableName . '` WHERE `' . $keyIndex . '` = ?', array($data));

            $data = $result->fetch();
        } else if (is_array($data) === false || array_key_exists($keyIndex, $data) === false) {
            throw new Exception(self::tableName . ' ' . $keyIndex . ' error.');
        }

        return parent::getInstance($data, $keyIndex);
    }

    public function getKnowledgeInstance() {
        if (isset($this->_knowledgeInstance) == false) {
            $this->_knowledgeInstance = Knowledge::getInstance($this->getKnowledgeId());
        }
        return $this->_knowledgeInstance;
    }


    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function getId() {
        return $this->_info['id'];
    }

    public function getKnowledgeId() {
        return $this->_info['knowledgeId'];
    }

    public function setKnowledgeId($knowledgeId) {
        $this->_info['knowledgeId']    = $knowledgeId;
        $this->_changes['knowledgeId'] = $this->_info['knowledgeId'];
    }

    public function getClientId() {
        return $this->_info['clientId'];
    }

    public function setClientId($clientId) {
        $this->_info['clientId']    = $clientId;
        $this->_changes['clientId'] = $this->_info['clientId'];
    }

    public function getKnowledgeName(){
        return $this->getKnowledgeInstance()->getTitle();
    }

    public function getPoint() {
        return $this->_info['ref'];
    }

    public function setPoint($point) {
        $this->_info['point']    = $point;
        $this->_changes['point'] = $this->_info['point'];
    }

    public function addPoint($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `point` = `point` + ? WHERE id = ?', array($i, $this->getId()));
        //$this->setTotal($this->getTotal()+1);
    }

    public function subtractPoint($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `point` = `point` - ? WHERE id = ?', array($i, $this->getId()));
        //$this->setTotal($this->getTotal()-1);
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
        //$this->setTotal($this->getTotal()+1);
    }

    public function subtractTotal($i = 1) {
        $this->db->query('UPDATE ' . self::tableName . ' set `total` = `total` - ? WHERE id = ?', array($i, $this->getId()));
        //$this->setTotal($this->getTotal()-1);
    }

    public function getRegDate($format = 'Y-m-d H:i:s') {
        if($this->_info['regDate'] == '') return '';
        return date($format, strtotime($this->_info['regDate']));
    }

    public function setRegDate($regDate) {
        $this->_info['regDate']    = $regDate;
        $this->_changes['regDate'] = $this->_info['regDate'];
    }
}