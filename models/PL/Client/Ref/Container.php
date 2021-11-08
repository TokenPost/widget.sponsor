<?php
namespace PL\Models\Client\Ref;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Client\Client;
use PL\Models\Client\Ref\Type\Container as ClientRefTypeContainer;
use PL\Models\Client\Ref\Type\Value\Container as ClientRefTypeValueContainer;

class Container extends AbstractContainer {

    protected $_sortedItems;

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {
        parent::__construct(Ref::tableName);
        $this->setTableName(Ref::tableName);
        $this->setOrder('`id` asc, ');

        if(is_null($clientInstance) == false){
            $this->setClientInstance($clientInstance);
            $this->setClientId($clientInstance->getId());
        }
    }

    public static function getTableNameStatic(){
        return Ref::tableName;
    }

    public static function getObjectInstanceStatic($date) : Ref {
        return Ref::getInstance($date);
    }

    public function getObjectInstance($date) : Ref {
        return Ref::getInstance($date);
    }


    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance($clientInstance){
        $this->_clientInstance = $clientInstance;
    }


    public function getClientId(){
        return $this->_clientId;
    }

    public function setClientId($clientId){
        $this->_clientId = $clientId;
    }



    public function calcRef($clientRef){
        // ref 생성,수정, 삭제
        // client 에는 값이 없으므로 아예 배열로 받는다.

        //$clientRef = $this->getClientInstance()->getAllRef();
        $clientRefTypeContainer = new ClientRefTypeContainer();
        $typeList = $clientRefTypeContainer->getArrayItems();
        $refItems = $this->getItems();

        $temp = array();
        $clientRefArray = array();
        foreach($typeList as $key => $var){
            if(isset($clientRef[$var]) == true && trim($clientRef[$var]) != ''){
                if(strpos($clientRef[$var], ',') == true){
                    $ex = explode(',', $clientRef[$var]);
                    foreach($ex as $var2){
                        //foreach(array_reverse($ex) as $var2){
                        if(trim($var2) != ''){
                            $temp['key'] = $key;
                            $temp['value'] = trim($var2);
                            $clientRefArray[] = $temp;
                        }
                    }
                } else {
                    $temp['key'] = $key;
                    $temp['value'] = trim($clientRef[$var]);
                    $clientRefArray[] = $temp;
                }
            }

            if(isset($clientRef[$key]) == true && trim($clientRef[$key]) != ''){
                if(strpos($clientRef[$key], ',') == true){
                    $ex = explode(',', $clientRef[$key]);
                    foreach($ex as $var2){
                        //foreach(array_reverse($ex) as $var2){
                        if(trim($var2) != ''){
                            $temp['key'] = $key;
                            $temp['value'] = trim($var2);
                            $clientRefArray[] = $temp;
                        }
                    }
                } else {
                    $temp['key'] = $key;
                    $temp['value'] = trim($clientRef[$key]);
                    $clientRefArray[] = $temp;
                }
            }
        }

        foreach($refItems as $key => $var){
            if(isset($clientRefArray[$key]) == true){
                $var->modify($clientRefArray[$key]);
            } else {
                $var->delete();
            }
        }

        if(sizeof($clientRefArray) > sizeof($refItems)){
            for($i = sizeof($refItems); $i < sizeof($clientRefArray) ; $i++){
                $newRef['clientId'] = $this->getClientId();
                $newRef['typeId'] = $clientRefArray[$i]['key'];
                $newRef['value'] = $clientRefArray[$i]['value'];
                $this->addNew($newRef);
            }
        }
    }


    public function getSortedItems() {
        if(isset($this->_sortedItems) != true){
            $this->getItems();
        }
        return $this->_sortedItems;
    }

