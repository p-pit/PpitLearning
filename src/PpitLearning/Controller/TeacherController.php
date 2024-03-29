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

    	$profiles = Account::getList('teacher', ['contact_1_id' => $context->getContactId()/*, 'status' => 'active'*/]);
    	 
    	$currentProfile = null;
    	if ($account_id) $currentProfile = Account::get($account_id);
    	else {
    		foreach ($profiles as $currentProfile) if ($currentProfile->status != 'gone') break;
    	}
    	if (!$currentProfile) return $this->redirect()->toRoute($context->getConfig('studentHome'));
    	if ($currentProfile->place_id) $place = Place::get($currentProfile->place_id);
    	else $place = $context->getPlace();
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
    		$currentProfile->email = $email;
    		if ($vcard) {
    			$userContact = UserContact::get($vcard->id, 'vcard_id');
    			if ($userContact) $panel = 'modalLoginForm';
    			$currentProfile->n_first = $vcard->n_first;
    			$currentProfile->n_last = $vcard->n_last;
    		}
    		else {
    			$currentProfile->n_first = $this->params()->fromQuery('n_first');
    			$currentProfile->n_last = $this->params()->fromQuery('n_last');
    		}
    		if ($panel != 'modalLoginForm') {
    			$panel = 'modalRegisterForm';
    		}
    	}
    
    	// Retrieve the global average if exists
    	$current_school_year = $context->getConfig('student/property/school_year/default');
    	$school_periods = $place->getConfig('school_periods');
    	$current_school_period = $context->getCurrentPeriod($school_periods);
    	$cursor = NoteLink::getList('report', ['category' => 'evaluation', 'subject' => 'global', 'school_year' => $current_school_year, 'school_period' => $current_school_period, 'account_id' => $currentProfile->id], 'id', 'ASC', $mode = 'search');
    	foreach ($cursor as $report) $averageNote = $report; // Should be unique but to keep only the last one
    	$global_average = (isset($averageNote) && $averageNote) ? $averageNote->value : null;
    
    	$view = new ViewModel(array(
    		'context' => $context,
    		'place_identifier' => $place->identifier,
    		'place' => $place,
    		'currentProfile' => $currentProfile,
    		'profiles' => $profiles,
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
    	$begin = $this->params()->fromQuery('begin');
    	$end = $this->params()->fromQuery('end');
    	 
    	$teacher_id = (int) $this->params()->fromQuery('teacher_id');
    
    	$notes = Note::GetList('homework', null, ['teacher_id' => $teacher_id, 'min_date' => $begin, 'max_date' => $end], 'date', 'DESC', 'search', null);
    	 
    	echo json_encode($notes, JSON_PRETTY_PRINT);
    	return $this->getResponse();
    }
    
	public function homeworkAction()
	{
		$context = Context::getCurrent();
		if ($this->getRequest()->isPost()) $requestType = 'POST';
		elseif ($this->getRequest()->isDelete()) $requestType = 'DELETE';
		else $requestType = 'GET';
		
		$id = $this->params()->fromRoute('id');
		$groups = $this->params()->fromQuery('groups');
		$groups = explode(',', $groups);
		$group_id = $groups[0];
		$group = Account::get($group_id);
		$account_id = $this->params()->fromQuery('account_id');
		$account= Account::get($account_id);
		
		if ($id) {
			$note = Note::get($id);
			$type = $note->type;
			$subject = $note->subject;
			$date = $note->date;
		}
		else {
			$type = $this->params()->fromRoute('type');
			$subject = $this->params()->fromQuery('subject');
			$date = $this->params()->fromQuery('date');
			$note = Note::instanciate($type, null);
			$note->place_id = $account->place_id;
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
		];
		
		$select = Document::getSelect('binary', [], ['folder' => ['eq', 'commitments'], 'account_id' => ['eq', $account_id]], ['-update_time'], null);
		$documents = Document::getTable()->selectWith($select);
		
		$content['documents'] = [];
		foreach ($documents as $document) {
			$data = $document->getProperties();
			$content['documents'][$document->id] = $data;
			$content['documents'][$document->id]['is_deletable'] = $document->isDeletable();
		}
		
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
		
		elseif ($this->getRequest()->isDelete()) {
			$rc = $note->delete(null);
		}
		
		$content['note'] = $note->getProperties();
		$content['statusCode'] = $statusCode;

		// Return the link list
		$view = new ViewModel($content);
		$view->setTerminal(true);
		return $view;
	}
/*    
	public function homeworkDocumentAction()
	{
		$view = $this->documentAction();
		$type = $this->params()->fromRoute('type');
		$view->type = $type;
		return $view;
	}*/

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
    public static function getAttendees($event, $absences)
    {
    	$groups = Account::getList('group', [], '+name', null);
    	$groupIds = $event->groups;
    	if ($groupIds) $groupIds = explode(',', $groupIds);
    
    	$attendees = [];
    	$filters['status'] = 'active,retention';
    	if ($event->property_2) $filters['property_7'] = $event->property_2;
    	$cursor = Account::getList('p-pit-studies', $filters, '+n_fn', null);
    
    	$matched_accounts = ($event->matched_accounts) ? explode(',', $event->matched_accounts) : [];
    	if (!$groupIds) foreach ($cursor as $account_id => $account) $attendees[$account_id] = ['n_fn' => $account->n_fn, 'matched' => true];
    	else {
    		foreach ($cursor as $account_id => $account) {
    			$keep = false;
    			foreach ($groupIds as $group_id) {
    				if ($account->groups) {
    					if (in_array($group_id, explode(',', $account->groups))) $keep = true;
    				}
    			}
    			if ($account->property_15 && $event->subcategory) {
					$rythm = explode(',', $event->subcategory); 
					if (!in_array($account->property_15, $rythm)) $keep = false;
				}
    			if ($keep) {
    				$attendees[$account_id] = $account->properties;
    				if (in_array($account_id, $matched_accounts)) $attendees[$account_id]['matched'] = true;
    				elseif (array_key_exists($account_id, $absences)) $attendees[$account_id]['matched'] = false;
    				else $attendees[$account_id]['matched'] = true;
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
    
    	$cursor = Event::getList('absence', ['property_11' => $event->id], '+id', null);
    	$absences = [];
    	foreach ($cursor as $absence) $absences[$absence->account_id] = $absence;
    	
    	$attendees = [];
    	$attendees = TeacherController::getAttendees($event, $absences);
    
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
    			$data['status'] = $request->getPost('status');
    			 
    			// Atomically save
    			$connection = Event::getTable()->getAdapter()->getDriver()->getConnection();
    			$connection->beginTransaction();
    			try {
    				if ($event->loadData($data) != 'OK') throw new \Exception('View error');
    
    				$rc = $event->update($request->getPost('event_update_time'));
    				if ($rc != 'OK') $error = $rc;
    				if ($error) $connection->rollback();
    				else {
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
    							
    							$duration = $request->getPost('lateness-' . $account_id);
    							if ($duration) {

    								$lateness = Absence::instanciate();
    								$lateness->place_id = $context->getPlaceId();
    								$lateness->category = 'lateness';
    								$lateness->school_year = $context->getConfig('student/property/school_year/default');
    								$lateness->type = 'schooling';
    								$lateness->account_id = $account_id;
    								$lateness->subject = $event->property_3;
    								$lateness->begin_date = $event->begin_date;
    								$lateness->end_date = $event->end_date;
    								$lateness->duration = $duration;
    								$lateness->add();
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
    
	// ICI
    public function evaluationAction()
    {
    	$context = Context::getCurrent();
		$updateProperties = $context->getConfig('teacher/evaluation/update')['properties'];

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
/*		if ($context->hasRole('manager') && !$context->hasRole('teacher')) {
			$cursor = Account::getList('teacher', ['status' => 'active,committed,contrat_envoye,reconnect_with'], '+name', null);
			foreach ($cursor as $teacher_id => $teacher) {
				$teachers[$teacher->contact_1_id] = $teacher->properties;
				$competences = $teachers[$teacher->contact_1_id]['property_3'];
				if ($competences) $competences = explode(',', $competences);
				else $competences = [];
				$teachers[$teacher->contact_1_id]['competences'] = $competences;
				
				$groups = $teachers[$teacher->contact_1_id]['groups'];
				if ($groups) $groups = explode(',', $groups);
				else $groups = [];
				$teachers[$teacher->contact_1_id]['groups'] = $groups;
			}
    		$owner_id = (int) $this->params()->fromQuery('myAccount');
	    	$myAccount = Account::get($owner_id);
		}
		else {*/
			$myAccount = Account::get($this->params()->fromQuery('myAccount'));
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
//		}
		$teacher = $teachers[$myAccount->contact_1_id];
		
		// Retrieve the existing note or instanciate
		
		$content = [];
		$content['type'] = $type;
		$content['id'] = $id;
		$content['note'] = [];
		$content['note']['type'] = $type;
		$content['noteLinks'] = [];
		$content['teacher'] = $teacher;
		
		if ($id) {
				
			$note = Note::get($id);
		
			// Retrieve the group and the place
			$class = $note->class;
			$group_id = $note->group_id;
			$content['note']['group_id'] = $group_id;
			$group = Account::get($group_id);
			if ($group) {
				$content['group'] = $group->properties;
			}
			else {
				$content['group'] = null;
			}
			$place = Place::get($note->place_id);
			$content['place'] = ($place) ? $place->properties : null;
		
			$noteLinks = [];
			foreach ($group->members as $member_id => $member) {
				if ($member->type == 'p-pit-studies') {
					if (!$accounts || in_array($member_id, $accounts)) {
						$noteLink = [
							'account_id' => $member_id,
							'n_fn' => $member->n_fn,
							'value' => null,
							'assessment' => '',
						];
						$noteLinks[$member_id] = $noteLink;
					}
				}
			}
			foreach ($note->links as $link_id => $link) {
				$noteLinks[$link_id]['value'] = $link->value;
				$noteLinks[$link_id]['assessment'] = $link->assessment;
				$noteLinks[$link_id]['evaluation'] = $link->evaluation;
			}
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
			$content['noteLinks'] = $noteLinks;
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
			$place = Place::get($myAccount->place_id);
			if (!$place) $place = Place::get($group->place_id);
			if ($place) $content['place'] = $place->properties;
				
			$note = Note::instanciate($type, null, $group_id);
			$noteLinks = [];
			foreach ($group->members as $member_id => $member) {
				if ($member->type == 'p-pit-studies' && (!$myAccount->place_id || $member->place_id == $myAccount->place_id)) {
					if (!$accounts || in_array($member_id, $accounts)) {

						// Select rule based on subject repository
						$keep = true;
						if ($subject) {
							$subjectConfig = $context->getConfig('student/property/school_subject')['modalities'][$subject];
							if (array_key_exists('filter', $updateProperties['subject'])) {
								foreach($updateProperties['subject']['filter'] as $paramId => $rule) {
									if (array_key_exists($paramId, $subjectConfig) && $subjectConfig[$paramId]) {
										foreach ($rule as $propertyId => $value) {
											if (!in_array($member->properties[$propertyId], explode(',', $value))) {
												$keep = false;
												break;
											}
										}
									}
								}
							}
						}
						if ($keep) {
							$noteLink = [
								'account_id' => $member_id,
								'n_fn' => $member->n_fn,
								'value' => null,
								'assessment' => '',
							];
							$noteLinks[] = $noteLink;
						}
					}
				}
			}
			$content['note']['status'] = 'current';
			if ($place) $content['note']['place_id'] = $place->id;
			if ($context->hasRole('manager')) $content['note']['teacher_id'] = null;
			else $content['note']['teacher_id'] = $myAccount->contact_1_id;
			$content['note']['subject'] = $subject;
			$content['note']['level'] = $level;
			$content['note']['date'] = $date;
			$content['note']['school_year'] = $context->getConfig('student/property/school_year/default');
		
			// user_story - student_evaluation_period: La période est pré-renseignée à la période en cours (en paramètre) mais peut être modifiée (ex. pour effectuer une rétro-saisie sur une période antérieure).
			$school_periods = ($place) ? $place->getConfig('school_periods') : $context->getPlace()->getConfig('school_periods');
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
		$modalities = ($place) ? $place->getConfig('student/property/school_subject')['modalities'] : $context->getPlace()->getConfig('student/property/school_subject')['modalities'];
		foreach ($modalities as $subjectId => $subject) {
			if (!array_key_exists('archive', $subject) || !$subject['archive']) {
				if ($context->hasRole('manager')) $subjects[$subjectId] = $subject;
				else {
					$teacherSubjects = ($teacher['property_5']) ? explode(',', $teacher['property_5']) : [];
					if (in_array($subjectId, $teacherSubjects)) $subjects[$subjectId] = $subject;
					if (array_key_exists('subcategory', $subject) && in_array($subject['subcategory'], $teacher['competences'])) $subjects[$subjectId] = $subject;
				}
			}
		}
		$content['config'] = [];
		$content['config']['subjects'] = $subjects;
		$categories = ($place) ? $place->getConfig('student/property/evaluationCategory')['modalities'] : $context->getPlace()->getConfig('student/property/evaluationCategory')['modalities'];
		$content['config']['categories'] = $categories;

		// POST request for create or update
		if ($this->request->isPost()) {
		
			// User story - student_evaluation_teachers:
			// Rôle manager: les enseignants pouvant être selectionnés dans le formulaire sont tous les enseignants ayant un statut "actif".
			// Rôle enseignant: je ne peux pas affecter un autre enseignant que moi-même à l'évaluation.
		
			// Load the input data
		
			$content['note']['teacher_id'] = $this->request->getPost('teacher_id');
			$content['note']['school_period'] = ($this->request->getPost('school_period')) ? $this->request->getPost('school_period') : 'Q1';
			$content['note']['level'] = $this->request->getPost('level');
			$content['note']['subject'] = $this->request->getPost('subject');
			$content['note']['date'] = $this->request->getPost('date');
			$content['note']['reference_value'] = $this->request->getPost('reference_value');
			$content['note']['weight'] = $this->request->getPost('weight');
			$content['note']['observations'] = $this->request->getPost('observations');

			foreach ($content['noteLinks'] as $noteLinkData) {
				$account_id = $noteLinkData['account_id'];
				if (!$this->request->getPost('noteAccount-' . $account_id)) {
					if (array_key_exists($account_id, $note->links)) {
						$noteLink = $note->links[$account_id];
						$noteLink->value = $noteLinkData['value'] = null;
						$noteLink->assessment = $noteLinkData['assessment'] = null;
					}
				}
				else {
					if (!$id || !array_key_exists($account_id, $note->links)) $noteLink = NoteLink::instanciate($account_id);
					else $noteLink = $note->links[$account_id];
					$value = $this->request->getPost('value-' . $account_id);
					if ($value === '') $value = null;
					//				$mention = $this->request->getPost('mention-' . $account_id);
					$assessment = $this->request->getPost('assessment-' . $account_id);
					$audit = [];
					if ($value !== null || $assessment) {
						if ($value == 'Non Évalué') {
							$noteLinkData['value'] = 0;
							$noteLinkData['evaluation'] = $value;
						} else {
							$noteLinkData['value'] = $value;
							$noteLinkData['evaluation'] = NULL;
						}
						//					$noteLinkData['evaluation'] = $mention;
						$noteLinkData['assessment'] = $assessment;
						$noteLink->loadData($noteLinkData);
						$note->links[$account_id] = $noteLink;
					}
				}
			}
			$rc = $note->loadData($content['note']);
			if ($rc != 'OK') {
				$this->response->setStatusCode('409');
				$this->response->setReasonPhrase($rc);
				return null;
			}
			else {
				// Atomically save
				$connection = Note::getTable()->getAdapter()->getDriver()->getConnection();
				$connection->beginTransaction();
				try {
					$update_time = $this->request->getPost('update_time');
					if ($note->id) $rc = $note->update('update_time');
					elseif (count($note->links)) {
						$rc = $note->add();
						$content['id'] = $note->id;
					}
					if ($rc != 'OK') {
						$connection->rollback();
						$this->response->setStatusCode('409');
						$this->response->setReasonPhrase($rc);
						return null;
					}
		
					// Save the note at the student level
					
					foreach ($note->links as $noteLink) {
						if (!$noteLink->id) {
							$noteLink->note_id = $note->id;
							$rc = $noteLink->add();
						}
						else {
							$rc = $noteLink->drop();
							$noteLink->id = null;
							if ($noteLink->value !== null || $noteLink->assessment || $noteLink->evaluation) $rc = $noteLink->add(null);
						}
						if ($rc != 'OK') {
							$connection->rollback();
							$this->response->setStatusCode('409');
							$this->response->setReasonPhrase($rc);
							return null;
						}
					}
						
					// Update the subject and global averages
					if (false /* (transient rule) $note->id*/) {
						$rc = Note::updateAverage($content['note']['place_id'], $class, $group_id, $content['note']['subject'], $content['note']['school_year'], $content['note']['school_period']);
						if ($rc) {
							$connection->rollback();
							$this->response->setStatusCode('409');
							$this->response->setReasonPhrase($rc);
							return null;
						}
					}
						
					// Compute the group indicators
					$content['indicators'] = null; //$note->computeGroupIndicators();
		
					$connection->commit();
					$this->response->setStatusCode('200');
				}
				catch (\Exception $e) {
					$connection->rollback();
					$this->response->setStatusCode('409');
					$this->response->setReasonPhrase('Exception: ' . $e);
					return null;
				}
			}
		}
		
		$view = new ViewModel(array(
			'context' => $context,
			'config' => $config,
			'updateProperties' => $updateProperties,
			'request' => ($this->getRequest()->isPost()) ? 'POST' : (($this->getRequest()->isDelete()) ? 'DELETE' : 'GET'),
			'content' => $content,
			'statusCode' => $this->response->getStatusCode(),
			'reasonPhrase' => $this->response->getReasonPhrase(),
		));
		$view->setTerminal(true);
		return $view;
	}
	
	public function reportListAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
		$school_year = $context->getConfig('student/property/school_year/default');

		$teacher_id = (int) $this->params()->fromRoute('teacher_id');
		$teacher = Account::get($teacher_id);
//		$periods = array();
		$where = ['teacher_id' => $teacher->contact_1_id, 'school_year' => $school_year];
		if ($teacher->place_id) $where['place_id'] = $teacher->place_id;
		$noteLinks = NoteLink::getList('report', $where, 'creation_time', 'DESC', 'search');

		// Return the link list
		$view = new ViewModel(array(
			'context' => $context,
			'config' => $context->getconfig(),
			'teacher' => $teacher,
			'noteLinks' => $noteLinks,
    		'groups' => Account::getList('group', [], '+name', null),
		));
		$view->setTerminal(true);
		return $view;
	}

	public function reportAction()
	{
		// Retrieve the context
		$context = Context::getCurrent();
		$school_year = '2019-2020'; //$context->getConfig('current_school_year');
	
		$id = (int) $this->params()->fromRoute('id');
		$noteLink = NoteLink::get($id);
		
		// Compute the average
		$notes = NoteLink::GetList('note', ['subject' => $noteLink->subject, 'school_year' => $noteLink->school_year, 'school_period' => $noteLink->school_period], 'subject', 'ASC', 'search');
		$averages = [];
		foreach ($notes as $link) {
			if (!array_key_exists($link->account_id, $averages)) $averages[$link->account_id] = [0, 0];
			if ($link->value !== null) {
				$averages[$link->account_id][0] += $link->value * $link->weight;
				$averages[$link->account_id][1] += $link->reference_value * $link->weight;
			}
		}
		
		// POST request for create or update
		if ($this->request->isPost()) {
			$noteLink->assessment = $this->request->getPost('assessment');
			$noteLink->update(null);
		}
		
		// Return the link list
		$view = new ViewModel(array(
			'context' => $context,
			'request' => ($this->getRequest()->isPost()) ? 'POST' : (($this->getRequest()->isDelete()) ? 'DELETE' : 'GET'),
			'noteLink' => $noteLink,
			'id' => $id,
			'averages' => $averages,
			'statusCode' => $this->response->getStatusCode(),
			'reasonPhrase' => $this->response->getReasonPhrase(),
		));
		$view->setTerminal(true);
		return $view;
	}
	
	public function initAction()
	{
		$context = Context::getCurrent();
		$places = Place::getList([]);
		
		// Get the teacher accounts
		$cursor = Account::getList('teacher', [], '+email', null);
		$teacher = [];
		foreach ($cursor as $teacher_id => $teacher) {
			$teachers[$teacher->email . '_' . $teacher->place_id] = $teacher;
			$vcard = Vcard::get($teacher->contact_1_id);
			if (!array_key_exists('teacher', $vcard->roles)) {
				$vcard->roles['teacher'] = 'teacher';
				$vcard->update(null);
				print_r(['id' => $teacher->id, 'email' => $teacher->email, 'place_caption' => $teacher->place_caption, 'contact_1_id' => $teacher->contact_1_id]);
			}
			if ($vcard->applications && $vcard->roles == ['teacher' => 'teacher']) {
				$vcard->applications = [];
				$vcard->update(null);
				print_r(['id' => $teacher->id, 'email' => $teacher->email, 'place_caption' => $teacher->place_caption, 'contact_1_id' => $teacher->contact_1_id, 'roles' => $vcard->roles, 'applications' => $vcard->applications]);
			}
		}
		
		// Get the vcard with role 'teacher'
/*		$columns = null;
		$filters = ['roles' => ['like', 'teacher']];
		$order = ['email'];
		$limit = null;
		$select = Vcard::getSelect($columns, $filters, $order, $limit);
		$select->join('core_user_contact', 'core_vcard.id = core_user_contact.vcard_id', ['user_id'], 'left');
		$select->join('core_user', 'core_user.user_id = core_user_contact.user_id', ['username'], 'left');
		$select->join('core_account', 'core_account.contact_1_id = core_vcard.id', ['account_id' => 'id'], 'left');
		$select->join('core_place', 'core_place.id = core_account.place_id', ['place_id' => 'id', 'place_caption' => 'caption'], 'left');
		$cursor = Vcard::getTable()->selectWith($select);
		$vcards = [];
		print_r("account_id;id;email;n_fn;place_id;username;place_caption;\n");
		foreach ($cursor as $vcard) {
			if ($vcard->username && array_key_exists('p-pit-admin', $vcard->perimeters) && array_key_exists('place_id', $vcard->perimeters['p-pit-admin'])) {
				foreach ($vcard->perimeters['p-pit-admin']['place_id'] as $place_id) {
					if (!array_key_exists($vcard->email . '_' . $place_id, $teachers)) {
						$account = Account::instanciate('teacher');
						$account->status = 'active';
						$account->contact_1_id = $vcard->id;
						$account->place_id = $place_id;
						$account->add();
						print_r($account->id . ';' . $vcard->id . ';' . $vcard->email . ';' . $vcard->n_fn . ';' . $place_id . ';' . $vcard->username . ';' . $places[$place_id]->caption . ";\n");
						$teachers[$vcard->email . '_' . $place_id] = $account;
					}
				}
			}
		}*/
		exit;
	}
	public function v1Get($context, $type, $requestBody, $response)
	{
		$context->getConfig('student/property/school_subject');
		$responseBody = [];

		$account_id = $this->params()->fromRoute('id');
		if ($account_id) {
			$account = Account::getTable()->get($account_id);
			if (!$account) {
				$response->setStatusCode('400');
				$response->setContent(json_encode(['message' => 'Not existing account with id ' . $account_id]));
				$response->setReasonPhrase("Not existing account with id $account_id");
				return;
			}

			$vcard = Vcard::getTable()->get($account->contact_1_id);
		}
		else {
			$email = $this->params()->fromQuery('email');
			if ($email) {
				$vcard = Vcard::getTable()->get($email, 'email');
				if ($vcard) {
					$account = Account::getTable()->get($type, 'type', $vcard->id, 'contact_1_id');
				}
				else $account = null;
			}
			else {
				$response->setStatusCode('400');
				$response->setContent(json_encode(['message' => 'Account search not available']));
				$response->setReasonPhrase("Account search not available");
				return;
			}
		}
		if ($account) {
			$responseBody[] = [
				'id' => $account->id,
				'status' => $account->status,
				'name' => $account->name,
				'n_first' => $vcard->n_first,
				'n_last' => $vcard->n_last,
				'n_fn' => $vcard->n_fn,
				'email' => $vcard->email,
				'tel_cell' => $vcard->tel_cell,
				'tel_work' => $vcard->tel_work,
				'org' => $vcard->org,
				'nationality' => $vcard->nationality,
				'tiny_4' => $vcard->tiny_4,
				'adr_street' => $vcard->adr_street,
				'adr_zip' => $vcard->adr_zip,
				'adr_city' => $vcard->adr_city,
				'adr_country' => $vcard->adr_country,
				'birth_date' => $vcard->birth_date,
				'origine' => $account->origine,
				'invoice_account_id' => $account->invoice_account_id,
				'transfer_order_id' => $account->transfer_order_id,
				'bank_identifier' => ($account->bank_identifier) ? $context->getSecurityAgent()->unprotectPrivateDataV2($account->bank_identifier) : null,
				'property_2' => $account->property_2,
				'subject' => $account->property_5,
				'groups_id' => $account->groups,
				'property_7' => $account->property_7,
				'property_11' => $account->property_11,
			];
		}
		$response->setContent(json_encode($responseBody));
		$response->setStatusCode(200);
	}

    public function v1Put($context, $type, $requestBody, $response)
	{
		$connection = Account::getTable()->getAdapter()->getDriver()->getConnection();
		$connection->beginTransaction();
		try {
			$responseBody = [];
			foreach ($requestBody as $accountData) {

				// Check existing account based on email
				$email = $accountData['email'];
				// $vcard = Vcard::getTable()->get($email, 'email');
				// if ($vcard) {
				// 	$account = Account::getTable()->get($vcard->id, 'contact_1_id', $type, 'type');
				// 	if ($account) { 
				// 		$responseBody[$email] = [
				// 			'status' => 'exists',
				// 			'account_id' => $account->id,
				// 			'account_status' => $account->status,
				// 			'account_callback_date' => $account->callback_date,
				// 		];
				// 		continue;
				// 	}
				// }

				// Instanciate vcard
				$vcard = new Vcard;
				$vcard->status = 'new';
				$vcard->locale = 'fr_FR';
				$vcard->photo_link_id = 'no-photo.png';
				$vcard->applications = array();
				$vcard->roles = array();
				$vcard->perimeters = array();
				$vcard->audit = array();
				$vcard->specifications = array();
				
				// Instanciate account
				$account = new Account;
				$account->status = 'new';
				$account->type = $type;
				$account->opening_date = date('Y-m-d');
				$account->callback_date = date('Y-m-d');
				$account->contact_history = array();
				$account->audit = array();
				$account->availability_exceptions = array();
				$account->availability_constraints = array();
				$account->credits = array();
				$account->json_property_1 = array();
				$account->json_property_2 = array();
				$account->json_property_3 = array();
				$account->json_property_4 = array();
				$account->json_property_5 = array();
						
				$comment = [];

				// Load data
				foreach ($accountData as $name => $value) {
		
					$vcard->id = null;
					$vcard->status = 'new';
					if ($name == 'email') $vcard->email = $value;
					elseif ($name == 'n_first') $vcard->n_first = $value;
					elseif ($name == 'n_last') $vcard->n_last = $value;
					elseif ($name == 'org') $vcard->org = $value;
					elseif ($name == 'tel_cell') $vcard->tel_cell = $value;
					elseif ($name == 'tel_work') $vcard->tel_work = $value;
					elseif ($name == 'birth_date') $vcard->birth_date = $value;
					elseif ($name == 'adr_street') $vcard->adr_street = $value;
					elseif ($name == 'adr_zip') $vcard->adr_zip = $value;
					elseif ($name == 'adr_city') $vcard->adr_city = $value;
					elseif ($name == 'adr_country') $vcard->adr_country = $value;

					elseif ($name == 'status') $account->status = $value;
					elseif ($name == 'identifier') $account->identifier = $value;
					elseif ($name == 'origine') $account->origine = $value;
					elseif ($name == 'place_id') $account->place_id = $value;
					elseif ($name == 'place_identifier') {
						$place = Place::get($value, 'identifier');
						$account->place_id = $place->id;
					} 
					elseif ($name == 'property_1') $account->property_1 = $value;
					elseif ($name == 'property_5') $account->property_5 = $value;
					elseif ($name == 'name') $account->name = $value;
					elseif ($name == 'property_6') $account->property_6 = $value;
					elseif ($name == 'property_7') $account->property_7 = $value;
					elseif ($name == 'property_8') $account->property_8 = $value;
					elseif ($name == 'property_10') $account->property_10 = $value;
					elseif ($name == 'property_11') $account->property_11 = $value;
					elseif ($name == 'property_13') $account->property_13 = $value;
					elseif ($name == 'property_18') $account->property_18 = $value;
					elseif ($name == 'comment') $comment[] = $value;
					else $comment[] = $value;
				}

				$vcard->n_fn = $vcard->n_last . ', '. $vcard->n_first;

				// Add vcard
				Vcard::getTable()->save($vcard);

				// Add account
				$account->contact_1_id = $vcard->id;
				$account->contact_1_status = 'main';
				if (!$account->name) $account->name = $vcard->n_fn;
				Account::getTable()->save($account);
				if (!$account->identifier) {
					$account->identifier = $account->id;
					Account::getTable()->save($account);
				}
		
				// Add audit
				if ($comment) {
					$audit = new Audit;
					$audit->status = 'new';
					$audit->entity = 'core_account';
					$audit->row_id = $account->id;
					$audit->action = 'add';
					$audit->time = date('Y-m-d H:i:s');
					$audit->n_fn = $context->getFormatedName();
					$audit->comment = implode("<br>", $comment);
					Audit::getTable()->save($audit);
				}

				$responseBody[$email] = [
					'status' => 'OK',
					'account_id' => $account->id,
					'account_status' => $account->status,
					'account_callback_date' => $account->callback_date,
				];
			}

			$connection->commit();
		
			$response->setContent(json_encode($responseBody));
			$response->setStatusCode('200');
		}
		catch (\Exception $e) {
			$connection->rollback();
			$response->setStatusCode('500');
			$response->setReasonPhrase($e->getMessage());
		}
	}
	
    public function v1Post($context, $type, $requestBody, $response)
	{
		$connection = Account::getTable()->getAdapter()->getDriver()->getConnection();
		$connection->beginTransaction();
		
		// Retrieve the account
		$account_id = $this->params()->fromRoute('id');
		$account = Account::getTable()->get($account_id);
		if (!$account) {
			$response->setStatusCode('400');
			$response->setContent(json_encode(['message' => 'Not existing account with id ' . $account_id]));
			$response->setReasonPhrase("Not existing account with id $account_id");
			return;
		}
		$contact_1 = Vcard::getTable()->get($account->contact_1_id);

		try {
			$responseBody = [];
			$accountData = $requestBody;
					
			$comment = [];

			// Load data
			$updateVcard = false;
			foreach ($accountData as $name => $value) {
				if ($name == 'n_first') {
					$contact_1->n_first = $value;
					$updateVcard = true;
				}
				elseif ($name == 'n_last') {
					$contact_1->n_last = $value;
					$updateVcard = true;
				}
				elseif ($name == 'n_fn') {
					$contact_1->n_fn = $value;
					$updateVcard = true;
				}
				elseif ($name == 'email') {
					$contact_1->email = $value;
					$updateVcard = true;
				}
				elseif ($name == 'org') {
					$contact_1->org = $value;
					$updateVcard = true;
				}
				elseif ($name == 'adr_street') {
					$contact_1->adr_street = $value;
					$updateVcard = true;
				}
				elseif ($name == 'adr_zip') {
					$contact_1->adr_zip = $value;
					$updateVcard = true;
				}
				elseif ($name == 'adr_city') {
					$contact_1->adr_city = $value;
					$updateVcard = true;
				}
				elseif ($name == 'adr_country') {
					$contact_1->adr_country = $value;
					$updateVcard = true;
				}
				elseif ($name == 'tel_cell') {
					$contact_1->tel_cell = $value;
					$updateVcard = true;
				}
				elseif ($name == 'tel_work') {
					$contact_1->tel_work = $value;
					$updateVcard = true;
				}
				elseif ($name == 'birth_date') {
					$contact_1->birth_date = $value;
					$updateVcard = true;
				}
				elseif ($name == 'nationality') {
					$contact_1->nationality = $value;
					$updateVcard = true;
				}
				elseif ($name == 'tiny_4') {
					$contact_1->tiny_4 = $value;
					$updateVcard = true;
				}
				elseif ($name == 'status') $account->status = $value;
				elseif ($name == 'origine') $account->origine = $value;
				elseif ($name == 'place_identifier') {
					$place = Place::get($value, 'identifier');
					$account->place_id = $place->id;
				}
				// elseif ($name == 'place_id') $vcard->place_id = $value;
				elseif ($name == 'org') $vcard->org = $value;
				elseif ($name == 'place_id') $account->place_id = $value;
				elseif ($name == 'name') $account->name = $value;
				elseif ($name == 'identifier') $account->identifier = $value;
				elseif ($name == 'transfer_order_id') $account->transfer_order_id = $value;
				elseif ($name == 'bank_identifier') $account->bank_identifier = $context->getSecurityAgent()->protectPrivateDataV2($value);
				elseif ($name == 'property_1') $account->property_1 = $value;
				elseif ($name == 'property_5') $account->property_5 = $value;
				elseif ($name == 'property_6') $account->property_6 = $value;
				elseif ($name == 'property_7') $account->property_7 = $value;
				elseif ($name == 'property_8') $account->property_8 = $value;
				elseif ($name == 'property_11') $account->property_11 = $value;
				elseif ($name == 'property_13') $account->property_13 = $value;
				elseif ($name == 'comment') $comment[] = $value;
				else $comment[] = $value;
			}
			$account->callback_date = date('Y-m-d');

			// Update account and vcard
			Account::getTable()->save($account);
			if ($updateVcard) Vcard::getTable()->save($contact_1);
	
			// Add audit
			if ($comment) {
				$audit = new Audit;
				$audit->status = 'new';
				$audit->entity = 'core_account';
				$audit->row_id = $account->id;
				$audit->action = 'update';
				$audit->time = date('Y-m-d H:i:s');
				$audit->n_fn = $context->getFormatedName();
				$audit->comment = implode("<br>", $comment);
				Audit::getTable()->save($audit);
			}

			$responseBody[$account->id] = 'Updated';

			$connection->commit();
		
			$response->setContent(json_encode($responseBody));
			$response->setStatusCode(200);
		}
		catch (\Exception $e) {
			$connection->rollback();
			$response->setStatusCode(500);
			$response->setContent(json_encode(['message' => 'Erreur de serveur, veuillez contacter votre interlocuteur']));
			$response->setReasonPhrase($e->getMessage());
		}
	}

    public function v1Action()
	{
        $context = Context::getCurrent();
        $request = $this->getRequest();
        if ($request->isGet()) $requestType = 'GET';
        elseif ($request->isPut()) $requestType = 'PUT';
        elseif ($request->isPost()) $requestType = 'POST';

		$type = $this->params()->fromRoute('type');
		$requestBody = json_decode($request->getContent(), true);

        if ($requestType == 'GET') $this->v1get($context, $type, $requestBody, $this->response);

        // New account
        elseif ($requestType == 'PUT') $this->v1put($context, $type, $requestBody, $this->response);

        // Already existing account
        elseif ($requestType == 'POST') $this->v1post($context, $type, $requestBody, $this->response);

		header('Content-Type: application/json');
		return $this->response;
	}
}
