<?php
/**
 * PpitCore V1.0 (https://github.com/p-pit/PpitCore)
 *
 * @link      https://github.com/p-pit/PpitCore
 * @copyright Copyright (c) 2016 Bruno Lartillot
 * @license   https://github.com/p-pit/PpitCore/blob/master/license.txt GNU-GPL license
 */

namespace PpitLearning\Controller;

use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Model\Event;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
use PpitCore\Form\CsrfForm;
use PpitLearning\Model\TestResult;
use PpitLearning\Model\TestSession;
use PpitLearning\ViewHelper\SsmlTestResultViewHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TestResultController extends AbstractActionController
{
    public function indexAction()
    {
    	$context = Context::getCurrent();
    	$place = Place::get($context->getPlaceId());
    	
		$applicationId = 'p-pit-learning';
		$applicationName = 'Learning by 2Pit';
		$currentEntry = $this->params()->fromQuery('entry', 'place');

    	return new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getConfig(),
    			'place' => $place,
    			'active' => 'application',
    			'applicationId' => $applicationId,
    			'applicationName' => $applicationName,
    			'currentEntry' => $currentEntry,
    	));
    }

    public function getFilters($params)
    {
		$context = Context::getCurrent();
    	
    	// Retrieve the query parameters
    	$filters = array();

    	foreach ($context->getConfig('testResult/search')['main'] as $propertyId => $rendering) {
    
    		$property = ($params()->fromQuery($propertyId, null));
    		if ($property) $filters[$propertyId] = $property;
    		$min_property = ($params()->fromQuery('min_'.$propertyId, null));
    		if ($min_property) $filters['min_'.$propertyId] = $min_property;
    		$max_property = ($params()->fromQuery('max_'.$propertyId, null));
    		if ($max_property) $filters['max_'.$propertyId] = $max_property;
    	}
    	 
    	return $filters;
    }

    public function searchAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	// Return the link list
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
				'places' => Place::getList(array()),
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function getList()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	$params = $this->getFilters($this->params());
    	$major = ($this->params()->fromQuery('major', 'identifier'));
    	$dir = ($this->params()->fromQuery('dir', 'ASC'));

    	if (count($params) == 0) $mode = 'todo'; else $mode = 'search';

    	// Retrieve the list
    	$results = TestResult::getList($params, $major, $dir, $mode);

    	// Return the link list
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
				'places' => Place::getList(array()),
    			'results' => $results,
    			'mode' => $mode,
    			'params' => $params,
    			'major' => $major,
    			'dir' => $dir,
    	));
    	$view->setTerminal(true);
    	return $view;
    }
    
    public function listAction()
    {
    	return $this->getList();
    }
    
    public function exportAction()
    {
    	$view = $this->getList();

   		include 'public/PHPExcel_1/Classes/PHPExcel.php';
   		include 'public/PHPExcel_1/Classes/PHPExcel/Writer/Excel2007.php';

		$workbook = new \PHPExcel;
		(new SsmlTestResultViewHelper)->formatXls($workbook, $view);		
		$writer = new \PHPExcel_Writer_Excel2007($workbook);

		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition:inline;filename=2Pit_TestResults.xlsx ');
		$writer->save('php://output');
    	return $this->response;
    }

    public function detailAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) {
    		$result = TestResult::get($id);
    	}
    	else $result = TestResult::instanciate();

    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'id' => $id,
    			'result' => $result,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();

    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) {
    		$result = TestResult::get($id);
    		$vcard = Vcard::get($result->vcard_id);
    		if (!$vcard) $vcard = Vcard::instanciate();
    	}
    	else {
    		$result = TestResult::instanciate();
    		$vcard = Vcard::instanciate();
    	}

    	$action = $this->params()->fromRoute('act', null);

    	$learningSessions = TestSession::getList(array(), 'expected_time', 'ASC', 'search');

    	// Instanciate the csrf form
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	if ($action == 'delete') $message = 'confirm-delete';
    	elseif ($action) $message =  'confirm-update';
    	else $message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$message = null;
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		 
    		if ($csrfForm->isValid()) { // CSRF check
    			// Load the input data
		    	$data = array();
		    	$data['status'] = 'new';
		    	$data['actual_time'] = null;
		    	$data['answers'] = array();
		    	foreach($context->getConfig('testResult/update') as $propertyId => $unused) {
		    		$data[$propertyId] = $request->getPost(($propertyId));
		    	}
		    	if ($result->loadData($data) != 'OK') throw new \Exception('View error');
		    	unset($data['status']);
		    	if ($vcard->loadData($data) != 'OK') throw new \Exception('View error');

	    		// Atomically save
	    		$connection = TestResult::getTable()->getAdapter()->getDriver()->getConnection();
	    		$connection->beginTransaction();
	    		try {
	    			if ($result->vcard_id) {
		    			Event::getTable()->multipleDelete(array('type' => 'test_note', 'vcard_id' => $result->vcard_id));
		    			Event::getTable()->multipleDelete(array('type' => 'test_detail', 'vcard_id' => $result->vcard_id));
	    			}
	    			if (!$result->id) {
	    				$rc = $vcard->add();
	    				if ($rc == 'OK') {
				    		$result->authentication_token = md5(uniqid(rand(), true));
	    					$result->vcard_id = $vcard->id;
    						$result->n_title = $vcard->n_title;
    						$result->n_first = $vcard->n_first;
    						$result->n_last = $vcard->n_last;
    						$result->n_fn = $vcard->n_fn;
    						$result->email = $vcard->email;
    						$result->tel_cell = $vcard->tel_cell;
	    					$rc = $result->add();
	    				}
	    			}
	    			elseif ($action == 'delete') {
	    				$rc = $vcard->delete(null);
	    				if ($rc == 'OK') $rc = $result->delete($request->getPost('test_result_update_time'));
	    			}
	    			else {
	    				$rc = $vcard->update(null);
	    				if ($rc == 'OK') {
	    					$result->vcard_id = $vcard->id;
    						$result->n_title = $vcard->n_title;
    						$result->n_first = $vcard->n_first;
    						$result->n_last = $vcard->n_last;
    						$result->n_fn = $vcard->n_fn;
    						$result->email = $vcard->email;
    						$result->tel_cell = $vcard->tel_cell;
	    					$rc = $result->update($request->getPost('test_result_update_time'));
	    				}
	    			}
    				if ($rc != 'OK') $error = $rc;
	    			if ($error) $connection->rollback();
	    			else {
	    				$connection->commit();
	    				$message = 'OK';

						// Send the email to the user
	    				if ($action != 'delete') {
	    					$url = $context->getServiceManager()->get('viewhelpermanager')->get('url');
							$email_body = $context->getConfig('testResult/message')['subscribeText'][$context->getLocale()];
							$email_body = sprintf($email_body, 'https://'.$context->getInstance()->fqdn.$url('testResult/perform', array('id' => $result->id)).'?hash='.$result->authentication_token);
							$email_title = $context->getConfig('testResult/message')['subscribeTitle'][$context->getLocale()];
							Context::sendMail($result->email, $email_body, $email_title, null);
	    				}
	    			}
	    		}
	    		catch (\Exception $e) {
	    			$connection->rollback();
	    			throw $e;
	    		}
	    		$action = null;
    		}
	    }
    	$result->properties = $result->getProperties();
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
				'places' => Place::getList(array()),
    			'id' => $id,
    			'action' => $action,
    			'result' => $result,
    			'vcard' => $vcard,
    			'learningSessions' => $learningSessions,
    			'csrfForm' => $csrfForm,
    			'error' => $error,
    			'message' => $message
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function subscribeAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    
    	$test_session_id = (int) $this->params()->fromRoute('test_session_id', 0);
    	$learningSession = TestSession::getTable()->get($test_session_id);
    	if (!$learningSession) return $this->redirect()->toRoute('user/expired');

    	$result = TestResult::instanciate();
    
    	// Load the input data
    	$data = array();
   		$data['status'] = 'in_progress';
    	$data['test_session_id'] = $test_session_id;
    	$result->actual_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+ '.'60'.' seconds'));
    	if ($result->loadData($data) != 'OK') throw new \Exception('View error');
    
    	$rc = $result->add();
    	return $this->redirect()->toRoute('testResult/perform', array('id' => $result->id));
    }

    public function performAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	
    	$place = Place::get($context->getPlaceId());
    	
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $result = TestResult::get($id);
    	else $result = TestResult::instanciate();

    	$token = null;
		if ($result->authentication_token) {
	    	$token = $this->params()->fromQuery('hash', null);
	    	if ($token != $result->authentication_token) return $this->redirect()->toRoute('user/expired');
		}

    	if ($result->actual_time > date('Y-m-d H:i:s')) {
    		$result->status = 'new';
    	}

    	require_once "vendor/dropbox/dropbox-sdk/lib/Dropbox/autoload.php";
    	$dropbox = $context->getConfig('ppitDocument')['dropbox'];
    	if ($dropbox) $dropboxClient = new \Dropbox\Client($dropbox['credential'], $dropbox['clientIdentifier']);
    	else $dropboxClient = null;
    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;

    	$message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		 
    		if ($csrfForm->isValid()) { // CSRF check

    			// Load the input data
    			$data = array();
    			if ($request->getPost('action') == 'start') {
    				$result->actual_time = date('Y-m-d H:i:s');
    				$result->status = 'in_progress';
    				$result->update(null);
    			}
    			else {
	    			$answers = array();
					foreach ($result->testSession->test->getQuestions() as $questionId => $question) {
						if ($question['type'] == 'select') {
							foreach ($question['modalities'] as $modalityId => $modality) {
								if ($request->getPost($questionId.'-'.$modalityId)) {
									$answers[$questionId] = $modalityId;								
								}
							}
						}
						elseif ($question['type'] == 'phpCode') {
							$res = $request->getPost('result_'.$questionId);
							$proposition = $request->getPost('proposition_'.$questionId);
							$answers[$questionId] = array('result' => $res, 'proposition' => $proposition);
						}
					}
					$data['answers'] = $answers;
					if ($result->loadData($data) != 'OK') throw new \Exception('View error');

					if ($request->getPost('action') == 'save') $result->update(null);
					else {
								
		    			// Atomically save
		    			$connection = TestResult::getTable()->getAdapter()->getDriver()->getConnection();
		    			$connection->beginTransaction();
		    			try {
							$result->status = 'performed';
		    				if (!$result->id) $rc = $result->add();
		    				else $rc = $result->update(null);
		    				if ($rc != 'OK') $error = $rc;
		    				if ($error) $connection->rollback();
		    				else {
								$result->computeScores();

		    					// Generate the global result event
		    					$event = Event::instanciate();
		    					$event->status = 'new';
		    					$event->type = 'test_note';
		    					$event->identifier = $result->testSession->test->caption.'_'.$result->actual_time;
		    					$event->place_id = $result->place_id;
		    					$event->vcard_id = $result->vcard_id;
		    					$event->caption = $result->testSession->test->caption;
		    					reset($result->axes);
		    					$axis = current($result->axes);
		    					$event->value = $axis['score'];
		    					$event->property_1 = $result->testSession->expected_time;
		    					$event->property_2 = $result->status;
		    					$event->property_3 = $result->actual_time;
		    					$event->property_4 = $result->actual_duration;
		    					$event->property_5 = json_encode($result->answers);
		    					$event->property_7 = json_encode($axis['note']);
		    					
		    					$scorePerAxis = array();
		    					foreach ($result->axes as $axisId => &$axis) $scorePerAxis[$axisId] = array_key_exists('score', $axis) ? $axis['score'] : null;
		    					$event->property_6 = json_encode($scorePerAxis);
		    					$event->add();

		    					// Generate the detailed result events
		    					$event = Event::instanciate();
		    					$event->status = 'new';
		    					$event->type = 'test_detail';
		    					$event->place_id = $result->place_id;
		    					$event->vcard_id = $result->vcard_id;
			    				$event->property_1 = $result->testSession->expected_time;
			    				$event->property_2 = $result->status;
			    				$event->property_3 = $result->actual_time;
			    				$event->property_4 = $result->actual_duration;
			    				foreach ($result->answers as $answerId => $answer) {
			    					$question = $result->testSession->test->content['questions'][$answerId];
									$event->id = 0;
		    						$event->identifier = $result->testSession->test->caption.'_'.$result->n_fn.'_'.$answerId;
			    					$event->caption = $answerId;
			    					if ($question['type'] == 'select') $event->value = $question['modalities'][$answer]['value'];
			    					elseif ($question['type'] == 'phpCode') $event->value = ($answer['result'] == $question['result']) ? $question['value'] : 0;
			    					$event->property_5 = $answerId;
			    					$event->property_6 = substr(json_encode($question['label']), 0, 255);
			    					$event->property_7 = json_encode($answer);
			    					if ($question['type'] == 'select') $event->property_8 = json_encode($question['modalities'][$answer]['label']);
			    					elseif ($question['type'] == 'phpCode') $event->property_8 = json_encode($question['result']);

		    						reset($result->testSession->test->content['questions'][$answerId]['axes']);
		    						foreach(current($result->testSession->test->content['questions'][$answerId]['axes'])['categories'] as $categoryId => $category) {
				    					$event->property_9 = $categoryId;
				    					$event->property_10 = $category['weight'];
		    						}
			    					foreach(current($result->testSession->test->content['questions'][$answerId]['axes'])['categories'] as $categoryId => $category) {
				    					$event->property_11 = $categoryId;
				    					$event->property_12 = $category['weight'];
		    						}
		    						$event->add();
		    					}

		    					$connection->commit();
		    					$message = 'OK';
		    				}
		    			}
		    			catch (\Exception $e) {
		    				$connection->rollback();
		    				throw $e;
		    			}
					}
				}
    		}
    	}
    	 
    	$beginTime = ($result->testSession->expected_time) ? $result->testSession->expected_time : $result->actual_time;
    	$endTime = date('Y-m-d H:i:s', strtotime($beginTime.'+ '.$result->testSession->expected_duration.' seconds'));
    	
		$this->layout('/layout/public-layout');
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'token' => $token,
    			'place' => $place,
    			'id' => $id,
    			'dropboxClient' => $dropboxClient,
    			'result' => $result,
    			'beginTime' => $beginTime,
    			'endTime' => $endTime,
    			'message' => $message,
    			'error' => $error,
    	));
    	return $view;
    }
}
