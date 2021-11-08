<?php
namespace PL\Models\Filter;

use Exception;
use Phalcon\DI;

class Filter {
    protected $_fieldName;
    protected $_condition;
    protected $_value;

    public function __construct($fieldName = '', $condition = '', $value = '') {
        if ($fieldName != '') {
            $this->setFieldName($fieldName);
        }

        if ($condition != '') {
            $this->setCondition($condition);
        }

        if ($value != '') {
            $this->setValue($value);
        }
    }

    public function setFieldName($fieldName) {
        $this->_fieldName = $fieldName;
    }

    public function setCondition($condition) {
        $this->_condition = $condition;
    }

    public function setValue($value) {
        $this->_value = $value;
    }

    public function getFieldName() {
        return $this->_fieldName;
    }

    public function getCondition() {
        switch (strtolower($this->_condition)) {
            case 'like-l':
            case 'like-r':
                return 'LIKE';
                break;
            default:
                return $this->_condition;
                break;
        }
    }

    public function getValue() {
        /**
         * Like일경우 % 자동으로 붙여준다.
         * -L  -R  옵션을 추가해  좌측, 우측용으로 사용.
         */
        switch (strtolower($this->_condition)) {
            case 'like':
                return '"%' . $this->_value . '%"';
                break;
            case 'like-l':
                return '"%' . $this->_value . '"';
                break;
            case 'like-r':
                return '"' . $this->_value . '%"';
                break;
            default:
                return $this->_value;
                break;
        }
    }
}