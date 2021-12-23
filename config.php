<?php

$database = "maltslist.sqlite3"; // name of the database file in the database/ directory

return array(
	'rootLogger' => array(
		'appenders' => array('default'),
	),
	'appenders' => array(
		'default' => array(
			'class' => 'LoggerAppenderFile',
			'layout' => array(
				'class' => 'LoggerLayoutPattern'
			),
			'params' => array(
				'file' => '/var/www/maltslist/maltslist.log',
				'append' => true
			)
		)
	)
);