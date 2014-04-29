<?php
namespace AppBase\Validator\Image;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
/**
 * Validator for the file extension of a file
 */
class Dimension extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const WIDTH_MIN = 'widthMin';
	const HEIGHT_MIN = 'heightMin';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::WIDTH_MIN => "The image need to be of at least %minWidth%x%minHeight%px.",
		self::HEIGHT_MIN => "The image need to be of at least %minWidth%x%minHeight%px.",
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'minWidth' => '',
		'minHeight' => '',
    );

    /**
     * @var array Error message template variables
     */
    protected $messageVariables = array(
        'minWidth' => array('options' => 'minWidth'),
		'minHeight' => array('options' => 'minHeight'),
    );

    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        parent::__construct($options);
    }
	
	public function getMinHeight()
    {
        $minHeight = $this->options['minHeight'];
        
        return $minHeight;
    }
	
	public function getMinWidth()
    {
        $minWidth = $this->options['minWidth'];
        
        return $minWidth;
    }

    public function isValid($value, $file = null)
    {
        if (is_string($value) && is_array($file)) {
            // Legacy Zend\Transfer API support
            $filename = $file['name'];
            $file     = $file['tmp_name'];
        } elseif (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
        } else {
            $file     = $value;
            $filename = basename($file);
        }
        $this->setValue($filename);
		list($width, $height) = getimagesize($file);
		
		if($width < $this->getMinWidth()):
			$this->error(self::WIDTH_MIN);
			return false;
		endif;
		
		if($height < $this->getMinHeight()):
			$this->error(self::HEIGHT_MIN);
			return false;
		endif;
        
		return true;
    }
}
