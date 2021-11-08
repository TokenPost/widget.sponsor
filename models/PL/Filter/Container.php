<?php
namespace PL\Models\Filter;

use Exception;
use PL\Models\Adapter\AbstractContainer;

class Container extends AbstractContainer {
    public $_items = Array();

    public function __construct(Filter $filter = null) {
        parent::__construct();
        if (is_null($filter) === false) {
            $this->add($filter);
        }
    }

    public static function getTableNameStatic(){
        return '';
    }

    public static function getObjectInstanceStatic($date) {
        return '';
    }

    public function getObjectInstance($date) {
        return '';
    }


    public function add(Filter $filter) {
        $this->_items[] = $filter;
    }

    public function getItems() {
        return $this->_items;
    }

    public function clear() {
        $this->_items = array();
    }
}
