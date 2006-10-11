<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004 Andreas Otto (andreas.otto@dkd.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
* Service 'staticUpload: Rsync' for the 'staticuploadrsync' extension.
*
* @author Andreas Otto <andreas.otto@dkd.de>
* @version $Id: class.tx_staticuploadrsync_sv1.php 560 2005-11-01 16:09:30Z a-otto $
*/

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   47: class tx_staticuploadrsync_sv1 extends t3lib_svbase
 *   59:     function process( $conf )
 *  109:     function microtime_float()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

	require_once(PATH_t3lib.'class.t3lib_svbase.php');

	class tx_staticuploadrsync_sv1 extends t3lib_svbase {
		var $prefixId = 'tx_staticuploadrsync_sv1';
		// Same as class name
		var $scriptRelPath = 'sv1/class.tx_staticuploadrsync_sv1.php'; // Path to this script relative to the extension dir.
		var $extKey = 'staticuploadrsync'; // The extension key.

		/**
		* Performs the service processing
		*
		* @param	array		Configuration array
		* @return	string
		*/
		function process( $conf ) {
		if ( is_array( $conf ) ) {
				// Get date and time
				$date = date( 'YmdHis' );

				// Compile logfile dir and logfile name
				if ( empty( $conf['logfile_dir'] ) ) {
					$conf['logfile_dir'] = './';
				}else{
					if ( substr( $conf['logfile_dir'], -1 ) != '/' ) {
						$conf['logfile_dir'] .= '/';
					}
				}
				$logfile = sprintf( '%sscp_%s_%s.log', $conf['logfile_dir'], $this->extKey, $date );

				// Compile paths to executables
				if ( substr( $conf['path_to_php'], -1 ) != '/' ) {
						$conf['path_to_php'] .= '/';
				}
				if ( substr( $conf['path_to_scp'], -1 ) != '/' ) {
						$conf['path_to_scp'] .= '/';
				}

				// Add logfile to conf array
				$conf['logfile'] = $logfile;

				$timeStart = $this->microtime_float();

				if ( empty( $logfile ) ) {
					$cmd= sprintf( '%sphp "%scli/exec_bg_cli.php" "%s" 2>&1 &', $conf['path_to_php'], t3lib_extMgm::extPath( $this->extKey ), base64_encode( serialize($conf) ) );
				}else{
					$cmd= sprintf( '%sphp "%scli/exec_bg_cli.php" "%s" > "%s" 2>&1 &', $conf['path_to_php'], t3lib_extMgm::extPath( $this->extKey ), base64_encode( serialize($conf) ), $logfile );
				}
				exec( $cmd );

				$timeEnd = $this->microtime_float();

				$this->out = 'function2.service';
			}else{
				$this->out = 'function2.no_configuration';
			}

			return $this->out;
		}

		/**
		 * Simple function to replicate PHP 5 behaviour
		 *
		 * @return	float
		 */
		function microtime_float() {
			list( $usec, $sec ) = explode( ' ', microtime() );
			return ( (float)$usec + (float)$sec );
		}
	}



	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticuploadrsync/sv1/class.tx_staticuploadrsync_sv1.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticuploadrsync/sv1/class.tx_staticuploadrsync_sv1.php']);
	}

?>
