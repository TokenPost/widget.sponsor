<?php
namespace PL\Models\Digital;

use Exception;
use Phalcon\DI;
use Phalcon\Db;

use PL\Models\Adapter\AbstractContainer;
use PL\Models\Adapter\AbstractSingleton;

use PL\Models\Util\Util;

class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Digital::tableName);
        $this->setTableName(Digital::tableName);
    }

    public static function getTableNameStatic(){
        return Digital::tableName;
    }

    public static function getObjectInstanceStatic($date) : Digital {
        return Digital::getInstance($date);
    }

    public function getObjectInstance($date) : Digital {
        return Digital::getInstance($date);
    }


}