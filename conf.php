<?php
/**
 * [ NOTIFICATION ]
 */

/** @var $notify['level']
 *  The Notify Level Options
 *  0 = No Notification
 *  1 = Notify by Email a summary when a vulnerability has been found (default)
 *  2 = Notify by Email a full report when a vulnerability has been found
*/
$notify['level'] = 1;


/** @var $notify['email']
 *  Your email address
 */
$notify['email'] = 'root@localhost';


/** @var $notify['from_email']
 *  Server email address
 */
$notify['from_email'] = 'noreply@localhost';


/** @var $notify['subject']
 *  Notify Subject Line
*/
$notify['subject'] = 'PUP Found on ' . $_SERVER['SERVER_NAME'];



/**
 * [ ACTION ]
*/

/** @var $action['level']
 *  The Level of action to take when a PUP is found
 *  0 = No Action - Allow file to continue being uploaded and stored and append $_FILES with scan results (not recommended)
 *  1 = Quarantine file and append $_FILES list with scan results (default)
 *  2 = Quarantine file and remove from $_FILES list
 *  3 = Remove file and append $_FILES list with scan results
 *  4 = Remove file and remove from $_FILES list
*/
$action['level'] = 1;


/** @var $action['iptables']
 *  Block client IP address on PUP detection
 *  Boolean (true/false)
 *  Default: false
 */
$action['iptables'] = false; /* working in 1.2 release */


/** @var $action['iptables_string']
 *  IPTABLES input string, %s is a placeholder for clients IP address, do not remove
 *  WARNING: ALTER AT OWN RISK
 */
$action['iptables_string'] = 'iptables -I INPUT -s %s -j DROP'; /* working in 1.2 release */


/** @var $action['ip_whitelist']
 *  IPTABLES will not add any of the follow IP addresses - this is a failsafe against IP spoofing.
 *  WARNING: ALTER AT OWN RISK
 */
$action['ip_whitelist'] = ['127.0.0.1'];


/** @var $action['log_enabled']
 *  Log IP addresses that upload PUP
 *  Boolean (true/false)
 *  Default: true
 */
$action['log_enabled'] = true;


/** @var $action['log_location']
 *  Location of log
 *  string
 *  Default: /var/log/phpsc.log
 */
$action['log_location'] = PHPSC_ROOT . '/phpsc.log';


/** @var $action['threshold']
 *  Minimum string matches before considered a PUP
 *  integer
 *  Default: 3
*/
$action['threshold'] = 3;