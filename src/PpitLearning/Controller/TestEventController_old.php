<?php
/**
 * PpitCore V1.0 (https://github.com/p-pit/PpitCore)
 *
 * @link      https://github.com/p-pit/PpitCore
 * @copyright Copyright (c) 2016 Bruno Lartillot
 * @license   https://github.com/p-pit/PpitCore/blob/master/license.txt GNU-GPL license
 */

namespace PpitLearning\Controller;

//use PpitCore\Model\App;
use PpitCore\Model\Community;
use PpitCore\Model\Event;
use PpitCore\Model\Generic;
use PpitCore\Model\Interaction;
use PpitCore\Model\Place;
use PpitCore\Model\Vcard;
use PpitCore\ViewHelper\SsmlEventViewHelper;
use PpitCore\Model\Csrf;
use PpitCore\Model\Context;
use PpitCore\Form\CsrfForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use ZendDeveloperTools\Collector\EventCollectorInterface;

class TestEventController_old extends AbstractActionController
{
    public function indexAction()
    {
    	$app = App::get('p-pit-learning', 'identifier');
    	$context = Context::getCurrent($app);

    	// Retrieve parameters
    	$type = $this->params()->fromRoute('type', $context->getConfig('event/type')['default']);
    	
    	$personnalize = ($this->params()->fromQuery('personnalize'));
    	$place = Place::get($context->getPlaceId());
    	$community = Community::get($context->getCommunityId());
    		 
		$applicationId = 'p-pit-learning';
		$applicationName = 'P-Pit Learning';
		$currentEntry = $this->params()->fromQuery('entry', 'place');
		return new ViewModel(array(
    			'context' => $context,
    			'type' => $type,
    			'config' => $context->getConfig(),
    			'place' => $place,
    			'community' => $community,
    			'active' => 'application',
    			'app' => $app,
    			'applicationId' => $applicationId,
    			'applicationName' => $applicationName,
    			'currentEntry' => $currentEntry,
				'personnalize' => $personnalize,
		));
    }
 }
