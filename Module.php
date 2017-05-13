<?php
namespace PpitLearning;

use PpitCore\Model\GenericTable;
use PpitLearning\Model\Evaluation;
use PpitLearning\Model\Test;
use PpitLearning\Model\TestResult;
use PpitLearning\Model\TestSession;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'PpitLearning\Model\EvaluationTable' =>  function($sm) {
                    $tableGateway = $sm->get('EvaluationTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'EvaluationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Evaluation());
                    return new TableGateway('learning_evaluation', $dbAdapter, null, $resultSetPrototype);
                },
                'PpitLearning\Model\TestTable' =>  function($sm) {
                    $tableGateway = $sm->get('TestTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'TestTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Test());
                    return new TableGateway('learning_test', $dbAdapter, null, $resultSetPrototype);
                },
                'PpitLearning\Model\TestResultTable' =>  function($sm) {
                    $tableGateway = $sm->get('TestResultTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'TestResultTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TestResult());
                    return new TableGateway('learning_test_result', $dbAdapter, null, $resultSetPrototype);
                },
                'PpitLearning\Model\TestSessionTable' =>  function($sm) {
                    $tableGateway = $sm->get('TestSessionTableGateway');
                    $table = new GenericTable($tableGateway);
                    return $table;
                },
                'TestSessionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new TestSession());
                    return new TableGateway('learning_test_session', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
