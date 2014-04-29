<?php
namespace AppBase\View\Model;

use Zend\View\Model\ViewModel as ZendViewModel;

class ScriptModel extends ZendViewModel
{
	protected $scripts = array();
	
	public function addScript($file) {
		$this->scripts[] = $file;
	}
	
	public function getScripts() {
		return $this->scripts;
	}
}
?>