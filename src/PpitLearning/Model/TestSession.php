<?php
namespace PpitLearning\Model;

use PpitCore\Model\Context;
use PpitLearning\Model\Test;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TestSession implements InputFilterAwareInterface
{
    public $id;
    public $instance_id;
    public $status;
    public $test_id;
    public $part_identifier;
    public $expected_time;
    public $expected_duration;
    public $next_session_id;
    public $audit;
    
    // Joined properties
    public $identifier;
    public $caption;

    // Transient properties
    public $test;
    public $properties;
    
    protected $inputFilter;

    // Static fields
    private static $table;
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->instance_id = (isset($data['instance_id'])) ? $data['instance_id'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->test_id = (isset($data['test_id'])) ? $data['test_id'] : null;
        $this->part_identifier = (isset($data['part_identifier'])) ? $data['part_identifier'] : null;
        $this->expected_time = (isset($data['expected_time'])) ? $data['expected_time'] : null;
        $this->expected_duration = (isset($data['expected_duration'])) ? $data['expected_duration'] : null;
        $this->next_session_id = (isset($data['next_session_id'])) ? $data['next_session_id'] : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
        
        // Joined properties
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['test_id'] = (int) $this->test_id;
    	$data['part_identifier'] =  $this->part_identifier;
    	$data['expected_time'] =  $this->expected_time;
    	$data['expected_duration'] =  $this->expected_duration;
    	$data['next_session_id'] = (int) $this->next_session__id;
    	$data['audit'] = $this->audit;

    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	 
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	unset($data['identifier']);
    	unset($data['caption']);
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    public static function getList($params, $major, $dir, $mode = 'todo')
    {
    	$select = TestSession::getTable()->getSelect()
    		->join('learning_test', 'learning_test_session.test_id = learning_test.id', array('identifier', 'caption'), 'left')
    		->order(array($major.' '.$dir, 'identifier'));
    	$where = new Where;
    	$where->notEqualTo('learning_test_session.status', 'deleted');
    
    	// Todo list vs search modes
    	if ($mode == 'todo') {
    	}
    	else {
    		foreach ($params as $propertyId => $property) {
    			if ($propertyId == 'identifier') $where->equalTo('learning_test.'.$propertyId, $params[$propertyId]);
    			elseif ($propertyId == 'caption') $where->like('learning_test.'.$propertyId, '%'.$params[$propertyId].'%');
    			elseif (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('learning_test_session.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    	$select->where($where);
    	$cursor = TestSession::getTable()->selectWith($select);
    	$sessions = array();
    	foreach ($cursor as $session) {
			if ($mode == 'todo') {
				if (!$session->expected_time || $session->expected_time >= date('Y-m-d h:i:s')) $sessions[$session->id] = $session;
			}
    		else $sessions[$session->id] = $session;
    	}
    	return $sessions;
    }
   
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$session = TestSession::getTable()->get($id, $column);
        if ($session) {
    		$session->test = Test::get($session->test_id);
    	}
    	return $session;
    }
    
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$session = new TestSession;
    	$session->status = 'new';
    	return $session;
    }
    
    public function loadData($data)
    {
    	$context = Context::getCurrent();
    	$auditRow = array(
    			'time' => Date('Y-m-d G:i:s'),
    			'n_fn' => $context->getFormatedName(),
    	);
    	if (array_key_exists('status', $data)) {
    		$status = trim(strip_tags($data['status']));
    		if ($status == '' || strlen($status) > 255) return 'Integrity';
    		if ($this->status != $status) $auditRow['status'] = $this->status = $status;
    	}
    	if (array_key_exists('test_id', $data)) {
			$test_id = (int) $data['test_id'];
    		if ($this->test_id != $test_id) $auditRow['test_id'] = $this->test_id = $test_id;
		}
        if (array_key_exists('part_identifier', $data)) {
    		$part_identifier = trim(strip_tags($data['part_identifier']));
    		if ($part_identifier == '' || strlen($part_identifier) > 255) return 'Integrity';
    		if ($this->part_identifier != $part_identifier) $auditRow['part_identifier'] = $this->part_identifier = $part_identifier;
    	}
		if (array_key_exists('expected_time', $data)) {
    		$expected_time = trim(strip_tags($data['expected_time']));
    		if ($expected_time == '' || strlen($expected_time) > 255) return 'Integrity';
    		if ($this->expected_time != $expected_time) $auditRow['expected_time'] = $this->expected_time = $expected_time;
    	}
        if (array_key_exists('expected_duration', $data)) {
			$expected_duration = (int) $data['expected_duration'];
    		if ($this-expected_>duration != $expected_duration) $auditRow['expected_duration'] = $this->expected_duration = $expected_duration;
		}
        if (array_key_exists('next_session_id', $data)) {
			$next_session_id = (int) $data['next_session_id'];
    		if ($this->next_session_id != $next_session_id) $auditRow['next_session_id'] = $this->next_session_id = $next_session_id;
		}
		$this->audit[] = $auditRow;
    	return 'OK';
    }
    
    public function add()
    {
    	$context = Context::getCurrent();
    
    	// Check consistency
    	$this->id = null;
    	TestSession::getTable()->save($this);
    
    	return ('OK');
    }
    
    public function update($update_time)
    {
    	$context = Context::getCurrent();
    	$session = TestSession::get($this->id);
    
    	// Isolation check
    	if ($update_time && $session->update_time > $update_time) return 'Isolation';
    
    	TestSession::getTable()->save($this);
    
    	return 'OK';
    }
   
	public function isDeletable() {
    	if (Generic::getTable()->cardinality('learning_test_result', array('status != ?' => 'deleted', 'test_session_id' => $this->id)) > 0) return false;
    	 
    	$config = Context::getCurrent()->getConfig();
    	foreach($config['ppitLearningDependencies'] as $dependency) {
    		if ($dependency->isUsed($this)) return false;
    	}
		
		return true;
	}
    
    public function delete($update_time)
    {
    	$context = Context::getCurrent();
    	$session = TestSession::get($this->id);
    
    	// Isolation check
    	if ($update_time && $session->update_time > $update_time) return 'Isolation';
    
    	TestSession::getTable()->delete($this->id);
    
    	return 'OK';
    }

    // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
    	throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        throw new \Exception("Not used");
    }

    public static function getTable()
    {
    	if (!TestSession::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		TestSession::$table = $sm->get('PpitLearning\Model\TestSessionTable');
    	}
    	return TestSession::$table;
    }
}