    public function getClientIdList() {
        $query = 'SELECT * FROM ' . $this->getTableName();

        $where = array();
        if (isset($this->_filter)) {
            foreach ($this->_filter->getItems() as $filter) {
                $where[] = $filter->getFieldName() . ' ' . $filter->getCondition() . ' ' . $filter->getValue();
            }
        }

        if (sizeof(array_filter($where)) > 0) {
            $query .= ' WHERE `clientId` = ' . $this->getClientId() . ' AND ' .join(' OR ', array_filter($where));
        }

        //$query .= " GROUP BY `client_id` ";

        $this->setQuery($query);
        $this->init();

        $limitStart = (($this->_page - 1) * $this->_listSize);
        $limitEnd   = $this->_listSize;

        $query .= " ORDER BY " . $this->_order . " `clientId` desc";
        $query .= " LIMIT " . $limitStart . " , " . $limitEnd;

        $result = $this->db->query($query)->fetchAll();

        if ($result === false) return array();

        $return = array();
        $sortedItems = array();

        foreach ($result as $var) {
            $temp = Ref::getInstance($var);
            $return[] = $temp;
            $sortedItems[$temp->getTypeId()][] = $temp;
        }

        $this->_items = $return;
        $this->_sortedItems = $sortedItems;
        return $return;
    }

    public function getItems($type = 0) {
        $query = 'SELECT * FROM '. $this->getTableName();

        $where = array();
        if (isset($this->_filter)) {
            foreach ($this->_filter->getItems() as $filter) {
                $where[] = $filter->getFieldName() . ' ' . $filter->getCondition() . ' ' . $filter->getValue();
            }
        }

        if($this->getClientId() >= 1){
            $where[] = '`clientId` = ' . $this->getClientId();
        }

        if (sizeof(array_filter($where)) > 0) {
            $query .= ' WHERE ' . join(' AND ', array_filter($where));
        }

        $this->setQuery($query);
        $this->init();

        $limitStart = (($this->_page - 1) * $this->_listSize);
        $limitEnd   = $this->_listSize;

        $query .= " ORDER BY " . $this->_order . " `id` asc";
        $query .= " LIMIT " . $limitStart . " , " . $limitEnd;

        $result = $this->db->query($query)->fetchAll();

        if ($result === false) return array();

        $return = array();
        $sortedItems = array();

        foreach ($result as $var) {
            $temp = Ref::getInstance($var);
            $return[] = $temp;
            $sortedItems[$temp->getTypeId()][] = $temp;
        }

        $this->_items = $return;
        $this->_sortedItems = $sortedItems;
        return $return;
    }

    public function getRef($type, $result = 'array'){
        $return = array();
        $allItem = $this->getSortedItems();

        if(is_numeric($type) != true){
            $typeObj = ClientRefTypeContainer::isItem($type, 'title');
            if($typeObj){
                $type = $typeObj->getId();
            } else {
                if($result == 'join' || $result == 'display') return '';
                return $return;
            }
        }

        if(isset($allItem[$type]) != true || sizeof($allItem[$type]) < 1){
            if($result == 'join' || $result == 'display') return '';
            return $return;
        }

        foreach($allItem[$type] as $var){
            $return[] = (int)$var->getValue();
        }

        if($result == 'first'){
            if(sizeof($return) >= 1){
                return $return[0];
            } else {
                return '';
            }
        }elseif($result == 'join'){
            return join(',', $return);
        }elseif($result == 'display'){
            return join(', ', $return);
        }
        return $return;
    }

    public function getRefTitle($type, $result = 'array', $languageId = 1){
        $glue = ',';
        $return = array();
        $ref = $this->getRef($type);


        if(sizeof($ref) >= 1){
            foreach($ref as $var){
                switch ($type){
                    case 1:
                    case 'interestCategory':
                        $return[] = ClientRefTypeValueContainer::getValueTranslate(1, $var, $languageId);
                        break;
                    case 2:
                    case 'interest':
                        $glue = '|';
                        $return[] = ClientRefTypeValueContainer::getValueTranslate(2, $var, $languageId);
                        break;
                    case 3:
                    case 'job':
                        $glue = '|';
                        $return[] = ClientRefTypeValueContainer::getValueTranslate(3, $var, $languageId);
                        break;
                    default:
                        $return[] = $var;
                        break;
                }
            }

        }

        if($result == 'first' || $result == 'one'){
            if(sizeof($return) >= 1){
                return $return[0];
            } else {
                return '';
            }
        }

        if($result == 'join') return join($glue, $return);
        if($result == 'display') return join(', ', $return);
        return $return;
    }


}