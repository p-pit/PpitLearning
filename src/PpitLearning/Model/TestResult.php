<?php
namespace PpitLearning\Model;

use PpitCore\Model\Context;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
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
    public $place_id;
    public $vcard_id;
    public $test_session_id;
    public $next_result_id;
    public $authentication_token;
    public $actual_time;
    public $actual_duration;
    public $answers;
    public $audit;
    public $update_time;
    
    // Joined properties
    public $identifier;
    public $place_caption;
    public $n_title;
    public $n_first;
    public $n_last;
    public $n_fn;
    public $email;
    public $tel_cell;
    public $caption;
    public $content;
    public $expected_time;
    public $expected_duration;
    
    // Transient properties
    public $testSession;
    public $properties;
    public $axes;
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
        $this->place_id = (isset($data['place_id'])) ? $data['place_id'] : null;
        $this->vcard_id = (isset($data['vcard_id'])) ? $data['vcard_id'] : null;
        $this->test_session_id = (isset($data['test_session_id'])) ? $data['test_session_id'] : null;
        $this->next_result_id = (isset($data['next_result_id'])) ? $data['next_result_id'] : null;
        $this->authentication_token = (isset($data['authentication_token'])) ? $data['authentication_token'] : null;
        $this->actual_time = (isset($data['actual_time'])) ? $data['actual_time'] : null;
        $this->actual_duration = (isset($data['actual_duration'])) ? $data['actual_duration'] : null;
        $this->answers = (isset($data['answers'])) ? json_decode($data['answers'], true) : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->place_caption = (isset($data['place_caption'])) ? $data['place_caption'] : null;
        $this->n_title = (isset($data['n_title'])) ? $data['n_title'] : null;
        $this->n_first = (isset($data['n_first'])) ? $data['n_first'] : null;
        $this->n_last = (isset($data['n_last'])) ? $data['n_last'] : null;
        $this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->tel_cell = (isset($data['tel_cell'])) ? $data['tel_cell'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->content = (isset($data['content'])) ? json_decode($data['content'], true) : null;
        $this->expected_time = (isset($data['expected_time'])) ? $data['expected_time'] : null;
        $this->expected_duration = (isset($data['expected_duration'])) ? $data['expected_duration'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['place_id'] = (int) $this->place_id;
    	$data['vcard_id'] = (int) $this->vcard_id;
    	$data['test_session_id'] = (int) $this->test_session_id;
    	$data['next_result_id'] = (int) $this->next_result_id;
    	$data['authentication_token'] =  $this->authentication_token;
    	$data['actual_time'] =  ($this->actual_time) ? $this->actual_time : null;
    	$data['actual_duration'] =  $this->actual_duration;
    	$data['answers'] = $this->answers;
    	$data['audit'] = $this->audit;

    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	$data['n_title'] = $this->n_title;
    	$data['n_first'] = $this->n_first;
    	$data['n_last'] = $this->n_last;
    	$data['n_fn'] = $this->n_fn;
    	$data['email'] = $this->email;
    	$data['tel_cell'] = $this->tel_cell;
    	$data['expected_time'] =  $this->expected_time;
    	$data['expected_duration'] =  $this->expected_duration;
    	 
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	unset($data['identifier']);
    	unset($data['caption']);
    	unset($data['n_title']);
    	unset($data['n_first']);
    	unset($data['n_last']);
    	unset($data['n_fn']);
    	unset($data['email']);
    	unset($data['tel_cell']);
    	unset($data['expected_time']);
    	unset($data['expected_duration']);
    	$data['answers'] = json_encode($this->answers);
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    public static function getList($params, $major, $dir, $mode = 'todo')
    {
    	$select = TestResult::getTable()->getSelect()
    		->join('learning_test_session', 'learning_test_result.test_session_id = learning_test_session.id', array('expected_time', 'expected_duration'), 'left')
    		->join('learning_test', 'learning_test_session.test_id = learning_test.id', array('identifier', 'caption', 'content'), 'left')
    		->join('core_vcard', 'learning_test_result.vcard_id = core_vcard.id', array('n_title', 'n_first', 'n_last', 'n_fn', 'email', 'tel_cell'), 'left')
    		->join('core_place', 'learning_test_result.place_id = core_place.id', array('place_caption' => 'caption'), 'left')
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
	    		$question = $result->content['questions'][$questionId];
    			if ($question['type'] == 'select') $result->score += $question['modalities'][$answer]['value'];
    		}
    		$results[] = $result;
    	}
    	return $results;
    }

    public function computeScores()
    {
    	$context = Context::getCurrent();
    	$this->axes = $this->testSession->test->getAxes();
    	$this->score = 0;
    	foreach ($this->testSession->test->content['questions'] as $questionId => $question) {
	    	$value = 0;
    		if (array_key_exists($questionId, $this->answers)) {
    		 	$answer = $this->answers[$questionId];
    		 	if ($question['type'] == 'select') $value = $question['modalities'][$answer]['value'];
	    		elseif ($question['type'] == 'phpCode') {
	    			if ($answer['result'] == $question['result']) $value = $question['value'];
	    		}
    		}
    		$this->score += $value;
    		foreach ($question['axes'] as $axisId => &$axis) {
    			foreach ($axis['categories'] as $categoryId => &$category) {
    				if (!array_key_exists('score', $this->axes[$axisId])) $this->axes[$axisId]['score'] = 0;
    				$this->axes[$axisId]['score'] += $value * $category['weight'];
    				if (!array_key_exists('score', $this->axes[$axisId]['categories'][$categoryId])) $this->axes[$axisId]['categories'][$categoryId]['score'] = 0;
    				$this->axes[$axisId]['categories'][$categoryId]['score'] += $value * $category['weight'];
    			}
    		}
    	}
    	foreach ($this->axes as $axisId => &$axis) {
    		if (array_key_exists('score', $axis)) {
    			$axis['score'] = round($axis['score'] / $this->testSession->test->axes[$axisId]['highest_score'], 2);
    			$axis['note'] = null;
    			foreach ($axis['segmentation'] as $segmentId => &$segment) {
    				if (!$axis['note'] && $axis['score'] <= $segment['limit']) $axis['note'] = $segment;
    			}
    		}
    	}
    }
    
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$result = TestResult::getTable()->get($id, $column);
    	if ($result) {
    		if ($result->place_id) {
    			$place = Place::get($result->place_id);
    			if ($place) $result->place_caption = $place->caption;
    		}

    		if ($result->vcard_id) {
    			$vcard = Vcard::get($result->vcard_id);
    			if ($vcard) $result->n_title = $vcard->n_title;
    			if ($vcard) $result->n_first = $vcard->n_first;
    			if ($vcard) $result->n_last = $vcard->n_last;
    			if ($vcard) $result->n_fn = $vcard->n_fn;
    			if ($vcard) $result->email = $vcard->email;
    			if ($vcard) $result->tel_cell = $vcard->tel_cell;
    		}
    		
    		$result->testSession = TestSession::get($result->test_session_id);
    		$result->computeScores();
    	}
    	return $result;
    }
    
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$result = new TestResult;
    	$result->status = 'new';
    	$result->answers = array();
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
        if (array_key_exists('place_id', $data)) {
			$place_id = (int) $data['place_id'];
    		if ($this->place_id != $place_id) $auditRow['place_id'] = $this->place_id = $place_id;
		}
    	if (array_key_exists('vcard_id', $data)) {
			$vcard_id = (int) $data['vcard_id'];
    		if ($this->vcard_id != $vcard_id) $auditRow['vcard_id'] = $this->vcard_id = $vcard_id;
		}
        if (array_key_exists('test_session_id', $data)) {
			$test_session_id = (int) $data['test_session_id'];
    		if ($this->test_session_id != $test_session_id) $auditRow['test_session_id'] = $this->test_session_id = $test_session_id;
		}
        if (array_key_exists('next_result_id', $data)) {
			$next_result_id = (int) $data['next_result_id'];
    		if ($this->next_result_id != $next_result_id) $auditRow['next_result_id'] = $this->next_result_id = $next_result_id;
		}
		if (array_key_exists('authentication_token', $data)) {
    		$authentication_token = trim(strip_tags($data['authentication_token']));
    		if ($authentication_token == '' || strlen($authentication_token) > 255) return 'Integrity';
    		if ($this->authentication_token != $authentication_token) $auditRow['authentication_token'] = $this->authentication_token = $authentication_token;
    	}
		if (array_key_exists('actual_time', $data)) {
    		$actual_time = trim(strip_tags($data['actual_time']));
    		if (strlen($actual_time) > 255) return 'Integrity';
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
