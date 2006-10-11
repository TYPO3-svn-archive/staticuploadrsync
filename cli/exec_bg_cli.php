<?php
/* $Id: exec_bg_cli.php 560 2005-11-01 16:09:30Z a-otto $ */
	// Defining circumstances for CLI mode:
define('TYPO3_cliMode', TRUE);

	// Defining PATH_thisScript here: Must be the ABSOLUTE path of this script in the right context:
	// This will work as long as the script is called by it's absolute path!
// define('PATH_thisScript',$_ENV['_']?$_ENV['_']:$_SERVER['_']);
define('PATH_thisScript',$_SERVER['argv'][0]);

	// Change working directory to the directory of the script.
chdir( dirname( PATH_thisScript ) );

	// Include configuration file:
require(dirname(PATH_thisScript).'/conf.php');

	// Include init file:
require(dirname(PATH_thisScript).'/'.$BACK_PATH.'init.php');



# HERE you run your application!
define( 'TYPO3_MODE', 'BE' );

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');

require_once(PATH_t3lib."config_default.php");
if (!defined ("TYPO3_db")) 	die ("The configuration file was not included.");

// Get the configuration settings and logfile name from argv
$conf = unserialize( base64_decode( $argv[1] ) );
$logfile = $argv[2];


require_once(PATH_t3lib.'class.t3lib_db.php');		// The database library
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');

// Connect to the database, needed at least for t3lib_extmgm to work
$result = $GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
if (!$result)	{
	die("Couldn't connect to database at ".TYPO3_db_host);
}
$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

// Include the language definitions
require_once(t3lib_extMgm::extPath('lang').'lang.php');
$LANG = t3lib_div::makeInstance('language');
$LANG->init($conf['lang']);
$GLOBALS['LANG']->includeLLFile('EXT:dkd_staticupload/mod1/locallang.php');

	/**
	* Simple function to replicate PHP 5 behaviour
	*/
	function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}


	function doTransfer( $conf, $logfile ) {
		if ( is_array( $conf ) ) {
			$timeStart = microtime_float();

			$cmd= sprintf( '%srsync %s %s %s@%s:%s  2>&1', $conf['path_to_scp'], $conf['options'], $conf['local_dir'], $conf['username'], $conf['server'], $conf['remote_dir'] );

			$output = array();
			$return = (int) 0;

			exec( $cmd, $output, $return );

			$timeEnd = microtime_float();

			$time = $timeEnd - $timeStart;

			if ( !empty( $conf['logfile'] ) ) {
				$fp = fopen( $conf['logfile'], 'ab' );

				fwrite( $fp, sprintf( 'Return value: %d%s', $return, chr( 10 ) ) );
				fwrite( $fp, sprintf( 'Time needed: %f seconds%s', $time, chr( 10 ) ) );

				foreach( $conf as $key => $var ) {
					fwrite( $fp, sprintf( '[%s]: %s%s', $key, $var, chr( 10 ) ) );
				}

				fwrite( $fp, sprintf( '%s[cmd]: %s%s', chr( 10 ), $cmd, chr( 10 ) ) );

				foreach( $output as $var ) {
					fwrite( $fp, sprintf( '%s%s', $var, chr( 10 ) ) );
				}

				fclose( $fp );
			}

			// Compile message parts for notification eMail
			$messagePart1 = array();
			$messagePart2 = array();
			foreach( $conf as $key => $var ) {
				$messagePart1[] = sprintf( '[%s]: %s', $key, $var );
			}
			$messagePart1[] = sprintf( '[cmd]: %s', $cmd );

			foreach( $output as $var ) {
				$messagePart2[] = sprintf( '%s', $var );
			}

			$subject = sprintf( $GLOBALS['LANG']->getLL( 'function2.email_subject', 'Notification for: %s' ), $conf['server'] );
			$message = sprintf( $GLOBALS['LANG']->getLL( 'function2.email_message', 'Time needed: %d Configuration: %s Return: %s' ), $time, implode( chr( 10 ), $messagePart1 ), implode( chr( 10 ), $messagePart2 ) );

			// Send the notification eMail
			t3lib_div::plainMailEncoded(
				$conf['email'],
				$subject,
				$message,
				'',
				'quoted-printable',
				'ISO-8859-1',
				0
			);
		}else{
			if ( !empty( $logfile ) ) {
				$fp = fopen( $logfile, 'ab' );
				fwrite( $fp, sprintf( 'Error: No configuration found.%s', chr( 10 ) ) );
				fclose( $fp );
			}

			// Send the notification eMail
			$subject = sprintf( $GLOBALS['LANG']->getLL( 'function2.email_subject', 'Notification for: %s' ), $conf['server'] );

			t3lib_div::plainMailEncoded(
				$conf['email'],
				$subject,
				$GLOBALS['LANG']->getLL( 'function2.email_message_error', 'No configuration found.' ),
				'',
				'quoted-printable',
				'ISO-8859-1',
				0
			);
		}
	}


	doTransfer( $conf, $logfile );

?>
