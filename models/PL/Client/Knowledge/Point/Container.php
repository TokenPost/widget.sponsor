<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019-11-25
 * Time: 오후 6:13
 */

namespace PL\Models\Client\Knowledge\Point;


use PL\Models\Knowledge\Category\Category;
use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;
use PL\Models\Knowledge\Item\Item;
use PL\Models\Knowledge\Knowledge;

use PL\Models\Client\Client;
use PL\Models\Client\Container as ClientContainer;
//use PL\Models\Knowledge\Category\Container as CategoryContainer;

class Container extends AbstractContainer {

    protected $_knowledgeInstance;
    protected $_knowledgeId;

    protected $_adminInstance;
    protected $_clientInstance;
    protected $_clientId;
    protected $_userType;

    protected $_categoryId;

    protected $_listStatusMode = 'active';
    protected $_noticeMode;

    public function __construct(Knowledge $knowledgeInstance = null) {
        parent::__construct(Point::tableName);
        $this->setTableName(Point::tableName);

        if($knowledgeInstance instanceof Knowledge == true){
            $this->_knowledgeInstance = $knowledgeInstance;
            $this->_knowledgeId = $knowledgeInstance->getId();
        } else {
            $this->_knowledgeInstance = null;
            $this->_knowledgeId = 0;
        }
    }

    public static function getTableNameStatic(){
        return Point::tableName;
    }

    public static function getObjectInstanceStatic($date) : Point {
        return Point::getInstance($date);
    }

    public function getObjectInstance($date) : Point {
        return Point::getInstance($date);
    }

    public function getKnowledgeInstance() {
        return $this->_knowledgeInstance;
    }

    public function getKnowledgeId(){
        return $this->_knowledgeId;
    }

    public function getCategoryId(){
        return $this->_categoryId;
    }

    public function setCategoryId($categoryId) {
        $this->_categoryId = $categoryId;
    }

    public function getListStatusMode(){
        return $this->_listStatusMode;
    }

    public function setListStatusMode($listStatusMode) {
        $this->_listStatusMode = $listStatusMode;
    }


    public function addNew($item) {
        if (isset($item['id']) == true && is_numeric($item['id']) && $item['id'] >= 1) {
            /* 수정 */
            if(isset($item['status']) == true) {
                $existItem = Point::getInstance($item['id']);
                if ($existItem->getStatus() == Point::Status_Active && $item['status'] != Point::Status_Active) {
                    // active 기준 삭제
                    $this->getKnowledgeInstance()->subtractActive();
                    $this->getKnowledgeInstance()->addInactive();
                    //$this->getKnowledgeInstance()->saveChanges();
                    if($this->getCategoryId() >= 1){
                        $this->getCategoryInstance()->subtractActive();
                        $this->getCategoryInstance()->addInactive();
                        //$this->getCategoryInstance()->saveChanges();
                    }
                } elseif ($existItem->getStatus() != Point::Status_Active && $item['status'] == Point::Status_Active) {
                    // active 기준 생성
                    $this->getKnowledgeInstance()->addActive();
                    $this->getKnowledgeInstance()->subtractInactive();
                    //$this->getKnowledgeInstance()->saveChanges();
                    if($this->getCategoryId() >= 1){
                        $this->getCategoryInstance()->addActive();
                        $this->getCategoryInstance()->subtractInactive();
                        //$this->getCategoryInstance()->saveChanges();
                    }
                }
            }
            $result = $this->db->update($this->getTableName(), array_keys($item), array_values($item), 'id = ' . $item['id']);
            if ($result == true) return $item['id'];
        } else {
            /* 생성 */

            $item['ref'] = $this->getNextRef();
            $item['knowledgeId'] = $this->getKnowledgeId();

            $result = $this->db->insert($this->getTableName(), array_values($item), array_keys($item));
            $ret = $this->db->lastInsertId();
            if ($result == true) {
                $addedItem = $this->getObjectInstance($ret);
                if($addedItem->getStatus() == Point::Status_Active){
                    $addedItem->getKnowledgeInstance()->addActive();
                } else {
                    $addedItem->getKnowledgeInstance()->addInactive();
                }
                $addedItem->getKnowledgeInstance()->addTotal();
                //$addedItem->getKnowledgeInstance()->saveChanges();

                if($addedItem->getCategoryId() >= 1){
                    if($addedItem->getStatus() == Item::Status_Active){
                        $addedItem->getCategoryInstance()->addActive();
                    } else {
                        $addedItem->getCategoryInstance()->subtractInactive();
                    }
                    $addedItem->getCategoryInstance()->addTotal();
                    //$addedItem->getCategoryInstance()->saveChanges();
                }
            }
            return $ret;
        }
        return false;
    }

    public function getNextRef(){
        return ($this->getMaxRef()+1);
    }

    public function getMaxRef(){
        $query = 'SELECT Max(`ref`) FROM `' . Point::tableName . '` WHERE KnowledgeId = ?';

        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query,array($this->getKnowledgeId()))->fetch();

        if($data[0] >= 1) {
            return $data[0];
        } else {
            return 0;
        }
    }

    public function getClientId() {
        return $this->_clientId;
    }

    public function getClientInstance() {
        if (isset($this->_clientInstance) == false) {
            $this->_clientInstance = Client::getInstance($this->getClientId());
        }
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance) {
        $this->_clientInstance = $clientInstance;
        $this->_clientId = $clientInstance->getId();
        $this->_userType = '2';
    }

    public function getNoticeMode() {
        return $this->_noticeMode;
    }

    public function setNoticeMode($noticeMode) {
        $this->_noticeMode = $noticeMode;
    }

    public function getUserType() {
        return $this->_userType;
    }

    public function setUserType($userType) {
        $this->_userType = $userType;
    }


    public function getItemsPBF(){
        $where = array();
        /*
        if($this->getMode() == 'search'){
            $searchSql = ' (';
            $searchSql .='`category` LIKE "%' . $this->getModeValue() . '%" OR ';
            $searchSql .='`title` LIKE "%' . $this->getModeValue() . '%" OR ';
            $searchSql .='`content` LIKE "%' . $this->getModeValue() . '%" ';
            $searchSql .= ' ) ';
            $where[] = $searchSql;
        }*/

        if(is_numeric($this->getNoticeMode()) == true && $this->getNoticeMode() >= 0) $where[] = '`isNotice` = '  . $this->getNoticeMode();
        if($this->getKnowledgeId() >= 1) $where[] = '`knowledgeId` = '  . $this->getKnowledgeId();
        if($this->getUserType() >= 1) {
            $where[] = '`userType` = '  . $this->getUserType();
            if($this->getClientId() >= 1) $where[] = '`userId` = '  . $this->getClientId();
        }
        if($this->getCategoryId() != '') $where[] = '`categoryId` = '  . $this->getCategoryId() . '';

        return $where;
    }

    public function getItemsPICI($var){
        /*if($this->getTimezone() != 'UTC'){
            //$var['pubDate'] = Util::convertTimezone($var['pubDate'], $this->getTimezone());
            $var['regDate'] = Util::convert2Timezone($var['regDate'], 'UTC', $this->getTimezone());
            $var['modDate'] = Util::convert2Timezone($var['modDate'], 'UTC', $this->getTimezone());
        }*/
        return $var;
    }

}