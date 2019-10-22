<?php
/*
HonestRepair Diablo Engine  -  Sharer
https://www.HonestRepair.net
https://github.com/zelon88

Licensed Under GNU GPLv3
https://www.gnu.org/licenses/gpl-3.0.html

Author: Justin Grimes
Date: 10/22/2019
<3 Open-Source

This is the configuration file for the Sharer Web Application. 
Based off the HonestRepair Diablo Engine.

This file was meant to be "included()" or "required()" by ShareCore.php.
This file does not output anything to the user.
If you lose this file you will be unable to decode session ID's and file keys later.

!!! MAKE BACKUP COPIES OF THIS FILE !!!
*/
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The version of this application. Must be a string.
// / Must be a string surrounded with single quotes.
$ShareVersion = 'v0.8.3'; 
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Specify the name of the application as displayed by its various UI elements.
// / Must be a string surrounded with single quotes.
$ApplicationName = 'Sharer';
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Set to "TRUE" to enable verbose logging.
// / Set to "FALSE" to disable verbose logging.
$Verbose = TRUE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Set the amount of time to keep pending shared files.
// / Set to a whole number in minutes. No decimal places or fractions allowed.
$DeleteThreshold = 30;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A setting to enable or diable "Maintenance Mode" for temporarily disabling the application.
// / Set to "TRUE" to prevent this application from running.
// / Set to "FALSE" to allow this application to run.
$MaintenanceMode = FALSE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Require that a user login with assigned credentials to upload or download files.
// / Set to "TRUE" to force a login screen before any operations can be completed.
// / Set to "FALSE" to allow anonymous usage.
$AuthenticationRequired = TRUE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Allow users without an account to create one on their own.
// / Set to "TRUE" to allow anonymous users to create new accounts.
// / Set to "FALSE" to prevent anonymous users from creating new accounts.
$AllowUsers = TRUE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Security Salts.
// / Set AT LEAST 4 array elements to use for authenticating operations that require additional security. 
// / Add additional array elenents will be used where possible, but the first 4 are required. 
$Salts = array('fgdsfg!876524fsfawedrw234381234120', 'fgsdfgafcrtytruyuio[][\;lkjhgj', 
 '><<>?#@$@%$fdasdasadq11123123DFASF #$FERG#$4445678$9899784f$f4', '568123456748978462418154da22sd41ew2w22c1111t15f5f89ryh347r9');
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Hashing algorithm. Must be a string.
// / Set a valid hash algorithm for internal encryption.
$EncryptionType = 'sha256';
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Enable virus scanning with ClamAV. If viruses are found the desired operation is rolled back.
// / Requires ClamAV to be installed on the server.
// / Set to "TRUE" to enable ClamAV virus scanning during uploads. 
// / Set to "FALSE" to disable ClamAV virus scanning during uploads.
$VirusScan = TRUE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Enable deep scanning. Takes longer, may require additional permissions setup. Reduces false-negatives.
// / Default is "TRUE".
// / Set to "TRUE" to enable thorough ClamAV scanning during uploads.
// / Set to "FALSE" to disable thorough ClamAV scanning during uploads.
$ThoroughAV = TRUE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Super Admin Users.
// / Users are treated as objects. Users added here have global admin powers that cannot be changed via the GUI.
// / Users added through the GUI after initial setup are contained in the cache.
// / Arrays are formatted as  $Users['USER_ID', 'USER_NAME', 'USER_EMAIL', 'HASHED_PASSWORD', "ADMIN_YES/NO(bool)", "LAST_SESION_ID"]
$Users = array(
 array('1', 'zelon88', 'test@gmail.com', 'testpassword', "TRUE"),
 array('2', 'Nikki', 'test@gmail.com', 'password', "FALSE"), 
 array('3', 'Leo', 'test@gmail.com', 'password', "FALSE"), 
 array('4', 'Ralph', 'test@gmail.com', 'password', "FALSE"), 
 array('5', 'Mikey', 'test@gmail.com', 'password', "FALSE"),
 array('6', 'Donny', 'test@gmail.com', 'these-are-all-fake-passwords', "FALSE") );
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Specify the location where temporary shared data will be stored until it has been requested.
// / Must be a string. No trailing slash!!!
// / Must be readable and writable to the www-data user. 
// /  Use.....
// /    chown -R www-data:www-data /path/to/ShareLoc
// /    chmod -R 0755 /path/to/ShareLoc
// / Must NOT be hosted!!!
$ShareLoc = '/ShareLoc';
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Specify the location where this application has been installed.
// / Must be a string. No trailing slash!!!
// / Must be readable and writable to the www-data user. 
// /  Use.....
// /    chown -R www-data:www-data /path/to/ShareLoc
// /    chmod -R 0755 /path/to/ShareLoc
// / Must be hosted!!!
// / Default is  '/var/www/html/HRProprietary/Sharer'
$InstLoc = '/var/www/html/HRProprietary/Sharer';
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Set the location where temporary shared data will be stored during download operations.
// / Must be a string. No trailing slash!!!
// / Must be readable and writable to the www-data user. 
// /  Use.....
// /    chown -R www-data:www-data /path/to/ShareLoc
// /    chmod -R 0755 /path/to/ShareLoc
// / Must be hosted!!!
// / Default is  $InstLoc.DIRECTORY_SEPARATOR.'Temp'
$TempLoc = $InstLoc.DIRECTORY_SEPARATOR.'Temp';
// / ----------------------------------------------------------------------------------