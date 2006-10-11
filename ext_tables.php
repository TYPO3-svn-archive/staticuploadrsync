<?php
/* $Id: ext_tables.php 560 2005-11-01 16:09:30Z a-otto $ */
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::addService($_EXTKEY,  'staticUpload' /* sv type */,  'tx_staticuploadrsync_sv1' /* sv key */,
		array(

			'title' => 'staticUpload: Rsync',
			'description' => 'Uses Rsync.',

			'subtype' => 'rsync',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => 'unix',
			'exec' => 'rsync',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_staticuploadrsync_sv1.php',
			'className' => 'tx_staticuploadrsync_sv1',
		)
	);
?>