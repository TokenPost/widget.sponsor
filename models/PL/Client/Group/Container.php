<?php
namespace PL\Models\Client\Group;

use Exception;
use Phalcon\Db;
use Phalcon\DI;
use PL\Models\Adapter\AbstractContainer;
use PL\Models\Util\Util;

class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Group::tableName);
        $this->setTableName(Group::tableName);
    }

    public static function getTableNameStatic(){
        return Group::tableName;
    }

    public static function getObjectInstanceStatic($date) : Group {
        return Group::getInstance($date);
    }

    public function getObjectInstance($date) : Group {
        return Group::getInstance($date);
    }

}