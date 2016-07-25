<?php
namespace PpitLearning\Model;

use PpitCore\Model\Context;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Evaluation implements InputFilterAwareInterface
{
    public $id;
    public $learner_id;
    public $order;
    public $period;
    public $subject;
    public $note;
    public $learner_average;
    public $class_average;
    public $coefficient;
    public $lower_note;
    public $higher_note;
    public $number_of_notes;
    public $appreciation;
    public $dis_note;
    public $dis_appreciation;
    public $piece_jointe;

    // Transient property
//    public $periods = array(1 => 'Sept./Oct.', 2 => 'Nov./Déc.', 3 => 'Jan./Fév.', 4 => 'Mars/Avril', 5 => 'Mai/Juin');
    
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
        $this->learner_id = (isset($data['learner_id'])) ? $data['learner_id'] : null;
        $this->order = (isset($data['order'])) ? $data['order'] : null;
        $this->period = (isset($data['period'])) ? $data['period'] : null;
        $this->subject = (isset($data['subject'])) ? $data['subject'] : null;
        $this->note = (isset($data['note'])) ? $data['note'] : null;
        $this->learner_average = (isset($data['learner_average'])) ? $data['learner_average'] : null;
        $this->class_average = (isset($data['class_average'])) ? $data['class_average'] : null;
        $this->coefficient = (isset($data['coefficient'])) ? $data['coefficient'] : null;
        $this->lower_note = (isset($data['lower_note'])) ? $data['lower_note'] : null;
        $this->higher_note = (isset($data['higher_note'])) ? $data['higher_note'] : null;
        $this->number_of_notes = (isset($data['number_of_notes'])) ? $data['number_of_notes'] : null;
        $this->appreciation = (isset($data['appreciation'])) ? $data['appreciation'] : null;
        $this->dis_note = (isset($data['dis_note'])) ? $data['dis_note'] : null;
        $this->dis_appreciation = (isset($data['dis_appreciation'])) ? $data['dis_appreciation'] : null;
        $this->piece_jointe = (isset($data['piece_jointe'])) ? $data['piece_jointe'] : null;
    }

    public function toArray() {
    	$data = array();
    	$data['id'] = (int) $this->id;
    	$data['learner_id'] = (int) $this->learner_id;
    	$data['order'] = (int) $this->order;
    	$data['period'] = $this->period;
    	$data['subject'] = $this->subject;
    	$data['note'] = $this->note;
    	$data['learner_average'] = (float) $this->learner_average;
    	$data['class_average'] = (float) $this->class_average;
    	$data['coefficient'] = (float) $this->coefficient;
    	$data['lower_note'] = (float) $this->lower_note;
    	$data['higher_note'] = (float) $this->higher_note;
    	$data['number_of_notes'] = (int) $this->number_of_notes;
    	$data['appreciation'] = $this->appreciation;
    	$data['dis_note'] = $this->dis_note;
    	$data['dis_appreciation'] = $this->dis_appreciation;
    	$data['piece_jointe'] = $this->piece_jointe;
    	return $data;
    }

    public static function getList($major, $dir, $learner_id = null)
    {
    	$select = Evaluation::getTable()->getSelect()
    		->order(array($major.' '.$dir, 'period DESC', 'subject'));
    	if ($learner_id) $select->where(array('learner_id' => $learner_id));
    	$cursor = Evaluation::getTable()->selectWith($select);
    	$evaluations = array();
    	foreach ($cursor as $evaluation) $evaluations[] = $evaluation;
    	return $evaluations;
    }
    
    public function loadData($data) {
    	$this->period = trim(strip_tags($data['period']));
    	$this->subject = trim(strip_tags($data['subject']));
    	$this->note = (int) $data['note'];
    	$this->learner_average = (float) $data['learner_average'];
    	$this->class_average = (float) $data['class_average'];
    	$this->coefficient = (float) $data['coefficient'];
    	$this->lower_note = (float) $data['lower_note'];
    	$this->higher_note = (float) $data['higher_note'];
    	$this->number_of_notes = (int) $data['number_of_notes'];
    	$this->appreciation = trim(strip_tags($data['appreciation']));
    	$this->dis_note = (int) $data['dis_note'];
    	$this->dis_appreciation = trim(strip_tags($data['dis_appreciation']));
    	$this->piece_jointe = trim(strip_tags($data['piece_jointe']));

    	if (!$this->period
    	||	!$this->subject || strlen($this->subject) > 255
    	||	$this->note < 0 || $this->note > 5
    	||	$this->learner_average < 0 || $this->learner_average > 999
    	||	$this->class_average < 0 || $this->class_average > 999
    	||	$this->coefficient < 0 || $this->coefficient > 999
    	||	$this->lower_note < 0 || $this->lower_note > 999
    	||	$this->higher_note < 0 || $this->higher_note > 999
    	||	$this->number_of_notes < 0 || $this->number_of_notes > 999
    	||	strlen($this->appreciation) > 2047
    	||	$this->dis_note < 0 || $this->dis_note > 5
    	||	strlen($this->dis_appreciation) > 2047
    	||	strlen($this->piece_jointe) > 255) {
    		
	    	return 400;
    	}
    	return null;
    }
    
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
    	if (!Evaluation::$table) {
    		$sm = Context::getCurrent()->getServiceManager();
    		Evaluation::$table = $sm->get('PpitLearning\Model\EvaluationTable');
    	}
    	return Evaluation::$table;
    }
}