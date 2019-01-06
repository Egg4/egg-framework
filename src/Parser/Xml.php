<?php

namespace Egg\Parser;

class Xml extends AbstractParser
{
    public function __construct(array $settings = array())
    {
        if (!class_exists('\SimpleXMLElement')) {
            throw new \Exception('Class "SimpleXMLElement" not found');
        }
    }

    public function parse($string)
    {
        $backup = libxml_disable_entity_loader(true);
        $simpleXMLElement = new \SimpleXMLElement($string);
        $array = $this->xmlToArray($simpleXMLElement);
        libxml_disable_entity_loader($backup);

        return $array;
    }

    protected function xmlToArray($simpleXMLElement)
    {
        $array = [];
        foreach ($simpleXMLElement->children() as $node) {
            if(count($node->children()) == 0) {
                $array[$node->getName()] = strval($node);
            }
            else {
                $array[$node->getName()][] = $this->xmlToArray($node);
            }
        }

        return $array;
    }
}