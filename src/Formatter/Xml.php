<?php

namespace Egg\Formatter;

class Xml extends AbstractFormatter
{
    public function __construct(array $settings = array())
    {
        if (!class_exists('\SimpleXMLElement')) {
            throw new \Exception('Class "SimpleXMLElement" not found');
        }

        $this->settings = array_merge(array(
            'root' => 'root',
            'item' => 'item_%d',
        ), $settings);
    }

    public function format(array $array)
    {
        $backup = libxml_disable_entity_loader(true);
        $xml = sprintf('<?xml version="1.0"?><%s></%s>', $this->settings['root'], $this->settings['root']);
        $simpleXMLElement = new \SimpleXMLElement($xml);
        $this->arrayToXml($array, $simpleXMLElement);
        $string = $simpleXMLElement->asXML();
        libxml_disable_entity_loader($backup);

        return $string;
    }

    protected function arrayToXml($array, $simpleXMLElement)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $key = is_numeric($key) ? sprintf($this->settings['item'], $key) : $key;
                $node = $simpleXMLElement->addChild($key);
                $this->arrayToXml($value, $node);
            } else {
                $simpleXMLElement->addChild($key, htmlspecialchars($value));
            }
        }
    }
}