<?php
namespace PpitLearning;

return array(
    'controllers' => array(
        'invokables' => array(
            'PpitLearning\Controller\TestResult' => 'PpitLearning\Controller\TestResultController',
            'PpitLearning\Controller\Evaluation' => 'PpitLearning\Controller\EvaluationController',
        ),
    ),
 
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
        	'testResult' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/test-result',
                    'defaults' => array(
                        'controller' => 'PpitLearning\Controller\TestResult',
                        'action'     => 'index',
                    ),
                ),
            	'may_terminate' => true,
            		'child_routes' => array(
            				'index' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/index',
            								'defaults' => array(
            										'action' => 'index',
            								),
            						),
            				),
            				'search' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/search',
            								'defaults' => array(
            										'action' => 'search',
            								),
            						),
            				),
            				'list' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/list',
	        								'defaults' => array(
	        										'action' => 'list',
	        								),
	        						),
	        				),
            				'export' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/export',
	        								'defaults' => array(
	        										'action' => 'export',
	        								),
	        						),
	        				),
            				'detail' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/detail[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'detail',
            								),
            						),
            				),
            				'update' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/update[/:id][/:act]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'update',
            								),
            						),
            				),
            				'perform' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/perform[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
	        								'defaults' => array(
	        										'action' => 'perform',
	        								),
	        						),
	        				),
            		),
            ),
        	'learningEvaluation' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/learning-evaluation',
                    'defaults' => array(
                        'controller' => 'PpitLearning\Controller\Evaluation',
                        'action'     => 'learnerIndex',
                    ),
                ),
            	'may_terminate' => true,
            		'child_routes' => array(
            				'export' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/export',
            								'defaults' => array(
            										'action' => 'export',
            								),
            						),
            				),
            				'learnerIndex' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/learner-index',
            								'defaults' => array(
            										'action' => 'learnerIndex',
            								),
            						),
            				),
            				'index' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/index[/:learner_id]',
            								'constraints' => array(
            										'learner_id' => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'index',
            								),
            						),
            				),
            				'update' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/update[/:learner_id][/:id]',
            								'constraints' => array(
            										'learner_id' => '[0-9]*',
            										'id' => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'update',
            								),
            						),
            				),
             				'detail' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/detail[/:id]',
            								'constraints' => array(
            										'id'     => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'detail',
            								),
            						),
            				),
            				'delete' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/delete[/:learner_id][/:id]',
            								'constraints' => array(
            										'learner_id' => '[0-9]*',
            										'id' => '[0-9]*',
            								),
            								'defaults' => array(
            										'action' => 'delete',
            								),
            						),
            				),
            		),
            	),
           ),
    ),
	'bjyauthorize' => array(
		// Guard listeners to be attached to the application event manager
		'guards' => array(
			'BjyAuthorize\Guard\Route' => array(

					array('route' => 'testResult', 'roles' => array('admin')),
					array('route' => 'testResult/index', 'roles' => array('admin')),
					array('route' => 'testResult/search', 'roles' => array('admin')),
					array('route' => 'testResult/list', 'roles' => array('admin')),
					array('route' => 'testResult/export', 'roles' => array('admin')),
					array('route' => 'testResult/detail', 'roles' => array('admin')),
					array('route' => 'testResult/update', 'roles' => array('admin')),
					array('route' => 'testResult/perform', 'roles' => array('guest')),
						
				// Learning evaluation
            	array('route' => 'learningEvaluation', 'roles' => array('trainer', 'admin')),
            	array('route' => 'learningEvaluation/export', 'roles' => array('trainer', 'admin')),
				array('route' => 'learningEvaluation/learnerIndex', 'roles' => array('trainer', 'admin')),
            	array('route' => 'learningEvaluation/index', 'roles' => array('trainer', 'admin')),
				array('route' => 'learningEvaluation/update', 'roles' => array('trainer', 'admin')), 
            	array('route' => 'learningEvaluation/detail', 'roles' => array('trainer', 'admin')), 
            	array('route' => 'learningEvaluation/delete', 'roles' => array('trainer', 'admin')),
			)
		)
	),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',       // On défini notre doctype
        'not_found_template'       => 'error/404',   // On indique la page 404
        'exception_template'       => 'error/index', // On indique la page en cas d'exception
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'ppit-learning' => __DIR__ . '/../view',
         ),
    ),
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type'     => 'phparray',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.php',
				'text_domain' => 'ppit-learning'
			),
	       	array(
	            'type' => 'phparray',
	            'base_dir' => './vendor/zendframework/zendframework/resources/languages/',
	            'pattern'  => 'fr/Zend_Validate.php',
	        ),
		),
	),
		
	'ppitLearningDependencies' => array(
	),

	'ppitApplications' => array(
			'p-pit-learning' => array(
					'labels' => array('fr_FR' => 'P-Pit Learning', 'en_US' => 'Learning by 2Pit'),
					'route' => 'testResult',
					'params' => array(),
					'roles' => array(
							'trainer' => array(
									'show' => true,
									'labels' => array(
											'en_US' => 'Trainer',
											'fr_FR' => 'Formateur',
									)
							),
					),
			),
	),
		
	'menus' => array(
			'p-pit-learning' => array(
					'testResult' => array(
							'route' => 'testResult/index',
							'params' => array(),
							'glyphicon' => 'glyphicon-education',
							'label' => array(
									'en_US' => 'Tests',
									'fr_FR' => 'Tests',
							),
					),
			),
	),

	// Test results
		
	'testResult' => array(
			'statuses' => array(),
			'properties' => array(
					'status' => array(
							'type' => 'select',
							'modalities' => array(
									'new' => array('en_US' => 'New', 'fr_FR' => 'Nouveau'),
									'performed' => array('en_US' => 'Performed', 'fr_FR' => 'Effectué'),
							),
							'labels' => array(
									'en_US' => 'Status',
									'fr_FR' => 'Statut',
							),
					),
					'identifier' => array(
							'type' => 'input',
							'labels' => array(
									'en_US' => 'Identifier',
									'fr_FR' => 'Identifiant',
							),
					),
					'caption' => array(
							'type' => 'input',
							'labels' => array(
									'en_US' => 'Title',
									'fr_FR' => 'Titre',
							),
					),
					'n_fn' => array(
							'type' => 'input',
							'labels' => array(
									'en_US' => 'Name',
									'fr_FR' => 'Nom',
							),
					),
					'expected_time' => array(
							'type' => 'date',
							'labels' => array(
									'en_US' => 'Expected date',
									'fr_FR' => 'Date prévue',
							),
					),
					'expected_duration' => array(
							'type' => 'input',
							'labels' => array(
									'en_US' => 'Expected duration',
									'fr_FR' => 'Durée prévue',
							),
					),
					'actual_time' => array(
							'type' => 'date',
							'labels' => array(
									'en_US' => 'Actual date',
									'fr_FR' => 'Date réelle',
							),
					),
					'actual_duration' => array(
							'type' => 'input',
							'labels' => array(
									'en_US' => 'Actual duration',
									'fr_FR' => 'Durée réelle',
							),
					),
			),
	),
	
	'testResult/index' => array(
			'title' => array('en_US' => 'Learning by 2Pit', 'fr_FR' => 'P-Pit Learning'),
	),
	
	'testResult/search' => array(
			'title' => array('en_US' => 'Test results', 'fr_FR' => 'Résultats de tests'),
			'todoTitle' => array('en_US' => 'recent', 'fr_FR' => 'récents'),
			'searchTitle' => array('en_US' => 'search', 'fr_FR' => 'recherche'),
			'main' => array(
					'status' => 'value',
					'caption' => 'contains',
					'n_fn' => 'contains',
					'expected_time' => 'range',
					'actual_time' => 'range',
			),
	),
	
	'testResult/list' => array(
			'identifier' => 'text',
			'n_fn' => 'text',
			'expected_time' => 'time',
			'status' => 'select',
	),
	
	'testResult/detail' => array(
			'title' => array('en_US' => 'Test result detail', 'fr_FR' => 'Détail du résultat de test'),
			'displayAudit' => true,
	),
	
	'testResult/update' => array(
			'status' => array('mandatory' => true),
	),
	
	'testResult/export' => array(
			'identifier' => 'text',
			'n_fn' => 'text',
			'expected_time' => 'time',
			'status' => null,
	),
);
