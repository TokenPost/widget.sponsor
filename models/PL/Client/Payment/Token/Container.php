<?php
namespace PL\Models\Client\Payment\Token;

use PL\Models\Util\Util;
use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

class Container extends AbstractContainer {

    protected $_items;
    protected $_filter;
    protected $_page = 1;
    protected $_maxPage;
    protected $_pageSize = 10;
    protected $_listSize = 20;
    protected $_count;
    protected $_order = "`id` desc";

    protected $_client;

    public function __construct($client) {
        $this->_client = $client;
        parent::__construct();
    }


    public function getClient(){
        return $this->_client;
    }

    public function getClientId(){
        return $this->_client->getId();
    }

    public static function isToken($tokenId, $result = ''){
        $query = 'SELECT COUNT(*) FROM ClientPaymentToken';
        if(is_numeric($tokenId) == true){
            $query .= ' WHERE id = ' . $tokenId;
        } else {
            return false;
        }
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetch();

        if($data[0] == 1) {
            if($result == 'obj') return Token::getInstance($tokenId);
            return true;
        }
        return false;
    }

    public function _isToken($tokenId){
        $query = 'SELECT COUNT(*) FROM ClientPaymentToken';
        if(is_numeric($tokenId) == true){
            $query .= ' WHERE id = ' . $tokenId;
        } else {
            return false;
        }
        $data = $this->db->query($query)->fetch();
        return $data[0] > 0 ? true : false;
    }


    public function init($query) {
        $query = str_replace('*', 'count(*)', $query);

        $data = $this->db->query($query)->fetch();;
        $count = $data[0];

        if ($count > 0) {
            $this->_count   = $count;
            $this->_maxPage = ceil($this->_count / $this->_listSize);
            if ($this->_page > $this->_maxPage) $this->_page = $this->_maxPage;
        } else {
            $this->_count   = 0;
            $this->_maxPage = 1;
            $this->_page    = 1;
        }
    }

    public function setFilter($filter) {
        $this->_filter = $filter;
    }

    public function setPage($page) {
        if (is_numeric($page) === false || $page < 1) return false;
        $this->_page = $page;
    }

    public function getPage() {
        return $this->_page;
    }

    public function getMaxPage() {
        return $this->_maxPage;
    }

    public function setListSize($listSize) {
        if (is_numeric($listSize) === false) return false;
        $this->_listSize = $listSize;
    }

    public function getListSize() {
        return $this->_listSize;
    }

    public function setPageSize($pageSize) {
        if (is_numeric($pageSize) === false) return false;
        $this->_pageSize = $pageSize;
    }

    public function getPageSize() {
        return $this->_pageSize;
    }

    public function setCount($count) {
        if (is_numeric($count) === false) return false;
        $this->_count = $count;
    }

    public function getCount() {
        return $this->_count;
    }

    public function setOrder($order) {
        $this->_order = $order;
    }

    public function getOrder() {
        return $this->_order;
    }

    public function getItems($type = 0) {
        $query = 'SELECT * FROM ClientPaymentToken';

        $where = array();
        $bind  = array();

        if (isset($this->_filter)) {
            foreach ($this->_filter->getItems() as $filter) {
                $where[] = $filter->getFieldName() . ' ' . $filter->getCondition() . ' ' . $filter->getValue();
            }
        }

        if (sizeof($where) > 0) {
            $query .= ' WHERE ' . join(' AND ', $where);
        }

        $this->init($query);

        $limitStart = (($this->_page - 1) * $this->_listSize);
        $limitEnd   = $this->_listSize;

        $query .= ' ORDER BY ' . $this->_order;
        $query .= " LIMIT " . $limitStart . " , " . $limitEnd;

        $result = $this->db->query($query, $bind)->fetchAll();

        if ($result === false) return array();

        $return = array();
        foreach ($result as $var) {
            $return[] = Token::getInstance($var);
        }

        $this->_items = $return;
        return $return;
    }

    public function addNew($item) {
        /* 글 등록 */
        $result = $this->db->insert('ClientPaymentToken', array_values($item), array_keys($item));

        if($result == false) return false;
        return $this->db->lastInsertId();
    }


    public function createToken(){
        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('clientId', '=', $this->getClientId()));
        $filterContainer->add(new Filter('status', '=', Token::Status_Standby));

        $this->setFilter($filterContainer);
        $result = $this->getItems();

        foreach($result as $var){
            $var->setStatus(Token::Status_Expired);
            $var->saveChanges();
        }


        $item['clientId']  = $this->getClientId();
        $item['token']     = substr(md5(array_shift(explode(' ', microtime()))), -10);
        $item['tx']        = '';
        $item['regIp']     = getIP();
        $item['regDate']   = Util::getDbNow();
        $item['status']    = Token::Status_Standby;

        return Token::getInstance($this->addNew($item));
    }

    public function checkToken($id, $token) {
        $filterContainer = new FilterContainer();
        $filterContainer->add(new Filter('id', '=' ,$id));
        $filterContainer->add(new Filter('token', '=' , '"' . addslashes($token) . '"'));
        $filterContainer->add(new Filter('status', '=' , 1));

        $this->setFilter($filterContainer);
        $result = $this->getItems();
        if(sizeof($result) != 1) return false;

        return $result[0];
    }
}
