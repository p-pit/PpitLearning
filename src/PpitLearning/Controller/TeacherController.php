<?php

namespace PpitLearning\Controller;

use PpitCommitment\Model\Commitment;
use PpitCommitment\Model\Notification;
use PpitCommitment\ViewHelper\CommitmentMessageViewHelper;
use PpitCommitment\ViewHelper\SsmlAccountViewHelper;
use PpitCore\Form\CsrfForm;
use PpitCore\Model\Account;
use PpitCore\Model\Community;
use PpitCore\Model\Context;
use PpitCore\Model\Credit;
use PpitCore\Model\Csrf;
use PpitCore\Model\Document;
use PpitCore\Model\Event;
use PpitCore\Model\Instance;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
use PpitCore\ViewHelper\EventPlanningViewHelper;
use PpitCommitment\ViewHelper\PpitPDF;
use PpitStudies\Model\Absence;
use PpitStudies\Model\Note;
use PpitStudies\Model\NoteLink;
use PpitStudies\Model\Progress;
use PpitStudies\Model\StudentSportImport;
use PpitStudies\ViewHelper\DocumentTemplate;
use PpitStudies\ViewHelper\PdfReportTableViewHelper;
use PpitStudies\ViewHelper\PdfReportViewHelper;
use Zend\Db\Sql\Where;
use Zend\Http\Client;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TeacherController extends AbstractActionController
{
    public function homeAction()
    {
    	// Retrieve context and parameters
    	$context = Context::getCurrent();
    	$account_id = (int) $this->params()->fromRoute('account_id');

    	$profile = null;
    	if ($account_id) $profile = Account::get($account_id);
    	else {
    		$candidates = Account::getList('p-pit-studies', ['contact_1_id' => $context->getContactId()]);
    		foreach ($candidates as $candidate) if ($candidate->status != 'gone') {
    			$profile = $candidate;
    			break;
    		}
    	}
    	if (!$profile) return $this->redirect()->toRoute('home');
    	$place = Place::get($profile->place_id);
    	$template = $context->getConfig('teacher/home/tabs');
    	$logo = ($place->logo_src) ? $place->logo_src : '/logos/'.$context->getInstance()->caption.'/'.$context->getConfig('headerParams')['logo'];
    	$logo_height = ($place->logo_src) ? $place->logo_height : $context->getConfig('headerParams')['logo-height'];
    	$configProperties = Account::getConfig('p-pit-studies');
    
    	// Authentication
    	$panel = $this->params()->fromQuery('panel');
    	$email = $this->params()->fromQuery('email');
    	$error = $this->params()->fromQuery('error');
    	$message = $this->params()->fromQuery('message');
    	$redirect = $this->params()->fromQuery('redirect', 'home');
    	if ($email && !$context->isAuthenticated()) {
    		$vcard = Vcard::get($email, 'email');
    		$profile->email = $email;
    		if ($vcard) {
    			$userContact = UserContact::get($vcard->id, 'vcard_id');
    			if ($userContact) $panel = 'modalLoginForm';
    			$profile->n_first = $vcard->n_first;
    			$profile->n_last = $vcard->n_last;
    		}
    		else {
    			$profile->n_first = $this->params()->fromQuery('n_first');
    			$profile->n_last = $this->params()->fromQuery('n_last');
    		}
    		if ($panel != 'modalLoginForm') {
    			$panel = 'modalRegisterForm';
    		}
    	}
    
    	// Retrieve the global average if exists
    	$current_school_year = $context->getConfig('student/property/school_year/default');
    	$school_periods = $place->getConfig('school_periods');
    	$current_school_period = $context->getCurrentPeriod($school_periods);
    	$cursor = NoteLink::getList('report', ['category' => 'evaluation', 'subject' => 'global', 'school_year' => $current_school_year, 'school_period' => $current_school_period, 'account_id' => $profile->id], 'id', 'ASC', $mode = 'search');
    	foreach ($cursor as $report) $averageNote = $report; // Should be unique but to keep only the last one
    	$global_average = (isset($averageNote) && $averageNote) ? $averageNote->value : null;
    
    	$view = new ViewModel(array(
    		'context' => $context,
    		'place_identifier' => $place->identifier,
    		'place' => $place,
    		'profile' => $profile,
    		'global_average' => $global_average,
    		'requestUri' => $this->request->getRequestUri(),
    		'viewController' => 'ppit-learning/teacher/home-scripts.phtml',
    		'configProperties' => $configProperties,
    		'groups' => Account::getList('group', [], '+name', null),
    
    		'template' => $template,
    		'logo' => $logo,
    		'logo_height' => $logo_height,
    
    		'token' => $this->params()->fromQuery('hash', null),
    		'panel' => $panel,
    		'email' => $email,
    		'redirect' => $redirect,
    		'message' => $message,
    		'error' => $error,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function calendarAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    
    	$account_id = (int) $this->params()->fromRoute('id');
    	$account = Account::get($account_id);
    
    	// Return the link list
    	$view = new ViewModel(array(
    		'context' => $context,
    	));
    	$view->setTerminal(true);
    	return $view;
    }
    
    public function planningAction()
    {
    	$context = Context::getCurrent();
    	$eventConfig = Event::getConfigProperties('calendar');
    	$id = (int) $this->params()->fromRoute('id', 0);
    	$date = $this->params()->fromQuery('date');
    	$grouped = $this->params()->fromQuery('grouped');
    	$event = Event::get($id);

    	$view = new ViewModel(array(
    		'context' => $context,
    		'eventConfig' => $eventConfig,
    		'id' => $id,
    		'date' => $date,
    		'grouped' => $grouped,
    		'event' => $event,
    	));
    	$view->setTerminal(true);
    	return $view;
    }

    public function documentAction()
    {
    	$context = Context::getCurrent();
    	$content = [
    		'context' => $context,
    	];
		$account_id = $this->params()->fromQuery('account_id');

		$select = Document::getSelect('binary', [], ['folder' => ['eq', 'commitments'], 'account_id' => ['eq', $account_id]], ['-update_time'], null);
		$documents = Document::getTable()->selectWith($select);
		
		$content['documents'] = [];
		foreach ($documents as $document) {
			$data = $document->getProperties();
			$content['documents'][$document->id] = $data;
			$content['documents'][$document->id]['is_deletable'] = $document->isDeletable();
		}
    	
    	// Return the link list
    	$view = new ViewModel($content);
    	$view->setTerminal(true);
    	return $view;
    }

    public function getHomeworkAction()
    {
    	// Retrieve the context
    	$context = Context::getCurrent();
    	$id = $this->params()->fromRoute('id');
    
    	$teacher_id = (int) $this->params()->fromQuery('teacher_id');
    
    	$notes = Note::GetList('homework', null, ['teacher_id' => $teacher_id], 'date', 'DESC', 'search', null);
    	 
    	echo json_encode($notes, JSON_PRETTY_PRINT);
    	return $this->getResponse();
    }
    
	public function homeworkAction()
	{
		$context = Context::getCurrent();
		if ($this->getRequest()->isPost()) $requestType = 'POST';
		elseif ($this->getRequest()->isDelete()) $requestType = 'DELETE';
		else $requestType = 'GET';
		
		$type = $this->params()->fromRoute('type');
		$id = $this->params()->fromRoute('id');
		$groups = $this->params()->fromQuery('groups');
		$groups = explode(',', $groups);
		$group_id = $groups[0];
		$group = Account::get($group_id);
		$account_id = $this->params()->fromQuery('account_id');
		$subject = $this->params()->fromQuery('subject');
		$date = $this->params()->fromQuery('date');
		
		if ($id) $homework = Note::get($id);
		else {
			$note = Note::instanciate($type, null);
			$note->teacher_id = $account_id;
			$note->group_id = $group_id;
			$note->subject = $subject;
			$note->date = $note->target_date = ($date) ? $date : date('Y-m-d');
		}
		$content = [
			'context' => $context,
			'requestType' => $requestType,
			'type' => $type,
			'id' => $id,
			'group' => $group->name,
			'note' => $note->getProperties(),
		];

		$statusCode = '200';
		if ($this->getRequest()->isPost()) {
			$note->date = $note->target_date = $context->encodeDate($this->getRequest()->getPost('target_date'));
			$note->observations = $this->getRequest()->getPost('observations');
			$note->document = $this->getRequest()->getPost('document');
			if ($note->observations) {
				if (!$id) $note->add();
				else $note->update(null);
			}
		}
		
		$content['statusCode'] = $statusCode;

		// Return the link list
		$view = new ViewModel($content);
		$view->setTerminal(true);
		return $view;
	}
    
	public function homeworkDocumentAction()
	{
		$view = $this->documentAction();
		$type = $this->params()->fromRoute('type');
		$view->type = $type;
		return $view;
	}

	public function homeworkDetailAction()
	{
		$context = Context::getCurrent();
		$id = $this->params()->fromRoute('id');
		$homework = Note::get($id);
		
		$view = new ViewModel($homework->getProperties());
		$view->context = $context;
		$view->setTerminal(true);
		return $view;
	}
	
    /**
     * user_story - event-attendees: The attendees of a calendar event are the accounts belonging to the event's class (if any) union the accounts linked to one of the event's groups
     */
    public static function getAttendees($event)
    {
    	$groups = Account::getList('group', [], '+name', null);
    	$groupIds = $event->groups;
    	if ($groupIds) $groupIds = explode(',', $groupIds);
    
    	$attendees = [];
    	$filters['status'] = 'active,retention';
    	if ($event->property_2) $filters['property_7'] = $event->property_2;
    	$cursor = Account::getList('p-pit-studies', $filters, '+n_fn', null);
    
    	if (!$groupIds) foreach ($cursor as $account_id => $account) $attendees[$account_id] = ['n_fn' => $account->n_fn, 'matched' => true];
    	else {
    		foreach ($groupIds as $group_id) {
    			foreach ($cursor as $account_id => $account) {
    				if ($account->groups) {
    					if (in_array($group_id, explode(',', $account->groups))) $attendees[$account_id] = ['n_fn' => $account->n_fn, 'matched' => true];
    				}
    			}
    		}
    	}
    	return $attendees;
    }
    
    public function absenceAction()
    {
    	$context = Context::getCurrent();
    	$event_id = (int) $this->params()->fromRoute('event_id', 0);
    	$event = Event::get($event_id);
    	$description = Event::getDescription($event->type);
    
    	$owner = ['id' => $event->account_id, 'n_fn', 'n_fn' => $event->n_fn, 'matched' => true];
    	 
    	$attendees = [];
    	/*    	if ($event->matched_accounts) {
    	 foreach (explode(',', $event->matched_accounts) as $account_id) $attendees[$account_id] = Account::get($account_id);
    	 }*/
    	$attendees = TeacherController::getAttendees($event);
    
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
    			 
    			// Load the input data
    			$data = [];
    			$data['matched_accounts'] = $request->getPost('matched_accounts');
    			if (!$request->getPost('owner')) $data['status'] = 'canceled';
    			else $data['status'] = 'realized';
    
    			// Atomically save
    			$connection = Event::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
    				if ($event->loadData($data) != 'OK') throw new \Exception('View error');
    
    				$rc = $event->update($request->getPost('event_update_time'));
    				if ($rc != 'OK') $error = $rc;
    				if ($error) $connection->rollback();
    				else {
    
    					if ($event->status != 'canceled') {
    
    						if ($event->matched_accounts) $matched_accounts = explode(',', $event->matched_accounts);
    						else $matched_accounts = [];
    
    						// user_story: event_calendar_absence: Generate the absence events
    						Event::getTable()->multipleDelete(['type' => 'absence', 'property_11' => $event->id]);
    						foreach ($attendees as $account_id => &$account) {
    							if (!in_array($account_id, $matched_accounts)) {
    								$account['matched'] = false;
    
    								$absence = Event::instanciate('absence');
    								$absence->account_id = $account_id;
    								$absence->property_11 = $event->id;
    								$absence->place_id = $event->place_id;
    								$absence->begin_date = $event->begin_date;
    								$absence->end_date = $event->end_date;
    								$absence->begin_time = $event->begin_time;
    								$absence->end_time = $event->end_time;
    
    								// Properties between property_1 and property_10 are loaded with their counterpart in the calendar event
    								$absence->property_1 = $event->property_1;
    								$absence->property_2 = $event->property_2;
    								$absence->property_3 = $event->property_3;
    								$absence->property_4 = $event->property_4;
    								$absence->property_5 = $event->property_5;
    								$absence->property_6 = $event->property_6;
    								$absence->property_7 = $event->property_7;
    								$absence->property_8 = $event->property_8;
    								$absence->property_9 = $event->property_9;
    								$absence->property_10 = $event->property_10;
    								$absence->property_12 = 'unjustified';
    								 
    								$rc = $absence->add();
    								if ($rc != 'OK') $error = $rc;
    							}
    						}
    					}
    
    					if (!$error) {
    						$connection->commit();
    						$message = 'OK';
    					}
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
    		'event_id' => $event_id,
    		'event' => $event,
    		'owner' => $owner,
    		'attendees' => $attendees,
    		'csrfForm' => $csrfForm,
    		'error' => $error,
    		'message' => $message,
    		'description' => $description,
    	));
    	$view->setTerminal(true);
    	return $view;
    }
    
    public function evaluationAction()
    {
    	$context = Context::getCurrent();
    	$config = [];
    	$config['properties'] = Account::getConfig('p-pit-studies');
    	$search = $context->getConfig('teacher/evaluation/account/search');
		$properties = array();
		foreach ($search as $propertyId => $options) {
			$property = $config['properties'][$propertyId];
			$properties[$propertyId] = $property;
			$properties[$propertyId]['options'] = $options;
		}
		$config['search'] = $properties;

		// Retrieve the parameters
		$type = $this->params()->fromRoute('type');
		if (!$type) {
			$this->response->setStatusCode('400');
			$this->response->setReasonPhrase('Expecting a type');
			return $this->response;
		}
		$id = $this->params()->fromRoute('id');
		$places = Place::getList(array());
		
		$accounts = null; // If no account is provided in parameters, all the group is evaluated
		$accounts = $this->params()->fromQuery('accounts');
		if ($accounts) $accounts = explode(',', $accounts);
		
		$subject = $this->params()->fromQuery('subject');
		$level = $this->params()->fromQuery('level');
		$date = $this->params()->fromQuery('date', date('Y-m-d'));
		
		// user_story - student_evaluation_teachers: Les enseignants pouvant être selectionnés dans le formulaire sont tous les enseignants ayant un statut "actif"
		$teachers = [];
		if ($context->hasRole('manager')) {
			$cursor = Account::getList('teacher', ['status' => 'active,committed,contrat_envoye,reconnect_with'], '+name', null);
			foreach ($cursor as $teacher_id => $teacher) {
				$teachers[$teacher->contact_1_id] = $teacher->properties;
				$competences = $teachers[$teacher->contact_1_id]['property_3'];
				if ($competences) $competences = explode(',', $competences);
				else $competences = [];
				$teachers[$teacher->contact_1_id]['competences'] = $competences;
			}
		}
		else {
			$myAccount = Account::get($context->getContactId(), 'contact_1_id', 'teacher', 'type');
			if (!$myAccount) {
				$this->response->setStatusCode('403');
				return $this->response;
			}
			$teachers[$myAccount->contact_1_id] = $myAccount->properties;
		
			$competences = $myAccount->property_3;
			if ($competences) $competences = explode(',', $competences);
			else $competences = [];
			$teachers[$myAccount->contact_1_id]['competences'] = $competences;
				
			if ($myAccount->groups) $teachers[$myAccount->contact_1_id]['groups'] = explode(',', $myAccount->groups);
			else $teachers[$myAccount->contact_1_id]['groups'] = [];
		}
		
		// Retrieve the existing note or instanciate
		
		$content = [];
		$content['type'] = $type;
		$content['id'] = $id;
		$content['note'] = [];
		$content['note']['type'] = $type;
		$content['noteLinks'] = [];
		$content['teachers'] = $teachers;
		
		if ($id) {
				
			$note = Note::get($id);
		
			// Retrieve the group and the place
			$class = $note->class;
			$group_id = $note->group_id;
			$content['note']['group_id'] = $group_id;
			$group = Account::get($group_id);
			if ($group) {
				$content['group'] = $group->properties;
				$place = Place::get($group->place_id);
			}
			else {
				$content['group'] = null;
				$place = Place::get($note->place_id);
			}
			$content['place'] = $place->properties;
		
			$noteLinks = $note->links;
			$content['note']['status'] = $note->status;
			$content['note']['place_id'] = $place->id;
			$content['note']['teacher_id'] = $note->teacher_id;
			$content['note']['subject'] = $note->subject;
			$content['note']['level'] = $note->level;
			$content['note']['date'] = $note->date;
			$content['note']['school_year'] = $note->school_year;
			$content['note']['school_period'] = $note->school_period;
			$content['note']['reference_value'] = $note->reference_value;
			$content['note']['weight'] = $note->weight;
			$content['note']['observations'] = $note->observations;
			foreach ($note->links as $noteLink) $content['noteLinks'][$noteLink->account_id] = $noteLink->getProperties();
			$content['update_time'] = $note->update_time;
		}
		else {
		
			// Retrieve the group and the place
			$class = $this->params()->fromQuery('class');
			$group_id = $this->params()->fromQuery('group_id');
			$content['note']['group_id'] = $group_id;
			$group = Account::get($group_id);
			if (!$group || $group->type != 'group') {
				$this->response->setStatusCode('400');
				$this->response->setReasonPhrase('Not existing group');
				return $this->response;
			}
			if (!$context->hasRole('manager') && !in_array($group_id, $teachers[$myAccount->contact_1_id]['groups'])) {
				$this->response->setStatusCode('403');
				$this->response->setReasonPhrase('Group not allowed for this user');
				return $this->response;
			}
			$content['group'] = $group->properties;
			$place = Place::get($group->place_id);
			$content['place'] = $place->properties;
				
			$note = Note::instanciate($type, null, $group_id);
			$noteLinks = [];
			foreach ($group->members as $member_id => $member) {
				if (!$accounts || in_array($member_id, $accounts)) {
					$noteLink = [
						'account_id' => $member_id,
						'n_fn' => $member->n_fn,
						'value' => null,
						'assessment' => '',
					];
					$noteLinks[] = $noteLink;
				}
			}
			$content['note']['status'] = 'current';
			$content['note']['place_id'] = $group->place_id;
			if ($context->hasRole('manager')) $content['note']['teacher_id'] = null;
			else $content['note']['teacher_id'] = $myAccount->contact_1_id;
			$content['note']['subject'] = $subject;
			$content['note']['level'] = $level;
			$content['note']['date'] = $date;
			$content['note']['school_year'] = $context->getConfig('student/property/school_year/default');
		
			// user_story - student_evaluation_period: La période est pré-renseignée à la période en cours (en paramètre) mais peut être modifiée (ex. pour effectuer une rétro-saisie sur une période antérieure).
			$school_periods = $place->getConfig('school_periods');
			$current_school_period = $context->getCurrentPeriod($school_periods);
		
			$content['note']['school_period'] = $current_school_period;
			$content['note']['reference_value'] = $context->getConfig('student/parameter/average_computation')['reference_value'];
			$content['note']['weight'] = 1;
			$content['note']['observations'] = '';
			$content['noteLinks'] = $noteLinks;
			$content['update_time'] = null;
		}
		
		// Retrieve the subject list. As a teacher my subject list is restricted according to my competences
		$subjects = [];
		foreach ($place->getConfig('student/property/school_subject')['modalities'] as $subjectId => $subject) {
			if (!array_key_exists('archive', $subject) || !$subject['archive']) {
				if ($context->hasRole('manager')) $subjects[$subjectId] = $subject;
				else {
					$teacher = $teachers[$myAccount->contact_1_id];
					$teacherSubjects = ($teacher['property_5']) ? explode(',', $teacher['property_5']) : [];
					if (in_array($subjectId, $teacherSubjects)) $subjects[$subjectId] = $subject;
					if (array_key_exists('subcategory', $subject) && in_array($subject['subcategory'], $teacher['competences'])) $subjects[$subjectId] = $subject;
				}
			}
		}
		$content['config'] = [];
		$content['config']['subjects'] = $subjects;
		$content['config']['categories'] = $place->getConfig('student/property/evaluationCategory')['modalities'];

		$view = new ViewModel(array(
			'context' => $context,
			'config' => $config,
			'request' => ($this->getRequest()->isPost()) ? 'POST' : (($this->getRequest()->isDelete()) ? 'DELETE' : 'GET'),
			'content' => $content,
			'statusCode' => $this->response->getStatusCode(),
			'reasonPhrase' => $this->response->getReasonPhrase(),
		));
		$view->setTerminal(true);
		return $view;
	}
}
