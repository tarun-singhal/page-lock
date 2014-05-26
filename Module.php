<?php
/**
 * Manage the module routing from bootstrap 
 * @author Tarun Singhal
 * @date 26 May, 2014
 */
namespace PageLock;

use PageLock\Event\Authentication;
use PageLock\Model\PageAccessModel;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;


class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
        		$this,
        		'mvcPreDispatch'
        ), 101);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
   
    public function getAutoloaderConfig()
    {
    	return array(
    			'Zend\Loader\StandardAutoloader' => array(
    					'namespaces' => array(
    							__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
    					)
    			)
    	);
    }

    /**
     * @desc : to move the controller for the authentication
     * Here, you can include your ACL related credentials
     * 
     * @param MvcEvent $event
     */
    public function mvcPreDispatch(MvcEvent $event)
    {
    	$auth = $event->getApplication()
				    	->getServiceManager()
				    	->get('PageLock\Event\Authentication');
    	return $auth->preDispatch($event);
    }
    
    /**
     * Get service configuration
     *
     * @access public
     * @return array
     */
    public function getServiceConfig()
    {
    	return array(
    		'factories' => array(
				'PageLock\Event\Authentication' => function ($serviceManager)
    			{
    				return new Authentication();
    			},
    			'PageLock\Model\PageAccessModel' => function ($serviceManager)
    			{
    				return new PageAccessModel($serviceManager->get('Zend\Db\Adapter\Adapter'));
    			}
   			)
    	);
    }
    
} //End of class