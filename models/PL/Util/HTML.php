<?php
namespace PL\Models\Util;

use DOMDocument;
use DOMXPath;

class HTML
{
    protected $_dom;
    protected $_xpath;

    public function __construct($xml)
    {
        if ($xml instanceof DOMDocument) {
            $this->_dom = $xml;
        } else {
            $xml = mb_convert_encoding($xml, 'HTML-ENTITIES', "UTF-8");

            $this->_dom = new DOMDocument();
            @$this->_dom->loadHTML($xml);
        }

        $this->_xpath = new DOMXPath($this->_dom);
    }

    public function removePath($xpath)
    {
        $entries = $this->_xpath->query($xpath);
        if ($entries->length < 1) {
            return false;
        }

        foreach ($entries as $node) {
            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    public function getOne($xpath)
    {
        $entries = $this->_xpath->query($xpath);
        if ($entries->length < 1) {
            return false;
        }

        return $entries->item(0)->nodeValue;
    }

    public function getValue($xpath)
    {
        return $this->getOne($xpath);
    }

    public function getAttr($xpath, $attr)
    {
        $entries = $this->_xpath->query($xpath);
        if ($entries->length < 1) {
            return false;
        }

        return $entries->item(0)->attributes->getNamedItem($attr)->nodeValue;
    }

    /**
     * @param $xpath
     *
     * @return HTML[]
     */
    public function getRows($xpath)
    {
        $entries = $this->_xpath->query($xpath);
        if ($entries->length < 1) {
            return false;
        }

        $result = array();
        foreach ($entries as $node) {
            $dom = new DOMDocument();
            $dom->appendChild($dom->importNode($node, true));
            $result[] = new HTML($dom);
        }

        return $result;
    }
}
