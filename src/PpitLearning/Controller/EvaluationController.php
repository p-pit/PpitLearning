<?php
namespace PpitLearning\Controller;

use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use PpitLearning\Model\Evaluation;
use PpitStudies\Model\Student;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class EvaluationController extends AbstractActionController
{
    public function learnerIndexAction()
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();

    	$major = $this->params()->fromQuery('major', 'nom_famille');
    	$dir = $this->params()->fromQuery('dir', 'ASC');
    	$mode = $this->params()->fromQuery('mode', 'total');
    	$eleves = Student::getList(array(), $major, $dir, 'todo');

        // Affichage de la vue avec page d'élèves et nom du centre en paramètre
        return new ViewModel(array(
    		'context' => $context,
			'config' => $context->getconfig(),
        	'eleves' => $eleves,
        	'major' => $major,
    		'dir' => $dir,
    		'mode' => $mode,
        ));
   	}

    public function indexAction()
    {
    	$learner_id = (int) $this->params()->fromRoute('learner_id', 0);
        if (!$learner_id) return $this->redirect()->toRoute('index');

        // Retrieve the context
    	$context = Context::getCurrent();
        
        // Retrieve the learner
        $learner = Student::getTable()->get($learner_id);

		// Retrieve the evaluations for this learner
    	$major = $this->params()->fromQuery('major', 'period');
    	$dir = $this->params()->fromQuery('dir', 'ASC');
    	$evaluations = Evaluation::getList($major, $dir, $learner_id);

        return new ViewModel(array(
        	'context' => $context,
			'config' => $context->getconfig(),
        	'user_type' => $currentUser->type,
			'learner_id' => $learner_id,
        	'learner' => $learner,
    		'evaluations' => $evaluations,
    	));
	}
    
    public function updateAction()
    {
        $learner_id = (int) $this->params()->fromRoute('learner_id', 0);
        if (!$learner_id) {    		
    		return $this->redirect()->toRoute('index');
    	}

        // Retrieve the context
    	$context = Context::getCurrent();

        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id) $evaluation = Evaluation::getTable()->get($id);
        else {
        	$evaluation = new Evaluation;
        	$evaluation->learner_id = $learner_id;
        }

        // Retrieve the learner
        $learner = Student::getTable()->get($learner_id);

        // Retrieve the documents
        $documents = array();
        foreach (Context::getDocumentTable()->fetchAll() as $document) {
        	if ($document->espace == "Etudes") $documents[] = $document;
        }

        // Instanciate the csrf form
        $csrfForm = new CsrfForm();
        $csrfForm->addCsrfElement('csrf');
        $error = null;
        $message = null;
        $request = $this->getRequest();
        if ($request->isPost()) {
        	$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
        	$csrfForm->setData($request->getPost());
        	
        	if ($csrfForm->isValid()) { // CSRF check

        		// Load the data from the request
        		$data = array();
        		$data['period'] = $request->getPost('period');
        		$data['subject'] = $request->getPost('subject');
        		$data['note'] = $request->getPost('note');
        		$data['learner_average'] = $request->getPost('learner_average');
        		$data['class_average'] = $request->getPost('class_average');
        		$data['coefficient'] = $request->getPost('coefficient');
        		$data['lower_note'] = $request->getPost('lower_note');
        		$data['higher_note'] = $request->getPost('higher_note');
        		$data['number_of_notes'] = $request->getPost('number_of_notes');
        		$data['appreciation'] = $request->getPost('appreciation');
        		$data['dis_note'] = $request->getPost('dis_note');
        		$data['dis_appreciation'] = $request->getPost('dis_appreciation');
        		$data['piece_jointe'] = $request->getPost('piece_jointe');
        		if (!$evaluation->loadData($data)) {
	        		
	        		Evaluation::getTable()->save($evaluation);
					$message = 'OK';
        		}
        		else throw new \Exception('View error');
            }
        }
        return array(
        	'context' => $context,
			'config' => $context->getconfig(),
        	'user_type' => $currentUser->type,
        	'learner_id' => $learner_id,
    		'id' => $id,
        	'learner' => $learner,
        	'evaluation' => $evaluation,
        	'documents' => $documents,
 	      	'csrfForm' => $csrfForm,
        	'error' => $error,
        	'message' => $message,
        );
    }

    public function deleteAction()
    {
        $learner_id = (int) $this->params()->fromRoute('learner_id', 0);
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$learner_id && !$id) {
    		return $this->redirect()->toRoute('index');
    	}

        // Retrieve the current user
        $context = Context::getCurrent();

        // Retrieve the evaluation to delete
        $evaluation = Evaluation::getTable()->get($id);

        // Retrieve the learner
        $learner = Student::getTable()->get($learner_id);
        
        // Instanciate the csrf form
        $csrfForm = new CsrfForm();
        $csrfForm->addCsrfElement('csrf');
        $error = null;
        $message = null;
        $request = $this->getRequest();
        if ($request->isPost()) {
        	$csrfForm->setInputFilter((new Csrf('csrf'))->getInputFilter());
        	$csrfForm->setData($request->getPost());
        	
        	if ($csrfForm->isValid()) { // CSRF check

        		Evaluation::getTable()->delete($id);
				$message = 'OK';
            }
        }
        return array(
    		'context' => $context,
			'config' => $context->getconfig(),
        	'learner_id' => $learner_id,
    		'id' => $id,
        	'learner' => $learner,
        	'evaluation' => $evaluation,
 	      	'csrfForm' => $csrfForm,
        	'error' => $error,
        	'message' => $message,
        );
    }

    public function exportAction()
    {
    	// Retrieve the current user
    	$context = Context::getCurrent();

    	// Retrieve the evaluations for this learner
    	$major = $this->params()->fromQuery('major', 'period');
    	$dir = $this->params()->fromQuery('dir', 'ASC');
    	$evaluations = Evaluation::getList($major, $dir);

    	$view = new ViewModel(array(
    			'context' => $context,
				'config' => $context->getconfig(),
    			'evaluations' => $evaluations,
    			'learnerTable' => $this->getStudentTable()
    	));
    	$view->setTerminal(true);
    	return $view;
    }
}
