<?php
namespace PpitLearning\Model;

use PpitCore\Model\Context;
use PpitLearning\Model\TestSession;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TestResult implements InputFilterAwareInterface
{
    public $id;
    public $instance_id;
    public $status;
    public $contact_id;
    public $test_session_id;
    public $actual_time;
    public $actual_duration;
    public $answers;
    public $audit;
    public $update_time;
    
    // Joined properties
    public $identifier;
    public $caption;
    public $content;
    public $n_fn;
    public $expected_time;
    public $expected_duration;
    
    // Transient properties
    public $testSession;
    public $properties;
    public $score;
    
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
        $this->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
        $this->test_session_id = (isset($data['test_session_id'])) ? $data['test_session_id'] : null;
        $this->actual_time = (isset($data['actual_time'])) ? $data['actual_time'] : null;
        $this->actual_duration = (isset($data['actual_duration'])) ? $data['actual_duration'] : null;
        $this->answers = (isset($data['answers'])) ? json_decode($data['answers'], true) : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->content = (isset($data['content'])) ? json_decode($data['content'], true) : null;
        $this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
        $this->expected_time = (isset($data['expected_time'])) ? $data['expected_time'] : null;
        $this->expected_duration = (isset($data['expected_duration'])) ? $data['expected_duration'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['contact_id'] = (int) $this->contact_id;
    	$data['test_session_id'] = (int) $this->test_session_id;
    	$data['actual_time'] =  $this->actual_time;
    	$data['actual_duration'] =  $this->actual_duration;
    	$data['answers'] = $this->answers;
    	$data['audit'] = $this->audit;

    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	$data['n_fn'] = $this->n_fn;
    	$data['expected_time'] =  $this->expected_time;
    	$data['expected_duration'] =  $this->expected_duration;
    	 
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	unset($data['identifier']);
    	unset($data['caption']);
    	unset($data['n_fn']);
    	unset($data['expected_time']);
    	unset($data['expected_duration']);
    	$data['answers'] = ($this->answers) ? json_encode($this->answers) : null;
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    public static function getList($params, $major, $dir, $mode = 'todo')
    {
    	$select = TestResult::getTable()->getSelect()
    		->join('learning_test_session', 'learning_test_result.test_session_id = learning_test_session.id', array('expected_time', 'expected_duration'), 'left')
    		->join('learning_test', 'learning_test_session.test_id = learning_test.id', array('identifier', 'caption', 'content'), 'left')
    		->join('core_vcard', 'learning_test_result.contact_id = core_vcard.id', array('n_fn'), 'left')
    		->order(array($major.' '.$dir, 'identifier'));
    	$where = new Where;
    	$where->notEqualTo('learning_test_result.status', 'deleted');
    
    	// Todo list vs search modes
    	if ($mode == 'todo') {
    	}
    	else {
    		foreach ($params as $propertyId => $property) {
    			if ($propertyId == 'identifier') $where->equalTo('learning_test.'.$propertyId, $params[$propertyId]);
    			elseif ($propertyId == 'caption') $where->like('learning_test.'.$propertyId, '%'.$params[$propertyId].'%');
    			elseif ($propertyId == 'n_fn') $where->like('core_vcard.'.$propertyId, '%'.$params[$propertyId].'%');
    			elseif ($propertyId == 'min_expected_time') $where->greaterThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif ($propertyId == 'max_expected_time') $where->lessThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif ($propertyId == 'min_expected_duration') $where->greaterThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif ($propertyId == 'max_expected_duration') $where->lessThanOrEqualTo('learning_test_session.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('learning_test_result.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('learning_test_result.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('learning_test_result.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    	$select->where($where);
    	$cursor = TestResult::getTable()->selectWith($select);
    	$results = array();
    	foreach ($cursor as $result) {
    		$result->properties = $result->getProperties();
    	    $result->score = 0;
	    	foreach ($result->answers as $questionId => $answer) {
    			$result->score += $result->content['parts'][$questionId]['modalities'][$answer]['value'];
    		}
    		$results[] = $result;
    	}
    	return $results;
    }
   
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$result = TestResult::getTable()->get($id, $column);
    	if ($result) {
    		$result->testSession = TestSession::get($result->test_session_id);
    	}
    	$result->score = 0;
    	foreach ($result->answers as $questionId => $answer) {
    		$result->score += $result->testSession->test->content['parts'][$questionId]['modalities'][$answer]['value'];
    	}
    	return $result;
    }
    
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$result = new TestResult;
    	$result->status = 'new';
    	$result->audit = array();
    	return $result;
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
    	if (array_key_exists('contact_id', $data)) {
			$contact_id = (int) $data['contact_id'];
    		if ($this->contact_id != $contact_id) $auditRow['contact_id'] = $this->contact_id = $contact_id;
		}
    	if (array_key_exists('actual_time', $data)) {
    		$actual_time = trim(strip_tags($data['actual_time']));
    		if ($actual_time == '' || strlen($actual_time) > 255) return 'Integrity';
    		if ($this->actual_time != $actual_time) $auditRow['actual_time'] = $this->actual_time = $actual_time;
    	}
        if (array_key_exists('actual_duration', $data)) {
			$actual_duration = (int) $data['actual_duration'];
    		if ($this->actual_duration != $actual_duration) $auditRow['actual_duration'] = $this->actual_duration = $actual_duration;
		}
        if (array_key_exists('answers', $data)) {
			$answers = $data['answers'];
    		if ($this->answers != $answers) $auditRow['answers'] = $this->answers = $answers;
		}
		$this->audit[] = $auditRow;
    	return 'OK';
    }
    
    public function add()
    {
    	$context = Context::getCurrent();
    
    	// Check consistency
    	$this->id = null;
    	TestResult::getTable()->save($this);

    	return ('OK');
    }
    
    public function update($update_time)
    {
    	$context = Context::getCurrent();
    	$result = TestResult::get($this->id);
    
    	// Isolation check
    	if ($update_time && $result->update_time > $update_time) return 'Isolation';
    
    	TestResult::getTable()->save($this);
    
    	return 'OK';
    }
   
	public function isDeletable() {
    	$config = Context::getCurrent()->getConfig();
    	foreach($config['ppitLearningDependencies'] as $dependency) {
    		if ($dependency->isUsed($this)) return false;
    	}
		
		return true;
	}
    
    public function delete($update_time)
    {
    	$context = Context::getCurrent();
    	$result = TestResult::get($this->id);
    
    	// Isolation check
    	if ($update_time && $result->update_time > $update_time) return 'Isolation';
    
    	TestResult::getTable()->delete($this->id);
    
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
    	if (!TestResult::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		TestResult::$table = $sm->get('PpitLearning\Model\TestResultTable');
    	}
    	return TestResult::$table;
    }
}
