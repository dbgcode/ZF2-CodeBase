<?php
namespace AppBase\Filter\File;

use Traversable;
use Zend\Filter\FilterInterface as ZendFilterInterface;
use Zend\Filter\Exception;
use Zend\Stdlib\ArrayUtils;

use AppBase\Filter\File\Adapter\Gd;

class Resize implements ZendFilterInterface
{
    protected $_width = null;
    protected $_height = null;
    protected $_keepRatio = true;
    protected $_keepSmaller = true;
	protected $_target = null;
    protected $_adapter = '\AppBase\Filter\File\Resize\Adapter\Gd';
 
    /**
     * Create a new resize filter with the given options
     *
     * @param Zend_Config|array $options Some options. You may specify: width, 
     * height, keepRatio, keepSmaller (do not resize image if it is smaller than
     * expected), directory (save thumbnail to another directory),
     * adapter (the name or an instance of the desired adapter)
     * @return Skoch_Filter_File_Resize An instance of this filter
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Invalid options argument provided to filter');
        }
 
        if (!isset($options['width']) && !isset($options['height'])) {
            throw new Exception\InvalidArgumentException('At least one of width or height must be defined');
        }
		
		if (!isset($options['target'])) {
            throw new Exception\InvalidArgumentException('Please set the target parameter');
        }
 
        if (isset($options['width'])) {
            $this->_width = $options['width'];
        }
        if (isset($options['height'])) {
            $this->_height = $options['height'];
        }
        if (isset($options['keepRatio'])) {
            $this->_keepRatio = $options['keepRatio'];
        }
        if (isset($options['keepSmaller'])) {
            $this->_keepSmaller = $options['keepSmaller'];
        }
		
        if (isset($options['target'])) {
            $this->_target = $options['target'];
        }
		
        if (isset($options['adapter'])) {
            if ($options['adapter'] instanceof AbstractAdapter) {
                $this->_adapter = $options['adapter'];
            } else {
                $this->_adapter = $options['adapter'];
            }
        }
 
        $this->_prepareAdapter();
    }
 
    /**
     * Instantiate the adapter if it is not already an instance
     *
     * @return void
     */
    protected function _prepareAdapter()
    {
        if ($this->_adapter instanceof AbstractAdapter) {
            return;
        } else {
            $this->_adapter = new $this->_adapter();
        }
    }
 
    /**
     * Defined by Zend\Filter\Filter
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     * @throws Exception\RuntimeException
     * @return string|array The new filename which has been set
     */
    public function filter($value)
    {
        return $this->_adapter->resize($this->_width, $this->_height,
            $this->_keepRatio, $value, $this->_target, $this->_keepSmaller);
    }
}
