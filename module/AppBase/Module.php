<?php

namespace AppBase;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterServiceFactory;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module implements AutoloaderProviderInterface
{
    
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $serviceManager      = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('AppBaseSessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
             $session->regenerateId(true);
             $container->init = 1;
        }
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getServiceConfig()
    {
		return array('factories' => array(
            'DbAdapter' => function ($serviceManager) {
                $adapterFactory = new \Zend\Db\Adapter\AdapterServiceFactory();
                $adapter = $adapterFactory->createService($serviceManager);
                \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);

                return $adapter;
            },
            'AppBaseSaveHandler' => function ($serviceManager) {
                $tableGateway = new TableGateway('session', $serviceManager->get('DbAdapter'));
                $saveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());

                return $saveHandler;
            },
            'AppBaseSessionManager' => function ($serviceManager) {
                $config = $serviceManager->get('config');
                if (isset($config['session'])) {
                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                        $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        $sessionSaveHandler = $serviceManager->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                    if (isset($session['validator'])) {
                        $chain = $sessionManager->getValidatorChain();
                        foreach ($session['validator'] as $validator) {
                            $validator = new $validator();
                            $chain->attach('session.validate', array($validator, 'isValid'));

                        }
                    }
                } else {
                    $sessionManager = new SessionManager();
                }
                Container::setDefaultManager($sessionManager);
                
                return $sessionManager;
            },
            'ScriptRenderer' => function ($serviceManager) {
				$config = $serviceManager->get('config');
                $renderer = new \AppBase\View\Renderer\ScriptRenderer();
				$resolver = new \AppBase\View\Resolver\ScriptPathStack();
				$resolver->addPaths($config['view_manager']['script_path_stack']);
				$renderer->setResolver($resolver);

                return $renderer;
            },
            'AppCache' => function ($serviceManager) {
                $config = $serviceManager->get('config');
                $cache = StorageFactory::factory(array(
                    'adapter' => array(
                        'name'    => $config['cache_configuration']['adaptor'],
                        'options' => $config['cache_configuration']['options'],
                    ),
                    'plugins' => array(
                        'exception_handler' => array(
                            'throw_exceptions' => getenv('APP_ENV') == 'production' ? false : true
                        )
                    )
                ));

                return $cache;
            }
        ));
    }
}