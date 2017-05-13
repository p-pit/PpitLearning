<?php
/**
 * PpitCore V1.0 (https://github.com/p-pit/PpitCore)
 *
 * @link      https://github.com/p-pit/PpitCore
 * @copyright Copyright (c) 2016 Bruno Lartillot
 * @license   https://github.com/p-pit/PpitCore/blob/master/license.txt GNU-GPL license
 */

namespace PpitLearning\Controller;

use PpitLearning\Model\TestResult;
use PpitLearning\ViewHelper\SsmlTestResultViewHelper;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TestResultController extends AbstractActionController
{
    public function indexAction()
    {
    	$context = Context::getCurrent();

		$applicationId = 'p-pit-learning';
		$applicationName = 'Learning by 2Pit';
		$currentEntry = $this->params()->fromQuery('entry', 'place');

    	return new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getConfig(),
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
    	if ($id) $result = TestResult::get($id);
    	else $result = TestResult::instanciate();
    	$action = $this->params()->fromRoute('act', null);
    	 
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
		    	foreach($context->getConfig('testResult/update') as $propertyId => $unused) {
		    		$data[$propertyId] = $request->getPost(($propertyId));
		    	}
				if ($result->loadData($data) != 'OK') throw new \Exception('View error');

	    		// Atomically save
	    		$connection = TestResult::getTable()->getAdapter()->getDriver()->getConnection();
	    		$connection->beginTransaction();
	    		try {
	    			if (!$result->id) $rc = $result->add();
	    			elseif ($action == 'delete') $rc = $result->delete($request->getPost('test_result_update_time'));
	    			else $rc = $result->update($request->getPost('test_result_update_time'));
    				if ($rc != 'OK') $error = $rc;
	    			if ($error) $connection->rollback();
	    			else {
	    				$connection->commit();
	    				$message = 'OK';
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
    			'id' => $id,
    			'action' => $action,
    			'result' => $result,
    			'csrfForm' => $csrfForm,
    			'error' => $error,
    			'message' => $message
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function performAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	 
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if ($id) $result = TestResult::get($id);
    	else $result = TestResult::instanciate();
    	$beginTime = $result->testSession->expected_time;
    	$endTime = date('Y-m-d H:i:s', strtotime($result->testSession->expected_time.'+ '.$result->testSession->expected_duration.' seconds'));

    	$csrfForm = new CsrfForm();
    	$csrfForm->addCsrfElement('csrf');
    	$error = null;
    	if ($result->testSession->expected_time > date('Y-m-d H:i:s')) $error = 'Not begun';
    	if ($endTime < date('Y-m-d H:i:s')) $error = 'Ended';
    	$message = null;
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
    		$csrfForm->setData($request->getPost());
    		 
    		if ($csrfForm->isValid()) { // CSRF check

    			// Load the input data
    			$data = array();
    			if ($request->getPost('action') == 'submit') $data['status'] = 'performed';
    			$answers = array();
				foreach ($result->testSession->test->content['parts'] as $partId => $part) {
					if ($part['type'] == 'select') {
						foreach ($part['modalities'] as $modalityId => $modality) {
							if ($request->getPost($partId.'-'.$modalityId)) {
								$answers[$partId] = $modalityId;								
							}
						}
					}
				}
				$data['answers'] = $answers;
				if ($result->loadData($data) != 'OK') throw new \Exception('View error');
    	
    			// Atomically save
    			$connection = TestResult::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
    				if (!$result->id) $rc = $result->add();
    				else $rc = $result->update(null);
    				if ($rc != 'OK') $error = $rc;
    				if ($error) $connection->rollback();
    				else {
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
    	 
    	$view = new ViewModel(array(
    			'context' => $context,
    			'config' => $context->getconfig(),
    			'id' => $id,
    			'result' => $result,
    			'beginTime' => $beginTime,
    			'endTime' => $endTime,
    			'message' => $message,
    			'error' => $error,
    	));
    	return $view;
    }
}
