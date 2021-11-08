<?php
namespace PL\Models\Site\Item\Widget\Type;
use Exception;
use Phalcon\Db;
use Phalcon\DI;

use PL\Models\Adapter\AbstractContainer;


use PL\Models\Util\Util;

use PL\Models\Site\Item\Widget\Widget;

use PL\Models\Filter\Filter;
use PL\Models\Filter\Container as FilterContainer;


class Container extends AbstractContainer {

    protected $_widgetInstance;
    protected $_widgetId;

    protected $_typeId;

    public function __construct(Widget $widgetInstance = null) {
        parent::__construct(Type::tableName);
        $this->setTableName(Type::tableName);

        if(is_null($widgetInstance) == false) {
            $this->setWidgetInstance($widgetInstance);
        }
    }

    public static function getTableNameStatic(){
        return Type::tableName;
    }

    public static function getObjectInstanceStatic($data) : Type {
        return Type::getInstance($data);
    }

    public function getObjectInstance($data) : Type {
        return Type::getInstance($data);
    }

    public function getWidgetInstance(){
        return $this->_widgetInstance;
    }

    public function setWidgetInstance(Widget $widgetInstance){
        $this->_widgetInstance = $widgetInstance;
        $this->setWidgetId($widgetInstance->getId());
    }

    public function getWidgetId(){
        return $this->_widgetId;
    }

    public function setWidgetId($widgetId) {
        $this->_widgetId = $widgetId;
    }



    public function getWidgetsPBF(){
        $where = array();
        if(Util::isInteger($this->getWidgetId()) == true) $where[] = '`widgetId` = "' . $this->getWidgetId() . '"';
        return $where;
    }
}