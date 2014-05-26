<?php
/**
 * @desc: This is used to handle the Page access Lock for the user.
 * 
 * User can not access the same page at same time irrespective of the browser. 
 *  
 * @author Tarun Singhal
 * @date 26 May, 2014
 */
namespace PageLock\Controller;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Mvc\Controller\AbstractActionController;
use PageLock\Model\PageAccessModel;


class PageAccessController extends AbstractActionController {
	
	protected $serviceManager;

    protected $event;

    protected $userId;

    /**
     * Constructor
     * @param unknown $event
     * @param unknown $userId
     */
	public function __construct($event, $userId)
    {
        $this->event = $event;
        $this->serviceManager = $event->getApplication()->getServiceManager();
        $this->userId = $userId;
    }
    
    /**
     * get the id of logged in user
     */
    public function getUserId()
    {
    	return $this->userId;
    }
    
    /**
     * get the instance of service manager
     */
    public function getServiceLocator()
    {
    	return $this->serviceManager;
    }
    
    /**
     * Get the event
     */
    public function getEvent()
    {
    	return $this->event;
    }
    
    /**
     * check particular route is accessed by someone or not
     *
     * @return boolean
     */
    public function isPageAccessed()
    {
    	$config = $this->serviceManager->get('config');
    	$timeDiff = $config['PageLock']['PAGE_LOCK']; // time in second
    	$userAgent = $_SERVER['HTTP_USER_AGENT'];
    	$ipAddress = $_SERVER['REMOTE_ADDR'];
    	$routeMatch = $this->getEvent()->getRouteMatch();
    	$controller = $routeMatch->getParam('controller');
    	$action = $routeMatch->getParam('action');
    	$route = $controller . '\\' . $action;

    	$pageAccessModel = $this->getServiceLocator()->get('PageLock\Model\PageAccessModel');
    	
    	$details = $pageAccessModel->getPageDetails(array(
    			'user_id' => $this->userId,
    			'route' => $route
    	));
    	if (! empty($details) && (($details['user_agent'] != $userAgent) || ($details['ip_address'] != $ipAddress))) {
    		if ($timeDiff >= $details['timediff']) {
    			return true;
    		} else {
    			$data = array(
    					'user_id' => $this->userId,
    					'route' => $route,
    					'user_agent' => $userAgent,
    					'ip_address' => $ipAddress,
    					'last_access' => date("Y-m-d H:i:s")
    			);
    			$where = array(
    					'user_id' => $this->userId,
    					'route' => $route
    			);
    			$pageAccessModel->updateData($data, $where);
    			return false;
    		}
    	} elseif (! empty($details) && (($details['user_agent'] == $userAgent) && ($details['ip_address'] == $ipAddress))) {
    		$data = array(
    				'user_id' => $this->userId,
    				'route' => $route,
    				'user_agent' => $userAgent,
    				'ip_address' => $ipAddress,
    				'last_access' => date("Y-m-d H:i:s")
    		);
    		$where = array(
    				'user_id' => $this->userId,
    				'user_agent' => $userAgent,
    				'ip_address' => $ipAddress,
    				'route' => $route
    		);
    		$pageAccessModel->updateData($data, $where);
    		return false;
    	} else {
    		$data = array(
    				'user_id' => $this->userId,
    				'route' => $route,
    				'user_agent' => $userAgent,
    				'ip_address' => $ipAddress,
    				'last_access' => date("Y-m-d H:i:s")
    		);
    
    		$userPagedetails = $pageAccessModel->getPageDetails(array(
    				'user_id' => $this->userId,
    				'user_agent' => $userAgent,
    				'ip_address' => $ipAddress
    		));
    		if (empty($userPagedetails)) {
    			$pageAccessModel->insertData($data);
    		} else {
    			$where = array(
    					'user_id' => $this->userId,
    					'user_agent' => $userAgent,
    					'ip_address' => $ipAddress
    			);
    			$pageAccessModel->deleteData($where);
    			$pageAccessModel->insertData($data);
    		}
    		return false;
    	}
    }

} //End of class