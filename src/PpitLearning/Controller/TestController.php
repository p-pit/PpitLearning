<?php

namespace PpitLearning\Controller;

use PpitCore\Form\CsrfForm;
use PpitCore\Model\Context;
use PpitCore\Model\Csrf;
use PpitLearning\Model\Test;
use Zend\Console\Request as ConsoleRequest;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class TestController extends AbstractActionController
{
    public function deserializeAction()
    {
    	$context = Context::getCurrent();
    	$config = $context->getConfig();
    	$id = $this->params()->fromRoute('id');
    	$test = Test::get($id);
    	var_export($test->content);
    	return $this->response;
    }
	
	public function serializeAction()
    {
    	$context = Context::getCurrent();
    	$config = $context->getConfig();
    	$identifier = $this->params()->fromRoute('identifier');
    	echo json_encode($context->getConfig('test/'.$identifier), JSON_PRETTY_PRINT);
    	return $this->response;
    }
}
