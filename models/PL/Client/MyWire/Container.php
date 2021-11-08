<?php
namespace PL\Models\Client\MyWire;

use PL\Models\Client\Client;
use Exception;
use PL\Models\News\Article\Article;
use PL\Models\News\News;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    protected $_clientId;
    protected $_clientInstance;

    public function __construct(Client $clientInstance = null) {

        if(is_null($clientInstance) != true){
            $this->setClientInstance($clientInstance);
        }
        parent::__construct('ClientMyWire');
        $this->setTableName('ClientMyWire');
    }

    public static function getTableNameStatic(){
        return false;
    }

    public static function getObjectInstanceStatic($date) : static {
        return false;
    }

    public function getObjectInstance($date) : self {
        return false;
    }


    public function getClientId(){
        return $this->_clientId;
    }

    private function setClientId($clientId){
        $this->_clientId = $clientId;
    }

    public function getClientInstance(){
        return $this->_clientInstance;
    }

    public function setClientInstance(Client $clientInstance){
        $this->_clientInstance = $clientInstance;
        $this->setClientId($clientInstance->getId());
    }


    public function addFavorites($articleId){
        //$db     = DI::getDefault()->getShared('db');
        $result = $this->db->query('SELECT (*) FROM ClientMyWire WHERE clientId = ? and ArticleId = ?', array($this->getClientId(), $articleId));
        $data = $result->fetch();
        if($data[0] >= 1){
            // 이미 존재 삭제처리.
            $data = $this->db->query('DELETE FROM ClientMyWire WHERE clientId = ? and ArticleId = ?', array($this->getClientId(), $articleId));
            if($data == true){
                return 0;
            } else {
                return 2;
            }
        } else {
            // 미존재 favorite 추가.
            $result = $this->db->insert('ClientMyWire', array($this->getClientId(), $articleId), array('clientId', 'articleId'));
            if ($result == true) {
                return 1;
            } else {
                return 3;
            }
        }

    }


    //?
    public function getItemsPBF() {
        $where = array();

        if (is_numeric($this->getClientId()) == true) {
            $where[] = 'clientId = ' . $this->getClientId();
        }

        return $where;
    }

    public function getItems($type = 0) {
        $query = 'SELECT * FROM ClientMyWire LEFT JOIN NewsArticle ON ClientMyWire.newsId = NewsArticle.id';
        $query .= ' WHERE ClientFavorites.clientId = ?';

        $limitStart = (($this->_page - 1) * $this->_listSize);
        $limitEnd   = $this->_listSize;

        $query .= " ORDER BY `articleId` desc";
        $query .= " LIMIT " . $limitStart . " , " . $limitEnd;

        $result = $this->db->query($query,$this->getClientId())->fetchAll();

        if ($result === false) return array();

        $return = array();
        foreach ($result as $var) {
            $return[] = Article::getInstance($var);
        }
        $this->_items = $return;
        return $return;
    }

}
