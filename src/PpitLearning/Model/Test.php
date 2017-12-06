<?php
namespace PpitLearning\Model;

use PpitCore\Model\Context;
use Zend\Db\Sql\Where;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Test implements InputFilterAwareInterface
{
    public $id;
    public $instance_id;
    public $status;
    public $type;
    public $identifier;
    public $caption;
    public $content;
    public $audit;
    
    protected $inputFilter;

    // Transient properties
    public $properties;
	public $axes;
    public $highestScore;
    
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
        $this->type = (isset($data['type'])) ? $data['type'] : null;
        $this->identifier = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->caption = (isset($data['caption'])) ? $data['caption'] : null;
        $this->content = (isset($data['content'])) ? json_decode($data['content'], true) : null;
      	$this->audit = (isset($data['audit'])) ? json_decode($data['audit'], true) : null;
    }

    public function getProperties()
    {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['instance_id'] = (int) $this->instance_id;
    	$data['status'] =  $this->status;
    	$data['type'] =  $this->type;
    	$data['identifier'] =  $this->identifier;
    	$data['caption'] =  $this->caption;
    	$data['content'] =  $this->content;
    	$data['audit'] = $this->audit;
    	return $data;
    }
    
    public function toArray()
    {
    	$data = $this->getProperties();
    	$data['content'] = ($this->content) ? json_encode($this->content) : null;
    	$data['audit'] = ($this->audit) ? json_encode($this->audit) : null;
    	return $data;
    }

    public static function getList($type, $params = array(), $major = 'identifier', $dir = 'ASC', $mode = 'todo')
    {
    	$select = Test::getTable()->getSelect()
    		->order(array($major.' '.$dir, 'identifier'))
    		->limit(200);
    	$where = new Where;
    	if ($type) $where->equalTo('test.type', $type);
    	 
    	// Todo list vs search modes
    	if ($mode == 'todo') {
	    	$where->equalTo('status', 'new');
    	}
    	else {
	    	$where->notEqualTo('status', 'deleted');
    		foreach ($params as $propertyId => $property) {
    			if (substr($propertyId, 0, 4) == 'min_') $where->greaterThanOrEqualTo('learning_test.'.substr($propertyId, 4), $params[$propertyId]);
    			elseif (substr($propertyId, 0, 4) == 'max_') $where->lessThanOrEqualTo('learning_test.'.substr($propertyId, 4), $params[$propertyId]);
    			else $where->like('learning_test.'.$propertyId, '%'.$params[$propertyId].'%');
    		}
    	}
    	$select->where($where);
    	$cursor = Test::getTable()->selectWith($select);
    	$tests = array();
    	foreach ($cursor as $test) $tests[$test->id] = $test;
    	return $tests;
    }
   
    public static function get($id, $column = 'id')
    {
    	$context = Context::getCurrent();
    	$test = Test::getTable()->get($id, $column);
    	$test->axes = $test->getAxes();
    	$test->highestScore = 0;
    	foreach ($test->content['questions'] as $questionId => $question) {
    		foreach ($question['axes'] as $axisId => &$axis) {
    			foreach ($axis['categories'] as $categoryId => &$category) {
    				if (!array_key_exists('highest_score', $test->axes[$axisId])) $test->axes[$axisId]['highest_score'] = 0;
    				$test->axes[$axisId]['highest_score'] += $category['weight'];
    				if (!array_key_exists('highest_score', $test->axes[$axisId]['categories'][$categoryId])) $test->axes[$axisId]['categories'][$categoryId]['highest_score'] = 0;
    				$test->axes[$axisId]['categories'][$categoryId]['highest_score'] += $category['weight'];
    			}
    		}
    		$test->highestScore++;
    	}
    	return $test;
    }

    public function getDescription()
    {
    	$context = Context::getCurrent();
    	if ($context->getConfig()['specificationMode'] == 'config') return $context->getConfig()['test/'.$this->identifier]['description'];
    	else return $this->content['description'];
    }
    
    public function getAxes()
    {
    	$context = Context::getCurrent();
    	if ($context->getConfig()['specificationMode'] == 'config') return $context->getConfig()['test/'.$this->identifier]['axes'];
    	else return $this->content['axes'];
    }

    public function getRules()
    {
    	$context = Context::getCurrent();
    	if ($context->getConfig()['specificationMode'] == 'config') return $context->getConfig()['test/'.$this->identifier]['rules'];
    	else return $this->content['rules'];
    }

    public function getQuestions()
    {
    	$context = Context::getCurrent();
    	if ($context->getConfig()['specificationMode'] == 'config') return $context->getConfig()['test/'.$this->identifier]['questions'];
    	else return $this->content['questions'];    }

    public function getParts()
    {
    	$context = Context::getCurrent();
    	if ($context->getConfig()['specificationMode'] == 'config') return $context->getConfig()['test/'.$this->identifier]['parts'];
    	else return $this->content['parts'];
    }
    
    public static function instanciate()
    {
    	$context = Context::getCurrent();
    	$test = new Test;
    	$test->status = 'new';
    	$test->content = array();
    	$test->audit = array();
    	return $test;
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
        if (array_key_exists('type', $data)) {
    		$type = trim(strip_tags($data['type']));
    		if ($type == '' || strlen($type) > 255) return 'Integrity';
    		if ($this->type != $type) $auditRow['type'] = $this->type = $type;
    	}
    	if (array_key_exists('identifier', $data)) {
    		$identifier = trim(strip_tags($data['identifier']));
    		if ($identifier == '' || strlen($identifier) > 255) return 'Integrity';
    		if ($this->identifier != $identifier) $auditRow['identifier'] = $this->identifier = $identifier;
    	}
        if (array_key_exists('caption', $data)) {
    		$caption = trim(strip_tags($data['caption']));
    		if ($caption == '' || strlen($caption) > 255) return 'Integrity';
    		if ($this->caption != $caption) $auditRow['caption'] = $this->caption = $caption;
    	}
    	if (array_key_exists('content', $data)) {
    		$content = $data['content'];
    		if ($this->content != $content) $auditRow['content'] = $this->content = $content;
    	}
    	$this->audit[] = $auditRow;
    	return 'OK';
    }
    
    public function add()
    {
    	$context = Context::getCurrent();
    
    	// Check consistency
    	if (Generic::getTable()->cardinality('learning_test', array('identifier' => $this->identifier, 'status != ?' => 'deleted')) > 0) return 'Duplicate';
    	$this->id = null;
    	Test::getTable()->save($this);
    
    	return ('OK');
    }
    
    public function update($update_time)
    {
    	$context = Context::getCurrent();
    	$test = Test::get($this->id);
    
    	// Isolation check
    	if ($update_time && $test->update_time > $update_time) return 'Isolation';
    
    	Test::getTable()->save($this);
    
    	return 'OK';
    }
    
    public static function processInteraction($interaction)
    {
    	$context = Context::getCurrent();
    	$content = json_decode($interaction->content, true);
    	$globalRc = 'OK';
    	$newContent = array();
    	foreach ($content as $data) {
    		$connection = Test::getTable()->getAdapter()->getDriver()->getConnection();
    		$connection->beginTransaction();
    		try {
    			if ($data['action'] == 'update' || $data['action'] == 'delete') $test = Test::get($data['identifier'], 'identifier');
    			elseif ($data['action'] == 'add') $test = Test::instanciate();
    			if ($data['action'] == 'delete') $rc = $test->delete(null);
    			else {
    				if ($test->loadData($data) != 'OK') throw new \Exception('View error');
    				if (!$test->id) $rc = $test->add();
    				else $rc = $test->update(null);
    				$data['result'] = $rc;
    				if ($rc != 'OK') {
    					$globalRc = 'partial';
    					$connection->rollback();
    				}
    				else $connection->commit();
    			}
    		}
    		catch (\Exception $e) {
    			$connection->rollback();
    			throw $e;
    		}
    		$newContent[] = $data;
    	}
    	$interaction->content = json_encode($newContent);
    	$interaction->update(null);
    	return $globalRc;
    }
    
	public function isDeletable() {
    	if (Generic::getTable()->cardinality('learning_test_session', array('status != ?' => 'deleted', 'test_id' => $this->id)) > 0) return false;
    	 
    	$config = Context::getCurrent()->getConfig();
    	foreach($config['ppitLearningDependencies'] as $dependency) {
    		if ($dependency->isUsed($this)) return false;
    	}
		
		return true;
	}
    
    public function delete($update_time)
    {
    	$context = Context::getCurrent();
    	$test = Test::get($this->id);
    
    	// Isolation check
    	if ($update_time && $test->update_time > $update_time) return 'Isolation';
    
    	Test::getTable()->delete($this->id);
    
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
    	if (!Test::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Test::$table = $sm->get('PpitLearning\Model\TestTable');
    	}
    	return Test::$table;
    }
}
