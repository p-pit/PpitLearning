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
            				'subscribe' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/subscribe[/:test_session_id]',
            								'constraints' => array(
            										'test_session_id'     => '[0-9]*',
            								),
	        								'defaults' => array(
	        										'action' => 'subscribe',
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
				array('route' => 'testResult/subscribe', 'roles' => array('guest')),
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

	// Axis
	
	'testResult/description' => array(
	        'title' => array(
		            'en_US' => 'Example test',
            		'fr_FR' => 'Test exemple',
        	),
	        'author' => array(
		            'en_US' => array(
			                'text' => '© 2017 Copyright: P-Pit',
		            ),
		            'fr_FR' => array(
		                	'text' => '© 2017 Copyright: P-Pit',
		            ),
	        ),
	),
		
	// Axis

	'testResult/axes' => array(
			"culture" => array(
					'segmentation' => array(
							'novice' => array(
									'limit' => 0.3,
									'label' => array('en_US' => '', 'fr_FR' => ''),
							),
							'specialist' => array(
									'limit' => 0.7,
									'label' => array('en_US' => 'Not so bad!', 'fr_FR' => 'Pas si mal !'),
							),
							'expert' => array(
									'limit' => 1.0,
									'label' => array('en_US' => 'You\'re the boss!', 'fr_FR' => 'C\'est toi le(la) chef !'),
							),
					),
					'categories' => array(
							'mythology' => array(
									'label' => array(
											'en_US' => 'Mythology',
											'fr_FR' => "Mythologie",
									),
							),
					),
			)
	),

	'rules' => array(
			'en_US' => array(
					'text' => '<h2>Rules of the test</h2><p>The test will start automatically in 2 minutes. It is presented as a series of multiple choice questions, illustrated with a support, written text or audio record.</p><p>You have 5 minutes in order to answer to the questions. Your answers are automatically registered, no matter if you have not enough time to click on the <em>Submit definitely</em> button.<p></p>At the end of the test, your score is displayed with the correction.</p><p>It\'s up to you and good luck!</p>',
					'image' => array(
							'src' => 'img/P-PIT/IMG_2180.jpg',
							'width' => '200',
					),
			),
			'fr_FR' => array(
					'text' => '<h2>Règles du test</h2><p>Le test démarrera automatiquement dans 2 minutes. Il se présente sous la forme d\'une série de questions à choix multiples, illustrées par un support, texte écrit ou enregistrement audio.</p><p>Vous disposez de 5 minutes pour répondre aux questions. Vos réponses sont enregistrées automatiquement, pas d\'inquiétude si vous n\'avez pas le temps de cliquer sur le bouton <em>Enregistrer définitivement</em> dans le délai imparti.<p></p>A la fin du test, votre score s\'affiche avec le corrigé.</p><p>A vous de jouer et bon courage !</p>',
					'image' => array(
							'src' => 'img/P-PIT/IMG_2180.jpg',
							'width' => '200',
					),
			),
	),
	
	'questions' => array(),
		
	// Test results
		
	'testResult' => array(
			'statuses' => array(),
			'properties' => array(
					'status' => array(
							'type' => 'select',
							'modalities' => array(
									'new' => array('en_US' => 'New', 'fr_FR' => 'Nouveau'),
									'in_progress' => array('en_US' => 'New', 'fr_FR' => 'En cours'),
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
					'test_session_id' => array(
							'type' => 'primitive',
							'labels' => array(
									'en_US' => 'Training session',
									'fr_FR' => 'Session de formation',
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
			'test_session_id' => array('mandatory' => true),
	),
	
	'testResult/export' => array(
			'identifier' => 'text',
			'n_fn' => 'text',
			'expected_time' => 'time',
			'status' => null,
	),
);
