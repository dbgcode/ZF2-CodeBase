<?php
namespace AppBase\View\Renderer;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;
use Zend\View\Renderer\TreeRendererInterface;
use AppBase\View\Model\ScriptModel;

class ScriptRenderer implements Renderer, TreeRendererInterface
{

    protected $resolver;
	protected $script;
	
	public function getEngine()
    {
        return $this;
    }
	
	public function getResolver()
    {
        return $this->resolver;
    }

    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render($nameOrModel, $values = null)
    {
		if ($nameOrModel instanceof Model) {
			
			foreach($nameOrModel->getScripts() as $script) {
				$path = $this->resolver->resolve($script);
				$this->script .= file_get_contents($path);
			}
        }
		return $this->script;
    }
	
	public function canRenderTrees()
    {
        return true;
    }
}
