<?php
namespace PL\Models\Referral;

use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;

class Container extends AbstractContainer {

    public function __construct() {
        parent::__construct(Referral::tableName);
        $this->setTableName(Referral::tableName);
        $this->setOrder('`id` ASC, ');
    }

    public static function getTableNameStatic(){
        return Referral::tableName;
    }

    public static function getObjectInstanceStatic($data) : Referral {
        return Referral::getInstance($data);
    }

    public function getObjectInstance($data) : Referral {
        return Referral::getInstance($data);
    }

}
