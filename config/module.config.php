<?php
namespace PpitLearning;

return array(
    'controllers' => array(
        'invokables' => array(
            'PpitLearning\Controller\Test' => 'PpitLearning\Controller\TestController',
        	'PpitLearning\Controller\TestEvent' => 'PpitLearning\Controller\TestEventController',
        	'PpitLearning\Controller\TestResult' => 'PpitLearning\Controller\TestResultController',
            'PpitLearning\Controller\Evaluation' => 'PpitLearning\Controller\EvaluationController',
        ),
    ),
 
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
        	'test' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/test',
                    'defaults' => array(
                        'controller' => 'PpitLearning\Controller\Test',
                        'action'     => 'index',
                    ),
                ),
            	'may_terminate' => true,
            		'child_routes' => array(
            				'deserialize' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/deserialize[/:id]',
            								'defaults' => array(
            										'action' => 'deserialize',
            								),
            						),
            				),
            				'serialize' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/serialize[/:identifier]',
            								'defaults' => array(
            										'action' => 'serialize',
            								),
            						),
            				),
            		),
        	),
        	'testEvent' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/test-event',
                    'defaults' => array(
                        'controller' => 'PpitLearning\Controller\TestEvent',
                        'action'     => 'index',
                    ),
                ),
            	'may_terminate' => true,
            		'child_routes' => array(
            				'index' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/index[/:type]',
            								'defaults' => array(
            										'action' => 'index',
            								),
            						),
            				),
            		),
        	),
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
            								'route' => '/index[/:type]',
            								'defaults' => array(
            										'action' => 'index',
            								),
            						),
            				),
            				'search' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/search[/:type]',
            								'defaults' => array(
            										'action' => 'search',
            								),
            						),
            				),
            				'list' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/list[/:type]',
	        								'defaults' => array(
	        										'action' => 'list',
	        								),
	        						),
	        				),
            				'export' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/export[/:type]',
	        								'defaults' => array(
	        										'action' => 'export',
	        								),
	        						),
	        				),
            				'distribute' => array(
	        						'type' => 'segment',
	        						'options' => array(
	        								'route' => '/distribute[/:type]',
	        								'defaults' => array(
	        										'action' => 'distribute',
	        								),
	        						),
	        				),
            				'detail' => array(
            						'type' => 'segment',
            						'options' => array(
            								'route' => '/detail[/:type][/:id]',
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
            								'route' => '/update[/:type][/:id][/:act]',
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
	        								'route' => '/subscribe[/:type][/:test_session_id]',
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
	        								'route' => '/perform[/:type][/:id]',
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

				array('route' => 'test/deserialize', 'roles' => array('admin')),
				array('route' => 'test/serialize', 'roles' => array('admin')),
						
				array('route' => 'testEvent', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testEvent/index', 'roles' => array('sales_manager', 'admin')),
						
				array('route' => 'testResult', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/index', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/search', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/list', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/export', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/distribute', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/detail', 'roles' => array('sales_manager', 'admin')),
				array('route' => 'testResult/update', 'roles' => array('sales_manager', 'admin')),
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

	'ppitCoreDependencies' => array(
			'test_result' => new \PpitLearning\Model\TestResult,
	),
		
	'ppitLearningDependencies' => array(
	),

	'ppitApplications' => array(
			'p-pit-learning' => array(
					'labels' => array('fr_FR' => 'P-Pit Learning', 'en_US' => 'Learning by 2Pit'),
					'default' => 'testResult',
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
		
	'menus/p-pit-learning' => array(
					'test_account' => array(
							'route' => 'account/index',
							'params' => array('entry' => 'account', 'type' => 'generic', 'app' => 'p-pit-learning'),
							'glyphicon' => 'glyphicon-user',
							'label' => array(
									'en_US' => 'Accounts',
									'fr_FR' => 'Comptes',
							),
					),
					'teacher' => array(
							'route' => 'account/index',
							'params' => array('entry' => 'account', 'type' => 'teacher', 'app' => 'p-pit-learning'),
							'glyphicon' => 'glyphicon-user',
							'label' => array(
									'en_US' => 'Teachers',
									'fr_FR' => 'Professeurs',
							),
					),
					'testResult' => array(
							'route' => 'testResult/index',
							'params' => array('type' => 'generic'),
							'glyphicon' => 'glyphicon-education',
							'label' => array(
									'en_US' => 'Subscriptions',
									'fr_FR' => 'Inscriptions',
							),
					),
					'test_note' => array(
							'route' => 'testEvent/index',
							'params' => array('type' => 'test_note'),
							'glyphicon' => 'glyphicon-education',
							'label' => array(
									'en_US' => 'Global result',
									'fr_FR' => 'Résultat global',
							),
					),
					'test_detail' => array(
							'route' => 'testEvent/index',
							'params' => array('type' => 'test_detail'),
							'glyphicon' => 'glyphicon-education',
							'label' => array(
									'en_US' => 'Detailed results',
									'fr_FR' => 'Résultats détaillés',
							),
					),
	),

		// Teacher

		'core_account/teacher/property/status' => array(
				'type' => 'select',
				'modalities' => array(
						'new' => array('en_US' => 'Contact', 'fr_FR' => 'A contacter'),
						'follow-up' => array('en_US' => 'Follow up', 'fr_FR' => 'A relancer'),
						'answer' => array('en_US' => 'Answer', 'fr_FR' => 'Répondre'),
						'committed' => array('en_US' => 'Send contract', 'fr_FR' => 'Envoyer contrat'),
						'active' => array('en_US' => 'Active', 'fr_FR' => 'Actif'),
						'gone' => array('en_US' => 'Gone', 'fr_FR' => 'Parti'),
				),
				'labels' => array(
						'en_US' => 'Status',
						'fr_FR' => 'Statut',
				),
				'perspectives' => array(
						'account' => array('new', 'active', 'gone'),
				),
		),
		'core_account/teacher/property/name' => array(
				'type' => 'input',
				'labels' => array(
						'en_US' => 'Training institution',
						'fr_FR' => 'Organisme de formation',
				),
		),
		'core_account/teacher/property/callback_date' => array(
				'type' => 'date',
				'labels' => array(
						'en_US' => 'Callback date',
						'fr_FR' => 'Date de rappel',
				),
		),
		'core_account/teacher/property/property_1' => array(
				'type' => 'input',
				'labels' => array(
						'en_US' => 'External link',
						'fr_FR' => 'Lien externe',
				),
		),
		'core_account/teacher/property/property_2' => array(
				'type' => 'number',
				'minValue' => 0,
				'maxValue' => 99999,
				'labels' => array(
						'en_US' => 'Hourly compensation',
						'fr_FR' => 'Rémunération horaire',
				),
		),
		'core_account/teacher/property/property_3' => array(
				'type' => 'input',
				'labels' => array(
						'en_US' => 'Course family',
						'fr_FR' => 'Famille de cours',
				),
		),
		'core_account/teacher/property/json_property_1' => array(
				'type' => 'structure',
				'max_occurences' => 3,
				'fields' => array(
					'title' => array(
						'type' => 'input',
						'mandatory' => true,
						'labels' => array('en_US' => 'Title', 'fr_FR' => 'Titre'),
					),
					'level' => array(
						'type' => 'input',
						'mandatory' => true,
						'labels' => array('en_US' => 'Teaching level', 'fr_FR' => 'Niveau d\'enseignement'),
					),
					'hours' => array(
						'type' => 'time',
						'min_value' => 0,
						'max_value' => 9999,
						'mandatory' => true,
						'labels' => array('en_US' => 'Number of hours', 'fr_FR' => 'Nombre d\'heures'),
					),
					'description' => array(
						'type' => 'textarea',
						'mandatory' => true,
						'labels' => array('en_US' => 'Description', 'fr_FR' => 'Description'),
					),
				),
				'labels' => array(
						'en_US' => 'Courses',
						'fr_FR' => 'Cours',
				),
		),
		
		'core_account/teacher' => array(
				'properties' => array(
						'title_1' => array(
								'definition' => 'inline',
								'type' => 'title',
								'labels' => array(
										'en_US' => 'TEACHER IDENTIFICATION',
										'fr_FR' => 'IDENTIFICATION DU PROFESSEUR',
								),
						),
						'title_2' => array(
								'definition' => 'inline',
								'type' => 'title',
								'labels' => array(
										'en_US' => 'COMMENTS',
										'fr_FR' => 'COMMENTAIRES',
								),
						),
						'status' => array('definition' => 'core_account/teacher/property/status', 'mandatory' => true),
						'place_id' => array('definition' => 'core_account/generic/property/place_id'),
						'name' => array('definition' => 'core_account/teacher/property/name'),
						'photo_link_id' => array('definition' => 'core_account/generic/property/photo_link_id'),
						'basket' => array('definition' => 'core_account/generic/property/basket'),
						'contact_1_id' => array('definition' => 'core_account/generic/property/contact_id'),
						'contact_1_status' => array('definition' => 'core_account/generic/property/contact_status'),
						'org' => array('definition' => 'core_account/generic/property/org'),
						'n_title' => array('definition' => 'core_account/generic/property/n_title', 'mandatory' => true),
						'n_first' => array('definition' => 'core_account/generic/property/n_first', 'mandatory' => true),
						'n_last' => array('definition' => 'core_account/generic/property/n_last', 'mandatory' => true),
						'email' => array('definition' => 'core_account/generic/property/email', 'mandatory' => true),
						'tel_work' => array('definition' => 'core_account/generic/property/tel_work'),
						'tel_cell' => array('definition' => 'core_account/generic/property/tel_cell'),
						'adr_street' => array('definition' => 'core_account/generic/property/adr_street'),
						'adr_extended' => array('definition' => 'core_account/generic/property/adr_extended'),
						'adr_post_office_box' => array('definition' => 'core_account/generic/property/adr_post_office_box'),
						'adr_zip' => array('definition' => 'core_account/generic/property/adr_zip'),
						'adr_city' => array('definition' => 'core_account/generic/property/adr_city'),
						'adr_state' => array('definition' => 'core_account/generic/property/adr_state'),
						'adr_country' => array('definition' => 'core_account/generic/property/adr_country'),
						'contact_2_id' => array('definition' => 'core_account/generic/property/contact_id'),
						'contact_2_status' => array('definition' => 'core_account/generic/property/contact_status'),
						'n_title_2' => array('definition' => 'core_account/generic/property/n_title'),
						'n_first_2' => array('definition' => 'core_account/generic/property/n_first'),
						'n_last_2' => array('definition' => 'core_account/generic/property/n_last'),
						'email_2' => array('definition' => 'core_account/generic/property/email'),
						'tel_work_2' => array('definition' => 'core_account/generic/property/tel_work'),
						'tel_cell_2' => array('definition' => 'core_account/generic/property/tel_cell'),
						'adr_street_2' => array('definition' => 'core_account/generic/property/adr_street'),
						'adr_extended_2' => array('definition' => 'core_account/generic/property/addr_extended'),
						'adr_post_office_box_2' => array('definition' => 'core_account/generic/property/adr_post_office_box'),
						'adr_zip_2' => array('definition' => 'core_account/generic/property/adr_zip'),
						'adr_city_2' => array('definition' => 'core_account/generic/property/adr_city'),
						'adr_state_2' => array('definition' => 'core_account/generic/property/adr_state'),
						'adr_country_2' => array('definition' => 'core_account/generic/property/adr_country'),
						'contact_3_id' => array('definition' => 'core_account/generic/property/contact_id'),
						'contact_3_status' => array('definition' => 'core_account/generic/property/contact_status'),
						'n_title_3' => array('definition' => 'core_account/generic/property/n_title'),
						'n_first_3' => array('definition' => 'core_account/generic/property/n_first'),
						'n_last_3' => array('definition' => 'core_account/generic/property/n_last'),
						'email_3' => array('definition' => 'core_account/generic/property/email'),
						'tel_work_3' => array('definition' => 'core_account/generic/property/tel_work'),
						'tel_cell_3' => array('definition' => 'core_account/generic/property/tel_cell'),
						'adr_street_3' => array('definition' => 'core_account/generic/property/adr_street'),
						'adr_extended_3' => array('definition' => 'core_account/generic/property/addr_extended'),
						'adr_post_office_box_3' => array('definition' => 'core_account/generic/property/adr_post_office_box'),
						'adr_zip_3' => array('definition' => 'core_account/generic/property/adr_zip'),
						'adr_city_3' => array('definition' => 'core_account/generic/property/adr_city'),
						'adr_state_3' => array('definition' => 'core_account/generic/property/adr_state'),
						'adr_country_3' => array('definition' => 'core_account/generic/property/adr_country'),
						'contact_4_id' => array('definition' => 'core_account/generic/property/contact_id'),
						'contact_4_status' => array('definition' => 'core_account/generic/property/contact_status'),
						'n_title_4' => array('definition' => 'core_account/generic/property/n_title'),
						'n_first_4' => array('definition' => 'core_account/generic/property/n_first'),
						'n_last_4' => array('definition' => 'core_account/generic/property/n_last'),
						'email_4' => array('definition' => 'core_account/generic/property/email'),
						'tel_work_4' => array('definition' => 'core_account/generic/property/tel_work'),
						'tel_cell_4' => array('definition' => 'core_account/generic/property/tel_cell'),
						'adr_street_4' => array('definition' => 'core_account/generic/property/adr_street'),
						'adr_extended_4' => array('definition' => 'core_account/generic/property/addr_extended'),
						'adr_post_office_box_4' => array('definition' => 'core_account/generic/property/adr_post_office_box'),
						'adr_zip_4' => array('definition' => 'core_account/generic/property/adr_zip'),
						'adr_city_4' => array('definition' => 'core_account/generic/property/adr_city'),
						'adr_state_4' => array('definition' => 'core_account/generic/property/adr_state'),
						'adr_country_4' => array('definition' => 'core_account/generic/property/adr_country'),
						'contact_5_id' => array('definition' => 'core_account/generic/property/contact_id'),
						'contact_5_status' => array('definition' => 'core_account/generic/property/contact_status'),
						'n_title_5' => array('definition' => 'core_account/generic/property/n_title'),
						'n_first_5' => array('definition' => 'core_account/generic/property/n_first'),
						'n_last_5' => array('definition' => 'core_account/generic/property/n_last'),
						'email_5' => array('definition' => 'core_account/generic/property/email'),
						'tel_work_5' => array('definition' => 'core_account/generic/property/tel_work'),
						'tel_cell_5' => array('definition' => 'core_account/generic/property/tel_cell'),
						'adr_street_5' => array('definition' => 'core_account/generic/property/adr_street'),
						'adr_extended_5' => array('definition' => 'core_account/generic/property/addr_extended'),
						'adr_post_office_box_5' => array('definition' => 'core_account/generic/property/adr_post_office_box'),
						'adr_zip_5' => array('definition' => 'core_account/generic/property/adr_zip'),
						'adr_city_5' => array('definition' => 'core_account/generic/property/adr_city'),
						'adr_state_5' => array('definition' => 'core_account/generic/property/adr_state'),
						'adr_country_5' => array('definition' => 'core_account/generic/property/adr_country'),
						'opening_date' => array('definition' => 'core_account/generic/property/opening_date'),
						'closing_date' => array('definition' => 'core_account/generic/property/closing_date'),
						'callback_date' => array('definition' => 'core_account/teacher/property/callback_date'),
						'priority' => array('definition' => 'core_account/generic/property/priority'),
						'origine' => array('definition' => 'core_account/generic/property/origine'),
						'contact_history' => array('definition' => 'core_account/generic/property/contact_history'),
						'notification_time' => array('definition' => 'core_account/generic/property/notification_time'),
						'property_1' => array('definition' => 'core_account/teacher/property/property_1'),
						'property_2' => array('definition' => 'core_account/teacher/property/property_2'),
						'property_3' => array('definition' => 'core_account/teacher/property/property_3'),
						'property_4' => array('definition' => 'core_account/generic/property/property_4'),
						'property_5' => array('definition' => 'core_account/generic/property/property_5'),
						'property_6' => array('definition' => 'core_account/generic/property/property_6'),
						'property_7' => array('definition' => 'core_account/generic/property/property_7'),
						'property_8' => array('definition' => 'core_account/generic/property/property_8'),
						'property_9' => array('definition' => 'core_account/generic/property/property_9'),
						'property_10' => array('definition' => 'core_account/generic/property/property_10'),
						'property_11' => array('definition' => 'core_account/generic/property/property_11'),
						'property_12' => array('definition' => 'core_account/generic/property/property_12'),
						'property_13' => array('definition' => 'core_account/generic/property/property_13'),
						'property_14' => array('definition' => 'core_account/generic/property/property_14'),
						'property_15' => array('definition' => 'core_account/generic/property/property_15'),
						'property_16' => array('definition' => 'core_account/generic/property/property_16'),
						'json_property_1' => array('definition' => 'core_account/teacher/property/json_property_1'),
						'json_property_2' => array('definition' => 'core_account/generic/property/json_property_2'),
						'json_property_3' => array('definition' => 'core_account/generic/property/json_property_3'),
						'json_property_4' => array('definition' => 'core_account/generic/property/json_property_4'),
						'json_property_5' => array('definition' => 'core_account/generic/property/json_property_5'),
						'comment_1' => array('definition' => 'core_account/teacher/property/comment_1'),
						'comment_2' => array('definition' => 'core_account/generic/property/comment_2'),
						'comment_3' => array('definition' => 'core_account/generic/property/comment_3'),
						'comment_4' => array('definition' => 'core_account/generic/property/comment_4'),
				),
				'acl' => array(
						'place_id' => array('application' => 'p-pit-admin', 'category' => 'place_id'),
				),
				'order' => 'name',
		),
		'core_account/index/teacher' => array(
				'title' => array('en_US' => 'Teachers', 'fr_FR' => 'Professeurs'),
		),
		'core_account/search/teacher' => array(
				'title' => array('en_US' => 'Teachers', 'fr_FR' => 'Professeurs'),
				'todoTitle' => array('en_US' => 'todo list', 'fr_FR' => 'todo list'),
				'properties' => array(
						'place_id' => ['multiple' => true],
						'status' => ['multiple' => true],
						'name' => [],
						'opening_date' => [],
						'callback_date' => [],
						'priority' => ['multiple' => true],
						'origine' => ['multiple' => true],
						'property_3' => [],
						'property_2' => [],
						'json_property_1' => [],
				),
		),
		'core_account/list/teacher' => array(
				'properties' => array(
						'status' => [],
						'name' => [],
						'property_3' => [],
						'property_2' => [],
						'opening_date' => [],
						'callback_date' => [],
						'priority' => [],
						'origine' => [],
						'place_id' => [],
				),
		),
		'core_account/detail/teacher' => array(
				'title' => array('en_US' => 'Contact detail', 'fr_FR' => 'Détail du contact'),
				'displayAudit' => true,
				'tabs' => array(
						'contact_1' => array(
								'definition' => 'inline',
								'route' => 'account/update',
								'params' => array('type' => ''),
								'labels' => array('en_US' => 'Main contact', 'fr_FR' => 'Contact principal'),
						),
						'contact_2' => array(
								'definition' => 'inline',
								'route' => 'account/updateContact',
								'params' => array('type' => '', 'contactNumber' => 2),
								'labels' => array('en_US' => 'Payment', 'fr_FR' => 'Règlement'),
						),
				),
		),
		'core_account/update/teacher' => array(
				'place_id' => ['mandatory' => false],
				'status' => ['mandatory' => true],
				'name' => ['mandatory' => false],
				'basket' => ['mandatory' => false],
				'opening_date' => ['mandatory' => false],
				'callback_date' => ['mandatory' => false],
				'origine' => ['mandatory' => false],
				'title_1' => [],
				'n_title' => ['mandatory' => false],
				'n_first' => ['mandatory' => true],
				'n_last' => ['mandatory' => true],
				'email' => ['mandatory' => false],
				'tel_work' => ['mandatory' => false],
				'tel_cell' => ['mandatory' => false],
				'adr_street' => ['mandatory' => false],
				'adr_zip' => ['mandatory' => false],
				'adr_city' => ['mandatory' => false],
				'title_2' => ['mandatory' => false],
				'property_1' => ['mandatory' => false],
				'property_2' => ['mandatory' => false],
				'property_3' => ['mandatory' => false],
				'json_property_1' => ['mandatory' => false],
				'contact_history' => ['mandatory' => false],
		),
		'core_account/updateContact/teacher' => array(
				'n_title' => array('mandatory' => false),
				'n_first' => array('mandatory' => false),
				'n_last' => array('mandatory' => false),
				'tel_work' => array('mandatory' => false),
				'tel_cell' => array('mandatory' => false),
				'email' => array('mandatory' => false),
				'adr_street' => array('mandatory' => false),
				'adr_extended' => array('mandatory' => false),
				'adr_zip' => array('mandatory' => false),
				'adr_post_office_box' => array('mandatory' => false),
				'adr_city' => array('mandatory' => false),
				'adr_state' => array('mandatory' => false),
				'adr_country' => array('mandatory' => false),
				'locale' => array('mandatory' => false),
		),
		'core_account/groupUpdate/teacher' => array(
				'status' => array('mandatory' => true),
				'callback_date' => array('mandatory' => false),
		),
		'core_account/post/teacher' => array(
				'place_identifier' => array('mandatory' => false),
				'name' => array('mandatory' => false),
				'n_title' => array('mandatory' => false),
				'n_last' => array('mandatory' => true),
				'n_first' => array('mandatory' => true),
				'email' => array('mandatory' => true),
				'request' => array('mandatory' => true),
				'request_comment' => array('mandatory' => false),
				'tel_work' => array('mandatory' => false),
				'tel_cell' => array('mandatory' => false),
				'adr_street' => array('mandatory' => false),
				'adr_extended' => array('mandatory' => false),
				'adr_zip' => array('mandatory' => false),
				'adr_post_office_box' => array('mandatory' => false),
				'adr_city' => array('mandatory' => false),
				'adr_state' => array('mandatory' => false),
				'adr_country' => array('mandatory' => false),
				'place_identifier' => array('mandatory' => false),
				'locale' => array('mandatory' => false),
		),
		'core_account/export/teacher' => array(
				'status' => [],
				'place_id' => [],
				'name' => [],
				'basket' => [],
				'opening_date' => [],
				'callback_date' => [],
				'priority' => [],
				'origine' => [],
				'n_title' => [],
				'n_first' => [],
				'n_last' => [],
				'email' => [],
				'tel_work' => [],
				'tel_cell' => [],
				'adr_street' => [],
				'adr_zip' => [],
				'adr_city' => [],
		
				'n_title_2' => [],
				'n_first_2' => [],
				'n_last_2' => [],
				'email_2' => [],
				'tel_work_2' => [],
				'tel_cell_2' => [],

				'json_property_1' => [],
				
				'contact_history' => [],
				'notification_time' => [],
		),
		
		'core_account/indexCard/teacher' => array(
				'title' => array('en_US' => 'Teacher index card', 'fr_FR' => 'Fiche professeur'),
				'header' => array(
						'place_id' => null,
						'status' => null,
						'origine' => null,
				),
				'1st-column' => array(
						'title' => 'title_1',
						'rows' => array(
								'name' => [],
								'n_title' => [],
								'n_first' => [],
								'n_last' => [],
								'email' => [],
								'tel_work' => [],
								'tel_cell' => [],
								'adr_street' => [],
								'adr_extended' => [],
								'adr_post_office_box' => [],
								'adr_zip' => [],
								'adr_city' => [],
								'adr_state' => [],
								'adr_country' => [],
						),
				),
				'2nd-column' => array(
						'title' => 'title_2',
						'rows' => array(
						),
				),
				'pdfDetailStyle' => '
<style>
table.note-report {
	font-size: 1em;
	border: 1px solid gray;
}
table.note-report th {
	color: #FFF;
	font-weight: bold;
	text-align: center;
	vertical-align: center;
	border: 1px solid gray;
	background-color: #006169;
}
		
table.note-report td {
	color: #666;
	border: 1px solid gray;
}
</style>
',
		),
		
	// Event

	'event/type' => array(
			'type' => 'select',
			'modalities' => array(
					'test_note' => array('en_US' => 'Global result', 'fr_FR' => 'Résultat global'),
					'test_detail' => array('en_US' => 'Detailed results', 'fr_FR' => 'Résultats détaillés'),
			),
			'default' => 'test_note',
			'labels' => array(
					'en_US' => 'Type',
					'fr_FR' => 'Type',
			),
	),
	'event/caption/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Test reference',
					'fr_FR' => 'Référence du test',
			),
	),
	'event/value/test_note' => array(
			'type' => 'number',
			'minValue' => 0,
			'maxValue' => 1000,
			'labels' => array(
					'en_US' => 'Score on this axis',
					'fr_FR' => 'Score sur cet axe',
			),
	),
	'event/property_1/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Axis',
					'fr_FR' => 'Axe',
			),
	),
	'event/property_2/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Category',
					'fr_FR' => 'Catégorie',
			),
	),
	'event/property_3/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Expected time',
					'fr_FR' => 'Heure prévue',
			),
	),
	'event/property_4/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Actual time',
					'fr_FR' => 'Heure réelle',
			),
	),
	'event/property_5/test_note' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Actual duration',
					'fr_FR' => 'Durée effective',
			),
	),
	'event/property_6/test_note' => array(
			'type' => 'textarea',
			'labels' => array(
					'en_US' => 'Answers',
					'fr_FR' => 'Réponses',
			),
	),
	'event/property_7/test_note' => array(
			'type' => 'textarea',
			'labels' => array(
					'en_US' => 'Note on the category',
					'fr_FR' => 'Note de la catégorie',
			),
	),
	'event/test_note' => array(
			'dimensions' => array(
			),
			'properties' => array(
					'status' => array('type' => 'specific', 'definition' => 'event/status'),
					'type' => array('type' => 'specific', 'definition' => 'event/type'),
					'identifier' => array('type' => 'specific', 'definition' => 'event/identifier'),
					'place_id' => array('type' => 'specific', 'definition' => 'event/place_identifier'),
					'place_identifier' => array('type' => 'specific', 'definition' => 'event/place_identifier'),
					'place_caption' => array('type' => 'specific', 'definition' => 'event/place_caption'),
					'n_fn' => array('type' => 'specific', 'definition' => 'event/n_fn'),
					'caption' => array('type' => 'specific', 'definition' => 'event/caption/test_note'),
					'description' => array('type' => 'specific', 'definition' => 'event/description'),
					'value' => array('type' => 'specific', 'definition' => 'event/value/test_note'),
					'property_1' => array('type' => 'specific', 'definition' => 'event/property_1/test_note'),
					'property_2' => array('type' => 'specific', 'definition' => 'event/property_2/test_note'),
					'property_3' => array('type' => 'specific', 'definition' => 'event/property_3/test_note'),
					'property_4' => array('type' => 'specific', 'definition' => 'event/property_4/test_note'),
					'property_5' => array('type' => 'specific', 'definition' => 'event/property_5/test_note'),
					'property_6' => array('type' => 'specific', 'definition' => 'event/property_6/test_note'),
					'property_7' => array('type' => 'specific', 'definition' => 'event/property_7/test_note'),
					'update_time' => array('type' => 'specific', 'definition' => 'event/update_time'),
			),
			'indicators' => array(),
	),
	'event/index/test_note'=> array(
			'title'=> array(
					'en_US' => 'P-Pit SynApps',
					'fr_FR' => 'P-Pit SynApps'
			)
	),
	'event/search/test_note'=> array(
			'title'=> array(
					'en_US' => 'Global result',
					'fr_FR' => 'Résultat global'
			),
			'todoTitle'=> array(
					'en_US' => 'to check',
					'fr_FR' => 'à vérifier'
			),
			'searchTitle'=> array(
					'en_US' => 'search',
					'fr_FR' => 'recherche'
			),
			'main'=> array(
					'caption' => 'contains',
					'property_1' => 'contains',
					'property_2' => 'contains',
					'n_fn' => 'contains',
					'value' => 'range',
			)
	),
	'event/list/test_note'=> array(
			'caption' => array('rendering' => 'text'),
			'property_1' => array('rendering' => 'text'),
			'property_2' => array('rendering' => 'text'),
			'n_fn' => array('rendering' => 'text'),
			'value' => array('rendering' => 'number'),
			'property_6' => array('rendering' => 'text'),
			'property_2' => array('rendering' => 'date'),
			'property_3' => array('rendering' => 'date'),
			'property_4' => array('rendering' => 'text'),
			'property_5' => array('rendering' => 'text'),
	),
	'event/masked/test_note'=> array(
	),
	'event/detail/test_note'=> array(
			'title'=> array(
					'en_US' => 'Zoom',
					'fr_FR' => 'Zoom'
			),
			'displayAudit' => true
	),
	'event/update/test_note'=> array(
			'place_id'=> array('mandatory' => true),
			'n_fn'=> array('mandatory' => false),
			'caption'=> array('mandatory' => false),
			'description'=> array('mandatory' => false),
			'property_1'=> array('mandatory' => false),
			'property_2'=> array('mandatory' => false),
			'value'=> array('mandatory' => false),
			'property_6'=> array('mandatory' => false),
			'property_2'=> array('mandatory' => false),
			'property_3'=> array('mandatory' => false),
			'property_4'=> array('mandatory' => false),
			'property_5'=> array('mandatory' => false),
	),
	
	'event/export/test_note'=> array(
			'identifier'=> 'A',
			'place_identifier'=> 'B',
			'n_fn'=> 'C',
			'caption'=> 'D',
			'description'=> 'E',
			'property_1'=> 'F',
			'property_2'=> 'G',
			'value'=> 'H',
			'property_6'=> 'I',
			'property_3'=> 'J',
			'property_4'=> 'K',
			'property_5'=> 'L',
	),

	// Detailed results
	'event/caption/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Question identifier',
					'fr_FR' => 'Identifiant question',
			),
	),
	'event/value/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Note',
					'fr_FR' => 'Note',
			),
	),
	'event/property_1/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Test session expected time',
					'fr_FR' => 'Date prévue de la session de test',
			),
	),
	'event/property_2/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Test result status',
					'fr_FR' => 'Statut du résultat de test',
			),
	),
	'event/property_3/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Actual begin time',
					'fr_FR' => 'Heure de début effective',
			),
	),
	'event/property_4/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Actual duration',
					'fr_FR' => 'Durée effective',
			),
	),
	'event/property_5/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Test reference',
					'fr_FR' => 'Référence du test',
			),
	),
	'event/property_6/test_detail' => array(
			'type' => 'textarea',
			'labels' => array(
					'en_US' => 'Question caption',
					'fr_FR' => 'Libellé question',
			),
	),
	'event/property_7/test_detail' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Answer number',
					'fr_FR' => 'Numéro de réponse',
			),
	),
	'event/test_detail' => array(
			'dimensions' => array(
			),
			'properties' => array(
					'status' => array('type' => 'specific', 'definition' => 'event/status'),
					'type' => array('type' => 'specific', 'definition' => 'event/type'),
					'identifier' => array('type' => 'specific', 'definition' => 'event/identifier'),
					'place_id' => array('type' => 'specific', 'definition' => 'event/place_identifier'),
					'place_identifier' => array('type' => 'specific', 'definition' => 'event/place_identifier'),
					'place_caption' => array('type' => 'specific', 'definition' => 'event/place_caption'),
					'community_name' => array('type' => 'specific', 'definition' => 'event/community_name'),
					'n_fn' => array('type' => 'specific', 'definition' => 'event/n_fn'),
					'caption' => array('type' => 'specific', 'definition' => 'event/caption/test_detail'),
					'description' => array('type' => 'specific', 'definition' => 'event/description'),
					'value' => array('type' => 'specific', 'definition' => 'event/value/test_detail'),
					'property_1' => array('type' => 'specific', 'definition' => 'event/property_1/test_detail'),
					'property_2' => array('type' => 'specific', 'definition' => 'event/property_2/test_detail'),
					'property_3' => array('type' => 'specific', 'definition' => 'event/property_3/test_detail'),
					'property_4' => array('type' => 'specific', 'definition' => 'event/property_4/test_detail'),
					'property_5' => array('type' => 'specific', 'definition' => 'event/property_5/test_detail'),
					'property_6' => array('type' => 'specific', 'definition' => 'event/property_6/test_detail'),
					'property_7' => array('type' => 'specific', 'definition' => 'event/property_7/test_detail'),
					'update_time' => array('type' => 'specific', 'definition' => 'event/update_time'),
			),
			'indicators' => array(),
	),
	'event/index/test_detail'=> array(
			'title'=> array(
					'en_US' => 'P-Pit Learning',
					'fr_FR' => 'P-Pit Learning'
			)
	),
	'event/search/test_detail'=> array(
			'title'=> array(
					'en_US' => 'Detailed result',
					'fr_FR' => 'Résultat détaillé'
			),
			'todoTitle'=> array(
					'en_US' => 'to check',
					'fr_FR' => 'à vérifier'
			),
			'searchTitle'=> array(
					'en_US' => 'search',
					'fr_FR' => 'recherche'
			),
			'main'=> array(
					'identifier' => 'contains',
					'n_fn' => 'contains',
					'property_5' => 'contains',
					'caption' => 'contains',
			)
	),
	'event/list/test_detail'=> array(
			'property_5' => array('rendering' => 'text'),
			'n_fn' => array('rendering' => 'text'),
			'caption' => array('rendering' => 'text'),
			'property_7' => array('rendering' => 'text'),
	),
	'event/masked/test_detail'=> array(
	),
	'event/detail/test_detail'=> array(
			'title'=> array(
					'en_US' => 'Zoom',
					'fr_FR' => 'Zoom'
			),
			'displayAudit' => true
	),
	'event/update/test_detail'=> array(
			'identifier'=> array('mandatory' => true),
			'place_id'=> array('mandatory' => true),
			'n_fn'=> array('mandatory' => false),
			'property_5'=> array('mandatory' => false),
			'caption'=> array('mandatory' => false),
			'property_7'=> array('mandatory' => false),
			'description'=> array('mandatory' => false),
			'property_1'=> array('mandatory' => false),
			'property_2'=> array('mandatory' => false),
			'property_3'=> array('mandatory' => false),
			'property_4'=> array('mandatory' => false),
	),
	
	'event/export/test_detail'=> array(
			'identifier'=> 'A',
			'place_identifier'=> 'B',
			'n_fn'=> 'C',
			'property_5'=> 'D',
			'caption'=> 'E',
			'property_7'=> 'F',
			'description'=> 'G',
			'property_1'=> 'H',
			'property_2'=> 'I',
			'property_3'=> 'J',
			'property_4'=> 'K',
	),

	// Test
	
	'test/type/generic' => array(
			'type' => 'select',
			'modalities' => array(
			),
			'labels' => array(
					'en_US' => 'Type',
					'fr_FR' => 'Type',
			),
	),
	'test/identifier/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Identifier',
					'fr_FR' => 'Identifiant',
			),
	),
	'test/place_id/generic' => array(
			'type' => 'list',
			'labels' => array(
					'en_US' => 'Place',
					'fr_FR' => 'Etablissement',
			),
	),
	'test/place_identifier/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Place code',
					'fr_FR' => 'Code établissement',
			),
	),
	'test/place_caption/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Place',
					'fr_FR' => 'Etablissement',
			),
	),
	'test/caption/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Caption',
					'fr_FR' => 'Libellé',
			),
	),
	'test/part_identifier/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Part',
					'fr_FR' => 'Partie',
			),
	),

	// testSession
	
	'testSession/test_id/generic' => array(
			'type' => 'list',
			'labels' => array(
					'en_US' => 'Test',
					'fr_FR' => 'Test',
			),
	),
	'testSession/expected_date/generic' => array(
			'type' => 'date',
			'labels' => array(
					'en_US' => 'Expected date',
					'fr_FR' => 'Date prévue',
			),
	),
	'testSession/expected_time/generic' => array(
			'type' => 'time',
			'labels' => array(
					'en_US' => 'Expected time',
					'fr_FR' => 'Heure prévue',
			),
	),
	'testSession/expected_time_zone/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Time zone',
					'fr_FR' => 'Fuseau horaire',
			),
	),
	'testSession/expected_duration/generic' => array(
			'type' => 'number',
			'minValue' => 0,
			'maxValue' => 86400, // 24h
			'labels' => array(
					'en_US' => 'Expected duration',
					'fr_FR' => 'Durée prévue',
			),
	),
	'testSession/expected_location/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Location',
					'fr_FR' => 'Lieu',
			),
	),
	'testSession/expected_latitude/generic' => array(
			'type' => 'number',
			'minValue' => -90,
			'maxValue' => 90,
			'labels' => array(
					'en_US' => 'Latitude',
					'fr_FR' => 'Latitude',
			),
	),
	'testSession/expected_longitude/generic' => array(
			'type' => 'number',
			'minValue' => 0,
			'maxValue' => 359.5999,
			'labels' => array(
					'en_US' => 'Longitude',
					'fr_FR' => 'Longitude',
			),
	),
	
	// testResult
	
	'testResult/status/generic' => array(
			'type' => 'select',
			'modalities' => array(
					'new' => array('en_US' => 'New', 'fr_FR' => 'Nouveau'),
					'in_progress' => array('en_US' => 'In progress', 'fr_FR' => 'En cours'),
					'performed' => array('en_US' => 'Performed', 'fr_FR' => 'Réalisé'),
			),
			'labels' => array(
					'en_US' => 'Status',
					'fr_FR' => 'Statut',
			),
	),
	'testResult/n_fn/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Name',
					'fr_FR' => 'Nom',
			),
	),
	'testResult/test_session_id/generic' => array(
			'type' => 'list',
			'labels' => array(
					'en_US' => 'Test session',
					'fr_FR' => 'Session de test',
			),
	),
	'testResult/actual_date/generic' => array(
			'type' => 'date',
			'labels' => array(
					'en_US' => 'Actual date',
					'fr_FR' => 'Date réelle',
			),
	),
	'testResult/actual_time/generic' => array(
			'type' => 'time',
			'labels' => array(
					'en_US' => 'Actual time',
					'fr_FR' => 'Heure réelle',
			),
	),
	'testResult/actual_time_zone/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Time zone',
					'fr_FR' => 'Fuseau horaire',
			),
	),
	'testResult/actual_duration/generic' => array(
			'type' => 'number',
			'minValue' => 0,
			'maxValue' => 86400, // 24h
			'labels' => array(
					'en_US' => 'Actual duration',
					'fr_FR' => 'Durée réelle',
			),
	),
	'testResult/actual_location/generic' => array(
			'type' => 'input',
			'labels' => array(
					'en_US' => 'Location',
					'fr_FR' => 'Lieu',
			),
	),
	'testResult/actual_latitude/generic' => array(
			'type' => 'number',
			'minValue' => -90,
			'maxValue' => 90,
			'labels' => array(
					'en_US' => 'Latitude',
					'fr_FR' => 'Latitude',
			),
	),
	'testResult/actual_longitude/generic' => array(
			'type' => 'number',
			'minValue' => 0,
			'maxValue' => 359.5999,
			'labels' => array(
					'en_US' => 'Longitude',
					'fr_FR' => 'Longitude',
			),
	),
	'testResult/update_time/generic' => array(
			'type' => 'datetime',
			'labels' => array(
					'en_US' => 'Update time',
					'fr_FR' => 'Heure de mise à jour',
			),
	),

	'testResult/generic' => array(
			'properties' => array(
					'status' => array('definition' => 'testResult/status/generic'),
					'identifier' => array('definition' => 'test/identifier/generic'),
					'place_id' => array('definition' => 'test/place_id/generic'),
					'place_identifier' => array('definition' => 'test/place_identifier/generic'),
					'place_caption' => array('definition' => 'test/place_caption/generic'),
					'caption' => array('definition' => 'test/caption/generic'),
					'test_id' => array('definition' => 'testSession/test_id/generic'),
					'test_session_id' => array('definition' => 'testResult/test_session_id/generic'),
					'part_identifier' => array('definition' => 'test/part_identifier/generic'),
					'n_title' => array('definition' => 'coreVcard/n_title'),
					'n_first' => array('definition' => 'coreVcard/n_first'),
					'n_last' => array('definition' => 'coreVcard/n_last'),
					'n_fn' => array('definition' => 'coreVcard/n_fn'),
					'email' => array('definition' => 'coreVcard/email'),
					'tel_cell' => array('definition' => 'coreVcard/tel_cell'),
					'expected_date' => array('definition' => 'testSession/expected_date/generic'),
					'expected_time' => array('definition' => 'testSession/expected_time/generic'),
					'expected_time_zone' => array('definition' => 'testSession/expected_time_zone/generic'),
					'expected_duration' => array('definition' => 'testSession/expected_duration/generic'),
					'expected_location' => array('definition' => 'testSession/expected_location/generic'),
					'expected_latitude' => array('definition' => 'testSession/expected_latitude/generic'),
					'expected_duration' => array('definition' => 'testSession/expected_duration/generic'),
					'actual_date' => array('definition' => 'testResult/actual_date/generic'),
					'actual_time' => array('definition' => 'testResult/actual_time/generic'),
					'actual_time_zone' => array('definition' => 'testResult/actual_time_zone/generic'),
					'actual_duration' => array('definition' => 'testResult/actual_duration/generic'),
					'actual_location' => array('definition' => 'testResult/actual_location/generic'),
					'actual_latitude' => array('definition' => 'testResult/actual_latitude/generic'),
					'actual_duration' => array('definition' => 'testResult/actual_duration/generic'),
					'update_time' => array('definition' => 'testResult/update_time/generic'),
			),
	),
	
	'testResult/index/generic' => array(
			'title' => array('en_US' => 'Learning by 2Pit', 'fr_FR' => 'P-Pit Learning'),
	),
	
	'testResult/search/generic' => array(
			'title' => array('en_US' => 'Test subscriptions', 'fr_FR' => 'Inscription au test'),
			'todoTitle' => array('en_US' => 'Coming or in progress', 'fr_FR' => 'En cours ou prévus'),
			'searchTitle' => array('en_US' => 'search', 'fr_FR' => 'recherche'),
			'properties' => array(
					'status' => null,
					'place_id' => null,
					'caption' => null,
					'n_fn' => null,
					'test_id' => null,
					'test_session_id' => null,
					'expected_date' => null,
					'actual_date' => null,
			),
	),
	
	'testResult/list/generic' => array(
			'properties' => array(
					'place_caption' => [],
					'caption' => [],
					'test_id' => [],
					'test_session_id' => [],
					'n_fn' => [],
					'expected_date' => [],
					'actual_date' => [],
					'status' => [],
			),
	),
	
	'testResult/detail/generic' => array(
			'title' => array('en_US' => 'Test result detail', 'fr_FR' => 'Détail du résultat de test'),
			'displayAudit' => true,
	),
	
	'testResult/update/generic' => array(
			'properties' => array(
					'place_id' => array('mandatory' => true, 'focus' => true),
					'n_title' => array('mandatory' => false),
					'n_first' => array('mandatory' => true),
					'n_last' => array('mandatory' => true),
					'email' => array('mandatory' => true),
					'tel_cell' => array('mandatory' => false),
					'test_session_id' => array('mandatory' => true),
			),
	),
	
	'testResult/export/generic' => array(
			'properties' => array(
					'identifier' => null,
					'status' => null,
					'place_identifier' => null,
					'place_caption' => null,
					'part_identifier' => null,
					'caption' => null,
					'n_title' => null,
					'n_first' => null,
					'n_last' => null,
					'n_fn' => null,
					'email' => null,
					'tel_cell' => null,
					'expected_date' => null,
					'expected_time' => null,
					'expected_duration' => null,
					'actual_date' => null,
					'actual_time' => null,
					'actual_duration' => null,
			),
	),
		
	'testResult/message/generic' => array(
			'from_mail' => 'postmaster@p-pit.fr',
			'from_name' => 'P-Pit',
			'cci' => array('support@p-pit.fr' => null),
			'subscribeTitle' => array(
					'en_US' => 'Your P-Pit Learning lesson',
					'fr_FR' => 'Votre leçon P-Pit Learning',
			),
			'subscribeText' => array(
					'en_US' => '
<p>Hello</p>
<p>Welcome in P-Pit Learning,</p>
<p>In order to access to your lesson, please follow this link: %s</p>',
					'fr_FR' => '
<p>Bonjour,</p>
<p>Bienvenue sur P-Pit Learning,</p>
<p>Afin d\'accéder à votre leçon, veuillez suivre ce lien : %s</p>',
			),
	),
		
	'demo' => array(
			'testResult/search/title' => array(
					'en_US' => '
<h4>Subscriptions list</h4>
<p>As a default, all the new subscriptions are presented in the list.</p>
<p>As soon as a criterion below is specified, the list switch in search mode.</p>
',
					'fr_FR' => '
<h4>Liste des inscriptions</h4>
<p>Par défaut, toutes les nouvelles inscriptions sont présentées dans la liste.</p>
<p>Dès lors qu\'un des critères ci-dessous est spécifié, le mode de recherche est automatiquement activé.</p>
',
			),
			'testResult/search' => array(
					'en_US' => '
<h4>Search button</h4>
<p>The search button refresh the list filtered according to the criteria below.</p>
',
					'fr_FR' => '
<h4>Bouton de recherche</h4>
<p>Le bouton de recherche rafraichit la liste filtrée sur les critères ci-dessous.</p>
',
			),
			'testResult/search/x' => array(
					'en_US' => '
<h4>Return in default mode</h4>
<p>The <code>x</code> button reinitializes all the search criteria and reset the list filtered on new subscriptions.</p>
',
					'fr_FR' => '
<h4>Retour au mode par défaut</h4>
<p>Le bouton <code>x</code> réinitialise tous les critères de recherche et ré-affiche la liste filtrée sur les nouvelles inscriptions.</p>
',
			),
			'testResult/search/export' => array(
					'en_US' => '
<h4>List export</h4>
<p>The list can be exported to Excel as it is presented: defaulting list or list resulting of a multi-criteria search.</p>
',
					'fr_FR' => '
<h4>Export de la liste</h4>
<p>La liste peut être exportée sous Excel telle que présentée : liste par défaut ou liste résultant d\'une recherche multi-critère.</p>
',
			),
			'testResult/list/ordering' => array(
					'en_US' => '
<h4>Ordering</h4>
<p>The list can be sorted according to each column in ascending or descending order.</p>
',
					'fr_FR' => '
<h4>Classement</h4>
<p>La liste peut être triée selon chaque colonne en ordre ascendant ou descendant.</p>
',
			),
			'testResult/list/add' => array(
					'en_US' => '
<h4>Adding of a new subscription</h4>
<p>The + button allows to add a new subscription.</p>
					',
					'fr_FR' => '
<h4>Ajout d\'une inscription</h4>
<p>Le bouton + permet l\'ajout d\une nouvelle inscription.</p>
					',
			),
			'testResult/list/detail' => array(
					'en_US' => '
<h4>Subscription détail</h4>
<p>The zoom-in button allows to access to the properties of a subscription.</p>
					',
					'fr_FR' => '
<h4>Détail d\'une inscription</h4>
<p>Le bouton zoom permet d\'accéder aux propriétés d\'une inscription.</p>
					',
			),
			'testResult/add' => array(
					'en_US' => '
<h4>New subscription</h4>
<p>Add a subscription by specifying the candidate coordinates and the test session.</p>
<p>The candidate receives by email his personal access link to the test.</p>
					',
					'fr_FR' => '
<h4>Nouvelle inscription</h4>
<p>Ajoutez une souscription en spécifiant les coordonnées du candidat et la session de test.</p>
<p>Le candidat reçoit un email contenant son lien personnel d\'accès au test.</p>
					',
			),
			'testResult/update' => array(
					'en_US' => '
<h4>Updating a subscription</h4>
<p>Accessing to the detail of a subscription allows to consult and possibly update the data.</p>
<p>Update a subscription implies that it will be reset. Previous recorded results are conceled and the statut is reset in order to allow the candidate to restart the test from the beginning.</p>
<p>Following this reset procedure, the candidate receives again by email his personal access link to the test.</p>
					',
					'fr_FR' => '
<h4>Modification d\'une inscription</h4>
<p>L\'accès au détail d\'une inscription permet de consulter et éventuellement en rectifier les données.</p>
<p>La modification d\'une inscription entraîne sa réinitialisation. Les résultats éventuellement déjà enregistrés sont annulés et le statut est repositionné en vue de permettre au candidat de relancer le test depuis le début.</p>
<p>Suite à cette réinitialisation, le candidat reçoit de nouveau l\'email contenant son lien personnel d\'accès au test.</p>
					',
			),
	),
);
