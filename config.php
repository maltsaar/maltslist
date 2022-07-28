<?php

// change this
$database = "maltslist.sqlite3"; // name of the database file in the db/ directory
$siteUrl = "https://list.wavy.ws";
$ssoEnabled = true; // this determines if the logout button gets displayed
$ssoUrlLogout = "https://sso.wavy.ws/logout"; // this is used exclusively for the logout button

// don't change anything starting from here
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
