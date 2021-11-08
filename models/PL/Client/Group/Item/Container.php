<?php
namespace PL\Models\Client\Group\Item;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Group\Group;
use PL\Models\Util\Util;

class Container extends AbstractContainer {


    protected $_groupInstance;
    protected $_groupId;

    public function __construct(Group $groupInstance = null) {
        if(is_null($groupInstance) == false){
            $this->_groupInstance = $groupInstance;
            $this->_groupId = $groupInstance->getId();
        }
        parent::__construct(Item::tableName);
        $this->setTableName(Item::tableName);
    }

    public static function getTableNameStatic(){
        return Item::tableName;
    }

    public static function getObjectInstanceStatic($date) : Item {
        return Item::getInstance($date);
    }

    public function getObjectInstance($date) : Item {
        return Item::getInstance($date);
    }



    public function getGroupInstance(){
        return $this->_groupInstance;
    }

    public function getGroupId(){
        return $this->_groupId;
    }



    public static function inactiveItem($groupId, $email) {

        // check groupId
        $groupInstance = Group::getInstance($groupId);
        if($groupInstance instanceof Group != true) return null;

        // check exist
        $existClientGroupItemInstance = self::isItemWithGroupId($email, $groupInstance->getId());

        if($existClientGroupItemInstance instanceOf Item == true){

            if($existClientGroupItemInstance->getStatus() != Item::Status_Inactive){
                $groupInstance->subtractActive();
                $existClientGroupItemInstance->setStatus(Item::Status_Inactive);
                $existClientGroupItemInstance->saveChanges();
            } else {
                // 이미 비활성화
            }
        }

    }

    public static function createOrActive($groupId, $email, $clientId = 0) {

        // check groupId
        $groupInstance = Group::getInstance($groupId);
        if($groupInstance instanceof Group != true) return null;

        // check exist
        $existClientGroupItemInstance = self::isItemWithGroupId($email, $groupInstance->getId());

        if($existClientGroupItemInstance instanceOf Item == true){
            // 이미 존재함

            // 비활성이였다 활성일 경우만 active추가
            if($existClientGroupItemInstance->getStatus() != Item::Status_Active){
                $groupInstance->addActive();
                $existClientGroupItemInstance->setStatus(Item::Status_Active);
            } else {
                //throw new Exception('이미 구독중인 이메일입니다.');
            }

            if($clientId >= 1){
                $existClientGroupItemInstance->setClientId($clientId);
            }
            $existClientGroupItemInstance->setRenewDate(Util::getDbNow());
            $existClientGroupItemInstance->setRenewTimestamp(Util::getLocalTime());
            $existClientGroupItemInstance->saveChanges();


            $groupInstance->setLastUpdate(Util::getDbNow());
            $groupInstance->setLastUpdateTimestamp(Util::getLocalTime());
            $groupInstance->setLastActiveDate(Util::getDbNow());
            $groupInstance->setLastActiveTimestamp(Util::getLocalTime());
            $groupInstance->saveChanges();

            return 'active';
        } else {

            $item['groupId']        = $groupInstance->getId();
            $item['clientId']       = $clientId;
            $item['email']          = $email;
            $item['renewDate']      = Util::getDbNow();
            $item['renewTimestamp'] = Util::getLocalTime();
            $item['regDate']        = Util::getDbNow();
            $item['regTimestamp']   = Util::getLocalTime();
            $item['status']         = Item::Status_Active;

            $result = $groupInstance->getItemContainer()->addNew($item);

            //if($result == false) throw new Exception('등록에 실패했습니다. code : 1');

            if($result != false){
                $groupInstance->addActive();
                $groupInstance->addTotal();
                $groupInstance->setLastUpdate(Util::getDbNow());
                $groupInstance->setLastUpdateTimestamp(Util::getLocalTime());
                $groupInstance->setLastActiveDate(Util::getDbNow());
                $groupInstance->setLastActiveTimestamp(Util::getLocalTime());
                $groupInstance->saveChanges();

            } else {
                // 실패 또는 이미 입력되어있음.
            }

            return 'create';
        }

    }


    public static function isItemWithGroupId($client, $groupId = 0){
        $query = 'SELECT * FROM ClientGroupItem';
        if(is_numeric($client) == true){
            $query .= ' WHERE ';
            if($groupId >= 1) $query .= '`groupId` = ' . $groupId . ' AND';
            $query .= ' `clientId` = ' . $client . '';
        }if(is_string($client) == true){
            $query .= ' WHERE ';
            if($groupId >= 1) $query .= '`groupId` = ' . $groupId . ' AND';
            $query .= ' `email` = "' . addslashes(trim($client)) . '"';
        } else {
            return false;
        }
        $query .= ' LIMIT 1';
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetch();

        if(is_array($data) == true) {
            return Item::getInstance($data);
        }
        return false;
    }



    public function addNewCreateBefore($item){
        // 중복메일 체크.
        if(self::isItemWithGroupId($item['email'], $item['groupId']) != false) return false;
        return true;
    }

    public function getItemsPBF(){
        $where = array();

        if($this->getGroupId() >= 1){
            $where[] = 'groupId = ' . $this->getGroupId();
        }
        return $where;
    }

}
