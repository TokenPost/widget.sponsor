<?php
namespace PL\Models\Client\Api\Log;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;

use PL\Models\Client\Api\Api;
use PL\Models\Client\Api\Log\Log;
use PL\Models\Util\Util;
use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;

class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Log::tableName);
        $this->setTableName(Log::tableName);
    }

    public static function getTableNameStatic(){
        return Log::tableName;
    }

    public static function getObjectInstanceStatic($date) : Log {
        return Log::getInstance($date);
    }

    public function getObjectInstance($date) : Log {
        return Log::getInstance($date);
    }


    public static function isLog($id, $result = ''){
        $query = 'SELECT * FROM ClientApiLog';
        if(is_numeric($id) == true){
            $query .= ' WHERE id = ' . $id;
        } else {
            return false;
        }
        $db     = DI::getDefault()->getShared('db');
        $data = $db->query($query)->fetchAll();

        if(sizeof($data) == 1) {
            if($result == 'obj') return Log::getInstance($data[0]);
            return true;
        }
        return false;
    }

}
