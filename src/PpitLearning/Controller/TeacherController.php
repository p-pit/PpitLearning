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

    public function detailAltAction()
    {
    	return $this->detailAction();
    }
    
    public function planningAction()
    {
    	$context = Context::getCurrent();
    	$description = Event::getDescription('calendar');
    	$id = (int) $this->params()->fromRoute('id', 0);
    	$event = Event::get($id);
    
    	$view = new ViewModel(array(
    		'context' => $context,
    		'id' => $id,
    		'event' => $event,
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
