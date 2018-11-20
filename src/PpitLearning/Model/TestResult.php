<?php
namespace PpitLearning\Model;

use PpitCore\Model\Account;
use PpitCore\Model\Context;
use PpitCore\Model\Generic;
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
    public $account_id;
    public $test_session_id;
    public $next_result_id;
    public $authentication_token;
    public $actual_date;
    public $actual_time;
    public $time_zone;
    public $location;
    public $latitude;
    public $longitude;
    public $actual_duration;
    public $answers;
    public $audit;
    public $update_time;
    
    // Joined properties
    public $type;
    public $identifier;
    public $place_identifier;
    public $place_caption;
    public $account_identifier;
    public $test_id;
    public $n_title;
    public $n_first;
    public $n_last;
    public $n_fn;
    public $email;
    public $tel_cell;
    public $caption;
    public $part_identifier;
    public $content;
    public $expected_date;
    public $expected_time;
    public $expected_duration;
    public $vcard_id; // Deprecated
    
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
        $this->account_id = (isset($data['account_id'])) ? $data['account_id'] : null;
        $this->test_session_id = (isset($data['test_session_id'])) ? $data['test_session_id'] : null;
        $this->next_result_id = (isset($data['next_result_id'])) ? $data['next_result_id'] : null;
        $this->authentication_token = (isset($data['authentication_token'])) ? $data['authentication_token'] : null;
        $this->actual_date = (isset($data['actual_date'])) ? $data['actual_date'] : null;
        $this->actual_time = (isset($data['actual_time'])) ? $data['actual_time'] : null;
        $this->time_zone = (isset($data['time_zone'])) ? $data['time_zone'] : null;
        $this->location = (isset($data['location'])) ? $data['location'] : null;
        $this->latitude = (isset($data['latitude'])) ? $data['latitude'] : null;
        $this->longitude = (isset($data['longitude'])) ? $data['longitude'] : null;
        $this->actual_duration = (isset($data['actual_duration'])) ? $data['actual_duration'] : null;
        $this->answers = (isset($data['answers'])) ? json_decode($data['answers'], true) : null;
        $this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
        $this->update_time = (isset($data['update_time'])) ? $data['update_time'] : null;
        
        // Joined properties
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->place_identifier = (isset($data['place_identifier'])) ? $data['place_identifier'] : null;
        $this->place_caption = (isset($data['place_caption'])) ? $data['place_caption'] : null;
        $this->account_identifier = (isset($data['account_identifier'])) ? $data['account_identifier'] : null;
        $this->test_id = (isset($data['test_id'])) ? $data['test_id'] : null;
        $this->n_title = (isset($data['n_title'])) ? $data['n_title'] : null;
        $this->n_first = (isset($data['n_first'])) ? $data['n_first'] : null;
        $this->n_last = (isset($data['n_last'])) ? $data['n_last'] : null;
        $this->n_fn = (isset($data['n_fn'])) ? $data['n_fn'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->tel_cell = (isset($data['tel_cell'])) ? $data['tel_cell'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->part_identifier = (isset($data['part_identifier'])) ? $data['part_identifier'] : null;
        $this->content = (isset($data['content'])) ? json_decode($data['content'], true) : null;
        $this->expected_date = (isset($data['expected_date'])) ? $data['expected_date'] : null;
        $this->expected_time = (isset($data['expected_time'])) ? $data['expected_time'] : null;
        $this->expected_duration = (isset($data['expected_duration'])) ? $data['expected_duration'] : null;
        $this->vcard_id = (isset($data['vcard_id'])) ? $data['vcard_id'] : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['place_id'] = (int) $this->place_id;
    	$data['account_id'] = (int) $this->account_id;
    	$data['test_session_id'] = (int) $this->test_session_id;
    	$data['next_result_id'] = (int) $this->next_result_id;
    	$data['authentication_token'] =  $this->authentication_token;
    	$data['actual_date'] =  ($this->actual_date) ? $this->actual_date : null;
    	$data['actual_time'] =  ($this->actual_time) ? $this->actual_time : null;
    	$data['actual_duration'] =  $this->actual_duration;
    	$data['answers'] = $this->answers;
    	$data['audit'] = $this->audit;

    	$data['place_identifier'] = $this->place_identifier;
    	$data['place_caption'] = $this->place_caption;
    	$data['test_id'] = $this->test_id;
    	$data['identifier'] = $this->identifier;
    	$data['caption'] = $this->caption;
    	$data['part_identifier'] = $this->part_identifier;
    	$data['n_title'] = $this->n_title;
    	$data['n_first'] = $this->n_first;
    	$data['n_last'] = $this->n_last;
    	$data['n_fn'] = $this->n_fn;
    	$data['email'] = $this->email;
    	$data['tel_cell'] = $this->tel_cell;
    	$data['expected_date'] =  $this->expected_date;
    	$data['expected_time'] =  $this->expected_time;
    	$data['expected_duration'] =  $this->expected_duration;
    	$data['vcard_id'] = $this->vcard_id;
    	 
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	unset($data['place_identifier']);
    	unset($data['place_caption']);
    	unset($data['test_id']);
    	unset($data['identifier']);
    	unset($data['caption']);
    	unset($data['part_identifier']);
    	unset($data['n_title']);
    	unset($data['n_first']);
    	unset($data['n_last']);
    	unset($data['n_fn']);
    	unset($data['email']);
    	unset($data['tel_cell']);
    	unset($data['expected_date']);
    	unset($data['expected_time']);
    	unset($data['expected_duration']);
    	unset($data['vcard_id']);
    	$data['answers'] = json_encode($this->answers);
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    public static function getList($type, $params, $major, $dir, $mode = 'todo')
    {
    	$context = Context::getCurrent();
    	$select = TestResult::getTable()->getSelect()
    		->join('learning_test_session', 'learning_test_result.test_session_id = learning_test_session.id', array('part_identifier', 'expected_date', 'expected_time', 'expected_duration'), 'left')
    		->join('learning_test', 'learning_test_session.test_id = learning_test.id', array('type', 'test_id' => 'id', 'identifier', 'caption', 'content'), 'left')
    		->join('core_account', 'learning_test_result.account_id = core_account.id', array('account_identifier' => 'identifier'), 'left')
    		->join('core_vcard', 'core_account.contact_1_id = core_vcard.id', array('n_title', 'n_first', 'n_last', 'n_fn', 'email', 'tel_cell', 'vcard_id' => 'id'), 'left')
    		->join('core_place', 'learning_test_result.place_id = core_place.id', array('place_identifier' => 'identifier', 'place_caption' => 'caption'), 'left')
    		->order(array($major.' '.$dir, 'identifier'));
    	$where = new Where;
		$where->notEqualTo('learning_test_result.status', 'deleted');
    	if ($type) $where->equalTo('learning_test.type', $type);
    	 
    	// Todo list vs search modes
    	if ($mode == 'todo') {
    		$where->in('learning_test_result.status', array('new', 'in_progress'));
    	}
    	else {
    		foreach ($params as $propertyId => $value) {
    			$property = $context->getConfig('testResult'.(($type) ? '/'.$type : ''))['properties'][$propertyId];
    			if ($property['definition'] != 'inline') $property = $context->getConfig($property['definition']);
    			
    			// Properties with a name shared between tables have to be qualified by the table name
    		    if ($propertyId == 'status') {
    				if ($value == '*') $where->notEqualTo('learning_test_result.status', '');
    				else $where->equalTo('learning_test_result.status', $value);
    			}
    		    elseif ($propertyId == 'place_id') $where->equalTo('core_place.id', $value);
    			elseif ($propertyId == 'caption') {
    				if ($value == '*') $where->notEqualTo('learning_test.caption', '');
    				else $where->like('learning_test.caption', '%'.$value.'%');
    			}
    			// Properties that have been SQL-aliased have to be tested with their real database name
    			elseif ($propertyId == 'place_identifier') {
    				if ($value == '*') $where->notEqualTo('learning_test.place_id', '');
    				else $where->like('core_place.identifier', '%'.$value.'%');
    			}
    			elseif ($propertyId == 'place_caption') {
    				if ($value == '*') $where->notEqualTo('learning_test.place_id', '');
    				else $where->like('core_place.caption', '%'.$value.'%');
    			}
    		    elseif ($propertyId == 'test_id') {
    				if ($value == '*') $where->notEqualTo('learning_test_session.test_id', '');
    				else $where->equalTo('learning_test.id', $value);
    			}
    		    elseif ($propertyId == 'test_session_id') {
    				if ($value == '*') $where->notEqualTo('learning_test_result.test_session_id', '');
    				else $where->equalTo('learning_test_session.id', $value);
    			}
    			// Other properties can be generically checked
    			elseif (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo(substr($propertyId, 4), $value);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo(substr($propertyId, 4), $value);
    			elseif (strpos($value, ',')) $where->in($propertyId, array_map('trim', explode(', ', $value)));
    			elseif ($value == '*') $where->notEqualTo($propertyId, '');
    			elseif ($property['type'] == 'select') $where->equalTo($propertyId, $value);
    			else $where->like($propertyId, '%'.$value.'%');
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
    	foreach ($this->answers as $questionId => $answer) {
    		$question = $this->testSession->test->content['questions'][$questionId];
    		foreach ($question['axes'] as $axisId => &$axis) {
    			foreach ($axis['categories'] as $categoryId => &$category) {
    				$value = $category['distribution'][$answer] * $category['weight'];
    				if (!array_key_exists('score', $this->axes[$axisId])) $this->axes[$axisId]['score'] = 0;
    				$this->axes[$axisId]['score'] += $value;
    				if (!array_key_exists('score', $this->axes[$axisId]['categories'][$categoryId])) $this->axes[$axisId]['categories'][$categoryId]['score'] = 0;
    				$this->axes[$axisId]['categories'][$categoryId]['score'] += $value;
    				if (!array_key_exists('answers', $this->axes[$axisId]['categories'][$categoryId])) $this->axes[$axisId]['categories'][$categoryId]['answers'] = [];
    				$this->axes[$axisId]['categories'][$categoryId]['answers'][$questionId] = $answer;
    			}
    		}
    	}
    	foreach ($this->axes as $axisId => &$axis) {
    		if (!array_key_exists('score', $axis)) $axis['score'] = 0;
			$axis['score'] = round($axis['score'] / $this->testSession->test->axes[$axisId]['highest_score'], 2);
			$axis['note'] = null;
				foreach ($axis['segmentation'] as $segmentId => &$segment) {
				if (!$axis['note'] && $axis['score'] <= $segment['limit']) $axis['note'] = $segment;
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

    		if ($result->account_id) {
    			$account = Account::get($result->account_id);
    			if ($account) $vcard = Vcard::get($account->contact_1_id);
    			else $vcard = null;
    			if ($vcard) $result->n_title = $vcard->n_title;
    			if ($vcard) $result->n_first = $vcard->n_first;
    			if ($vcard) $result->n_last = $vcard->n_last;
    			if ($vcard) $result->n_fn = $vcard->n_fn;
    			if ($vcard) $result->email = $vcard->email;
    			if ($vcard) $result->tel_cell = $vcard->tel_cell;
    			if ($vcard) $result->vcard_id = $vcard->id; // Deprecated
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
    	if (array_key_exists('account_id', $data)) {
			$account_id = (int) $data['account_id'];
    		if ($this->account_id != $account_id) $auditRow['account_id'] = $this->account_id = $account_id;
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
        if (array_key_exists('actual_date', $data)) {
	    	$actual_date = $data['actual_date'];
			if ($actual_date && !checkdate(substr($actual_date, 5, 2), substr($actual_date, 8, 2), substr($actual_date, 0, 4))) return 'Integrity';
	    	if ($this->actual_date != $actual_date) $auditRow['actual_date'] = $this->actual_date = $actual_date;
    	}
        	if (array_key_exists('actual_time', $data)) {
    		$actual_time = substr(trim(strip_tags($data['actual_time'])), 0, 19);
			if ($actual_time && !TestSession::checktime($actual_time)) return 'Integrity';
    		if ($this->actual_time != $actual_time) $auditRow['actual_time'] = $this->actual_time = $actual_time;
    	}
        if (array_key_exists('time_zone', $data)) {
			$time_zone = (int) $data['time_zone'];
    		if ($this->time_zone != $time_zone) $auditRow['time_zone'] = $this->time_zone = $time_zone;
		}
        if (array_key_exists('location', $data)) {
    		$location = trim(strip_tags($data['location']));
    		if ($location == '' || strlen($location) > 255) return 'Integrity';
    		if ($this->location != $location) $auditRow['location'] = $this->location = $location;
    	}
        if (array_key_exists('latitude', $data)) {
			$latitude = (float) $data['latitude'];
    		if ($this->latitude != $latitude) $auditRow['latitude'] = $this->latitude = $latitude;
		}
        if (array_key_exists('longitude', $data)) {
			$longitude = (float) $data['longitude'];
    		if ($this->longitude != $longitude) $auditRow['longitude'] = $this->longitude = $longitude;
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

    public function loadAndAdd($data, $configProperties)
    {
    	$context = Context::getCurrent();
    
    	if (!array_key_exists('status', $data)) $data['status'] = 'new';
    	foreach ($configProperties as $propertyId => $property) {
    		if (array_key_exists($propertyId, $data) && $data[$propertyId]) {
    			if ($property['type'] == 'select') {
    				foreach (explode(',', $data[$propertyId]) as $modalityId) {
    					if (!array_key_exists($modalityId, $property['modalities'])) {
    						return ['400', 'The modality '.$data[$propertyId].' does not exist in '.$propertyId];
    					}
    				}
    			}
    			elseif ($property['type'] == 'date' && $data[$propertyId] && (strlen($data[$propertyId] < 10) || !checkdate(substr($data[$propertyId], 5, 2), substr($data[$propertyId], 8, 2), substr($data[$propertyId], 0, 4)))) {
    				return ['400', $data[$propertyId].' is not a valid date for '.$propertyId];
    			}
    		}
    	}
    
    	// Load the data
    	$rc = $this->loadData($data);
    	if ($rc != 'OK') return ['500', $rc];
    	
    	$rc = $vcard->add();
    	$account->contact_1_id = $vcard->id;
    	$rc = $account->add();
    	$this->authentication_token = md5(uniqid(rand(), true));
    	$this->account_id = $account->id;
    	$rc = $this->add();
    	$result_id = $this->id;
    	$resultIds = array();
    	$session = TestSession::get($this->test_session_id);
    	while ($rc =='OK' && $session->next_session_id) {
    		$session = TestSession::get($session->next_session_id);
    		$this->test_session_id = $session->id;
    		$rc = $this->add();
    		$resultIds[] = $this->id;
    	}
    	$result = TestResult::get($result_id);
    	foreach ($resultIds as $id) {
    		$result->next_result_id = $id;
    		$result->update(null);
    		$result = TestResult::get($id);
    	}
    	 
    	// Save the data
    	$this->add();
    	if ($rc != 'OK') return ['500', 'event->add: '.$rc];
    
    	$this->properties = $this->getProperties();
    	return ['200'];
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
    
    public function loadAndUpdate($data, $configProperties, $update_time = null)
    {
    	$context = Context::getCurrent();
    
    	foreach ($configProperties as $propertyId => $property) {
    		if (array_key_exists($propertyId, $data)) {
    			if ($property['type'] == 'select' && !array_key_exists($data[$propertyId], $property['modalities'])) {
    				return ['400', 'The modality '.$data[$propertyId].' does not exist in '.$propertyId];
    			}
    			elseif ($property['type'] == 'date' && $data[$propertyId] && (strlen($data[$propertyId] < 10) || !checkdate(substr($data[$propertyId], 5, 2), substr($data[$propertyId], 8, 2), substr($data[$propertyId], 0, 4)))) {
    				return ['400', $data[$propertyId].' is not a valid date for '.$propertyId];
    			}
    		}
    	}
    
    	// Load the data
    	$rc = $this->loadData($data);
    	if ($rc != 'OK') return ['500', $rc];
 
    	Event::getTable()->multipleDelete(array('type' => 'test_note', 'account_id' => $this->account_id));
    	Event::getTable()->multipleDelete(array('type' => 'test_detail', 'account_id' => $this->account_id));
    	 
    	// Save the data
    	$this->update($update_time);
    	if ($rc != 'OK') return ['500', 'event->update: '.$rc];
    	return ['200'];
    }
    
    public function isUsed($object)
    {
    	// Allow or not deleting an account
    	if (get_class($object) == 'PpitCore\Model\Account') {
    		if (Generic::getTable()->cardinality('learning_test_result', array('account_id' => $object->id)) > 0) return true;
    	}
    	return false;
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
