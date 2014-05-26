<?php
/**
 * Management for the page access model layer
 * @author Tarun Singhal
 * @date 26 May, 2014
 */
namespace PageLock\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Having;

class PageAccessModel extends AbstractTableGateway implements ServiceLocatorAwareInterface
{

    protected $_serviceLocator;

    public $table = 'page_access';

    protected $adapter;

    /**
     * Constructor
     *
     * @access pubic
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
    	$this->adapter = $adapter;
    	$this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
    	$this->initialize();
    }
    
    /**
     * Set $_serviceLocator
     *
     * @access pubic
     * @param ServiceLocatorInterface $serviceLocator
     *            // ServiceLocatorInterface instance
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    /**
     * Get $_serviceLocator
     *
     * @access pubic
     * @return \Zend\ServiceManager\ServiceLocatorAwareInterface
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    /**
     * Get Resource Permission on the respective role
     * @param <array> $where
     * @throws Exception
     * @return array
     */
    public function getPageDetails($where = array())
    {
        try {
            $sql = new Sql($this->adapter);
            $select = $sql->select()->from(array(
                'pa' => $this->table
            ));
            
            $select->columns(array(
                'id',
                'route',
                'user_agent',
                'ip_address',
                'user_id',
                'last_access',
                'timediff' => new \Zend\Db\Sql\Expression("(TIME_TO_SEC(TIMEDIFF(NOW(), last_access)))")
            ));
            
            if (! empty($where)) {
                $select->where($where);
            }
            $statement = $sql->prepareStatementForSqlObject($select);
            $resources = $this->resultSetPrototype->initialize($statement->execute())
                ->toArray();
            if (! empty($resources)) {
                return $resources[0];
            }
            return $resources;
        } catch (\Exception $err) {
            throw $err;
        }
    }

    /**
     * Insert page access entry in table
     * @param array $pageDetails
     * @throws \Exception
     */
    public function insertData($pageDetails)
    {
        try {
            $sql = new Sql($this->getAdapter());
            $insert = $sql->insert($this->table);
            $insert->values($pageDetails);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

    /**
     * update page access records
     * @param array $pageDetails
     * @param array $where
     * @throws \Exception
     * @return bool
     */
    public function updateData($pageDetails, $where = array())
    {
        try {
            $sql = new Sql($this->getAdapter());
            $update = $sql->update($this->table);
            $update->set($pageDetails);
            if (! empty($where)) {
                $update->where($where);
            }
            $statement = $sql->prepareStatementForSqlObject($update);
            // prx($sql->getSqlStringForSqlObject($update));
            $result = $statement->execute();
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

	/**
	 * delete the data
	 * @param array $where
	 * @throws \Exception
	 * @return bool
	 */    
    public function deleteData($where)
    {
        try {
            $sql = new Sql($this->getAdapter());
            $delete = $sql->delete($this->table);
            $delete->where($where);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result = $statement->execute();
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
} //End of class