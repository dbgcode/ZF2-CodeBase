<?php
namespace AppBase\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Filter\AbstractFilter;

class Xss extends AbstractFilter
{
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
    }
	
    public function filter($value)
    {
		$escaper = new \Zend\Escaper\Escaper('utf-8');
		$filtered = $escaper->escapeJs($value);
		$filtered = $escaper->escapeCss($filtered);
		$filtered = $escaper->escapeUrl($filtered);
		return $filtered;
    }
}