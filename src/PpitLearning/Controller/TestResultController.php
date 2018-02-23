<?php
/**
 * PpitCore V1.0 (https://github.com/p-pit/PpitCore)
 *
 * @link      https://github.com/p-pit/PpitCore
 * @copyright Copyright (c) 2016 Bruno Lartillot
 * @license   https://github.com/p-pit/PpitCore/blob/master/license.txt GNU-GPL license
 */

namespace PpitLearning\Controller;

use PpitContact\Model\ContactMessage;
use PpitCore\Model\Account;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Model\Event;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
use PpitCore\Form\CsrfForm;
use PpitLearning\Model\Test;
use PpitLearning\Model\TestResult;
use PpitLearning\Model\TestSession;
use PpitLearning\ViewHelper\SsmlTestResultViewHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class TestResultController extends AbstractActionController
{
	public function getConfigProperties($type) {
		$context = Context::getCurrent();
		$properties = array();
		foreach($context->getConfig('testResult/'.$type)['properties'] as $propertyId => $property) {
			if ($property['definition'] != 'inline') $property = $context->getConfig($property['definition']);
			$properties[$propertyId] = $property;
		}
		return $properties;
	}
	
	public function getListProperties($type) {
		foreach ($this->getConfigProperties($type) as $propertyId => $property) {
			if ($property['type'] == 'list') {
				if ($propertyId == 'place_id') $lists['place_id'] = Place::getList(array());
				elseif ($propertyId == 'test_id') $lists['test_id'] = Test::getList(array());
				elseif ($propertyId == 'test_session_id') $lists['test_session_id'] = TestSession::getList(array());
			}
		}
		return $lists;
	}

	public function indexAction()
    {
    	$context = Context::getCurrent();
    	$place = Place::get($context->getPlaceId());
    	
		$applicationId = 'p-pit-learning';
		$applicationName = 'Learning by 2Pit';
		$currentEntry = $this->params()->fromQuery('entry', 'place');

		$type = $this->params()->fromRoute('type', '');
		$personnalize = ($this->params()->fromQuery('personnalize'));

		$configProperties = $this->getConfigProperties($type);
		return new ViewModel(array(
    			'context' => $context,
    			'type' => $type,
    			'place' => $place,
    			'applicationId' => $applicationId,
				'personnalize' => $personnalize,
				'page' => $context->getConfig('testResult/index/'.$type),
				'searchPage' => $context->getConfig('testResult/search/'.$type),
				'listPage' => $context->getConfig('testResult/list/'.$type),
				'detailPage' => $context->getConfig('testResult/detail/'.$type),
				'updatePage' => $context->getConfig('testResult/update/'.$type),
				'configProperties' => $configProperties,
				'major' => 'expected_date',
				'dir' => 'DESC',
		));
    }

    public function getFilters($type, $params)
    {
		$context = Context::getCurrent();
    	
    	// Retrieve the query parameters
    	$filters = array();

    	foreach ($context->getConfig('testResult/search/'.$type)['properties'] as $propertyId => $unused) {
    
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
    	$type = $this->params()->fromRoute('type', '');
		$configProperties = $this->getConfigProperties($type);
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
				'page' => $context->getConfig('testResult/search/'.$type),
    			'configProperties' => $configProperties,
    			'lists' => $this->getListProperties($type),
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function getList()
    {
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', '');
    	$params = $this->getFilters($type, $this->params());
    	$major = ($this->params()->fromQuery('major', 'expected_date'));
    	$dir = ($this->params()->fromQuery('dir', 'DESC'));
    	if (count($params) == 0) $mode = 'todo'; else $mode = 'search';
    	$results = TestResult::getList($type, $params, $major, $dir, $mode);
    	$configProperties = $this->getConfigProperties($type);
    	 
    	// Aggregate
    	$sum = 0;
    	$distribution = array();
    	foreach ($results as $result) {
    		$majorProperty = $configProperties[$major];
    		if ($majorProperty['type'] == 'number') $sum += $result->properties[$major];
    		elseif ($majorProperty['type'] == 'select') {
    			if (array_key_exists($result->properties[$major], $distribution)) $distribution[$result->properties[$major]]++;
    			else $distribution[$result->properties[$major]] = 1;
    		}
    	}
    	$average = (count($results)) ? round($sum / count($results), 1) : null;
    	 
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
				'page' => $context->getConfig('testResult/list/'.$type),
    			'results' => $results,
    			'distribution' => $distribution,
    			'count' => count($results),
    			'sum' => $sum,
    			'average' => $average,
    			'mode' => $mode,
    			'params' => $params,
    			'major' => $major,
    			'dir' => $dir,
				'configProperties' => $configProperties,
    			'lists' => $this->getListProperties($type),
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

    public function distributeAction()
    {
    	$distribution = array();
    	foreach ($this->getList()->results as $result) {
    		if (array_key_exists($result->caption, $distribution)) $distribution[$result->caption]++;
    		else $distribution[$result->caption] = 1;
    	}
    	$colors = array('#F7464A', '#46BFBD', '#FDB45C', '#4D5360');
    	$highlights = array('#FF5A5E', '#5AD3D1', '#FFC870', '#616774');
    	$data = array();
    	$i=0;
    	foreach ($distribution as $value => $number) {
    		$data[] = array(
    				'value' => $number,
    				'color' => $colors[$i % 4],
    				'highlight' => $highlights[$i % 4],
    				'label' => $value,
    		);
    		$i++;
    	}
    	return new JsonModel($data);
    }
    
    public function detailAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', '');
    	 
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) {
    		$result = TestResult::get($id);
    	}
    	else $result = TestResult::instanciate();

    	$configProperties = $this->getConfigProperties($type);
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'type' => $type,
				'page' => $context->getConfig('testResult/detail/'.$type),
    			'id' => $id,
    			'result' => $result,
				'configProperties' => $configProperties,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function updateAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', '');
    	 
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) {
    		$result = TestResult::get($id);
    		$account = Account::get($result->account_id);
    		if ($account) $vcard = Vcard::get($account->contact_1_id);
    		else {
    			$account = Account::instanciate('generic');
    			$vcard = Vcard::instanciate();
    		}
    		
    	}
    	else {
    		$result = TestResult::instanciate();
    		$vcard = Vcard::instanciate();
    		$account = Account::instanciate('generic');
    	}
    	$action = $this->params()->fromRoute('act', null);
    	$learningSessions = TestSession::getList(array(/*'status' => 'new'*/), 'expected_date', 'ASC', 'search');
    	$configProperties = $this->getConfigProperties($type);
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
    			$data = array();
    			if ($action == 'delete') {
    				$data['contact_history'] = 'Annulation du test '.$result->testSession->caption;
					if ($account->loadData($account->type, $data) != 'OK') throw new \Exception('View error');
    			}
    			else {
    			
    				// Load the input data
			    	$data['status'] = 'new';
			    	$data['actual_time'] = null;
			    	$data['answers'] = array();
			    	foreach($context->getConfig('testResult/update/'.$type)['properties'] as $propertyId => $unused) {
			    		$data[$propertyId] = $request->getPost(($propertyId));
			    	}
			    	if ($result->loadData($data) != 'OK') throw new \Exception('View error');
			    	unset($data['status']);
			    	if ($vcard->loadData($data) != 'OK') throw new \Exception('View error');
			    	$data['status'] = 'active';
			    	unset($data['test_session_id']);
			    	unset($data['actual_time']);
			    	unset($data['answers']);
			    	$data['contact_history'] = 'Inscription au test '.$result->caption;
			    	if ($account->loadData($account->type, $data) != 'OK') throw new \Exception('View error');
	    			if (!$account->name) $account->name = $account->contact_1->n_fn;
    			}

	    		// Atomically save
	    		$connection = TestResult::getTable()->getAdapter()->getDriver()->getConnection();
	    		$connection->beginTransaction();
	    		try {
	    			if ($result->account_id) {
		    			Event::getTable()->multipleDelete(array('type' => 'test_note', 'account_id' => $result->account_id));
		    			Event::getTable()->multipleDelete(array('type' => 'test_detail', 'account_id' => $result->account_id));
	    			}
	    			if (!$result->id) {
	    				$rc = $vcard->add();
	    				$account->contact_1_id = $vcard->id;
	    				$rc = $account->add();
						$result->authentication_token = md5(uniqid(rand(), true));
						$result->account_id = $account->id;
						$result->n_title = $vcard->n_title;
						$result->n_first = $vcard->n_first;
						$result->n_last = $vcard->n_last;
						$result->n_fn = $vcard->n_fn;
						$result->email = $vcard->email;
						$result->tel_cell = $vcard->tel_cell;
						$rc = $result->add();
						$result_id = $result->id;
						$resultIds = array();
						$session = TestSession::get($result->test_session_id);
						while ($rc =='OK' && $session->next_session_id) {
							$session = TestSession::get($session->next_session_id);
							$result->test_session_id = $session->id;
							$rc = $result->add();
							$resultIds[] = $result->id;
						}
						$result = TestResult::get($result_id);
						foreach ($resultIds as $id) {
							$result->next_result_id = $id;
							$result->update(null);
							$result = TestResult::get($id);
						}
	    			}
	    			elseif ($action == 'delete') {
	    				$rc = $result->delete($request->getPost('test_result_update_time'));
	    				$rc = $account->update(null);
	    			}
	    			else {
	    				$rc = $vcard->update(null);
	    				$rc = $account->update(null);
						$result->account_id = $account->id;
						$result->n_title = $account->contact_1->n_title;
						$result->n_first = $account->contact_1->n_first;
						$result->n_last = $account->contact_1->n_last;
						$result->n_fn = $account->contact_1->n_fn;
						$result->email = $account->contact_1->email;
						$result->tel_cell = $account->contact_1->tel_cell;
						$rc = $result->update($request->getPost('test_result_update_time'));
						$result_id = $result->id;
	    			}
    				if ($rc != 'OK') $error = $rc;
	    			if ($error) $connection->rollback();
	    			else {
	    				$message = 'OK';

						// Send the email to the user
	    				if ($action != 'delete') {
	    					$url = $context->getServiceManager()->get('viewhelpermanager')->get('url');
	    					if (array_key_exists('email_template', $result->testSession->test)) {
	    						$template = $result->testSession->test['email_template'];
	    					}
	    					else {
	    						$template = $context->getConfig('testResult/message/'.$type);
	    					}
							$email = ContactMessage::instanciate($type);
							$email->from_mail = $template['from_mail'];
							$email->from_name = (array_key_exists('from_name', $template)) ? $template['from_name'] : $template['from_mail'];
							$email->to = array($result->email => null);
							if (array_key_exists('cc', $template)) $email->cc = $template['cc'];
							if (array_key_exists('cci', $template)) $email->cci = $template['cci'];
							$email->subject = $context->localize($template['subscribeTitle']);
							$email_body = $template['subscribeText'][$context->getLocale()];
							$email->body = sprintf($email_body, 'https://'.$context->getInstance()->fqdn.$url('testResult/perform', array('type' => $type, 'id' => $result_id)).'?hash='.$result->authentication_token);
							$email->sendHtmlMail();
	    				}
	    				$connection->commit();
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
    			'type' => $type,
				'page' => $context->getConfig('testResult/update/'.$type),
    			'id' => $id,
    			'action' => $action,
    			'result' => $result,
    			'learningSessions' => $learningSessions,
    			'csrfForm' => $csrfForm,
    			'error' => $error,
    			'message' => $message,
				'configProperties' => $configProperties,
    			'lists' => $this->getListProperties($type),
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function subscribeAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', '');
    	 
    	$test_session_id = (int) $this->params()->fromRoute('test_session_id', 0);
    	$learningSession = TestSession::getTable()->get($test_session_id);
    	if (!$learningSession) return $this->redirect()->toRoute('user/expired');

    	$result = TestResult::instanciate();
    
    	// Load the input data
    	$data = array();
   		$data['status'] = 'in_progress';
    	$data['test_session_id'] = $test_session_id;
    	$actual_datetime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+ '.'60'.' seconds'));
    	$result->actual_date = substr($actual_datetime, 0, 10);
    	$result->actual_time = substr($actual_datetime, 11, 8);
    	if ($result->loadData($data) != 'OK') throw new \Exception('View error');
    
    	$rc = $result->add();
    	return $this->redirect()->toRoute('testResult/perform', array('type' => $type, 'id' => $result->id));
    }

    public function performAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	$type = $this->params()->fromRoute('type', '');

    	$place = Place::get($context->getPlaceId());
    	
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $result = TestResult::get($id);
    	else $result = TestResult::instanciate();
    	$configProperties = $this->getConfigProperties($type);

    	// Shift to the first result in the chain not already performed
    	while($result->status == 'performed' && $result->next_result_id) $result = TestResult::get($result->next_result_id);

    	$part = $result->testSession->test->getParts()[$result->testSession->part_identifier];
		$token = null;
		if ($result->authentication_token) {
	    	$token = $this->params()->fromQuery('hash', null);
	    	if ($token != $result->authentication_token) return $this->redirect()->toRoute('user/expired');
		}

    	if ($result->actual_date.' '.$result->actual_time > date('Y-m-d H:i:s')) {
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
    				$result->actual_date = date('Y-m-d');
    				$result->actual_time = date('H:i:s');
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
		    				else {
		    					$rc = $result->update(null);
		    				    $session = TestSession::get($result->test_session_id);
		    					if ($rc =='OK' && $result->status == 'performed' && $session->next_session_id) {
		    						$nextResult = TestResult::get($result->next_result_id);
		    						if ($nextResult->status == 'new') {
					    				$nextResult->actual_date = date('Y-m-d');
					    				$nextResult->actual_time = date('H:i:s');
		    							$nextResult->status = 'in_progress';
		    							$nextResult->update(null);
		    						}
		    					}
		    				}
		    				if ($rc != 'OK') $error = $rc;
		    				if ($error) $connection->rollback();
		    				else {
								$result->computeScores();

		    					// Generate the per-axis result event
		    					foreach ($result->axes as $axisId => $axis) {
		    						foreach ($axis['categories'] as $categoryId => $category) {
		    							if (array_key_exists('score', $category)) {

		    								// Retrieve a possibly existing note event
		    								$event = Event::get($result->testSession->test->caption.'_'.$result->account_id.'_'.$categoryId, 'identifier');
		    								if ($event) {
		    									$event->answers = json_decode($event->property_6, true);
		    								}
		    								else {
						    					$event = Event::instanciate();
						    					$event->status = 'new';
						    					$event->type = 'test_note';
						    					$event->identifier = $result->testSession->test->caption.'_'.$result->account_id.'_'.$categoryId;
						    					$event->place_id = $result->place_id;
						    					$event->account_id = $result->account_id;
						    					$event->vcard_id = $result->vcard_id;
						    					$event->n_fn = $result->n_fn;
						    					$event->caption = $result->testSession->test->caption;
		    									$event->value = 0;
						    					$event->property_1 = $axisId;
						    					$event->property_2 = $categoryId;
						    					$event->property_3 = $result->testSession->expected_date.' '.$result->testSession->expected_time;
						    					$event->property_4 = $result->actual_date.' '.$result->actual_time;
						    					$event->property_5 = $result->actual_duration;
						    					$event->answers = array();
						    					$event->property_7 = $context->localize($axis['note']['label']);
		    								}
		    								$event->value += $category['score'];
		    								$event->answers = array_merge($event->answers, $category['answers']);
						    				$event->property_6 = json_encode($event->answers);
		    								if (!$event->id) $event->add();
						    				else $event->update(null);
		    							}
		    						}
		    					}

		    					// Generate the detailed result events
		    					$event = Event::instanciate();
		    					$event->status = 'new';
		    					$event->type = 'test_detail';
		    					$event->place_id = $result->place_id;
		    					$event->account_id = $result->account_id;
		    					$event->vcard_id = $result->vcard_id; // Deprecated
		    					$event->n_fn = $result->n_fn;
		    					$event->property_1 = $result->testSession->expected_date.' '.$result->testSession->expected_time;
			    				$event->property_2 = $result->status;
			    				$event->property_3 = $result->actual_date.' '.$result->actual_time;
			    				$event->property_4 = $result->actual_duration;
			    				foreach ($result->answers as $answerId => $answer) {
			    					$question = $result->testSession->test->content['questions'][$answerId];
									$event->id = 0;
		    						$event->identifier = $result->testSession->test->caption.'_'.$result->n_fn.'_'.$answerId;
			    					$event->caption = $answerId;
			    					$event->value = 1;
			    					$event->property_5 = $result->testSession->test->caption;
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
		    					return $this->redirect()->toRoute('testResult/perform', array('type' => $type, 'id' => $id), array('query' => array('hash' => $token)));
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

    	$beginTime = ($result->testSession->expected_time) ? $result->testSession->expected_date.' '.$result->testSession->expected_time : $result->actual_date.' '.$result->actual_time;
    	$endTime = date('Y-m-d H:i:s', strtotime($beginTime.'+ '.$result->testSession->expected_duration.' seconds'));
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'template' => array(),
    			'type' => $type,
    			'token' => $token,
    			'place' => $place,
    			'id' => $id,
    			'dropboxClient' => $dropboxClient,
    			'result' => $result,
    			'part' => $part,
    			'beginTime' => $beginTime,
    			'endTime' => $endTime,
    			'message' => $message,
    			'error' => $error,
				'configProperties' => $configProperties,
    	));
    	$view->setTerminal(true);
    	return $view;
    }
}
