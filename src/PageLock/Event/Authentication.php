<?php
/**
 * Here, you can handle the authenticate and authorize of the site user
 * 
 * @author Tarun Singhal
 * @date 26 May, 2014
 */
namespace PageLock\Event;
use Zend\Mvc\MvcEvent;

class Authentication
{

    /**
     * preDispatch Event Handler
     *
     * @param \Zend\Mvc\MvcEvent $event            
     * @throws \Exception
     */
    public function preDispatch(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        
        $userId = 1; //To maintain the lock on basis of logged-in userId
         
        $pageAccess = false;
        $pageAccessObj = new \PageLock\Controller\PageAccessController($event, $userId);
        $pageAccess = $pageAccessObj->isPageAccessed();
        
        $viewModel = $event->getViewModel();
        $viewModel->setVariable('isPageAccessed', $pageAccess);
    }
    
} //End of class