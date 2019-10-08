<?php
/*
HonestRepair Diablo Engine  -  Sharer
https://www.HonestRepair.net
https://github.com/zelon88

Licensed Under GNU GPLv3
https://www.gnu.org/licenses/gpl-3.0.html

Author: Justin Grimes
Date: 10/8/2019
<3 Open-Source

This is the configuration file for the Sharer Web Application. 
Based off the HonestRepair Diablo Engine.
*/
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The version of this application.
$shareVersion = 'v0.5';
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Specify the name of the application as displayed by its various UI elements.
$applicationName = "Sharer";
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Enable verbose logging.
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
$MaintenanceMode = FALSE;
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Allow users without an account to create one on their own.
// / Set to enabled to allow anonymous users to create new accounts.
// / Set to false to prevent anonymous users from creating new accounts.
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
// / Super Admin Users.
// / Users are treated as objects. Users added here have global admin powers that cannot be changed via the GUI.
// / Users added through the GUI after initial setup are contained in the cache.
// / Arrays are formatted as  $Users['USER_ID', 'USER_NAME', 'USER_EMAIL', 'SHA-256_HASHED_PASSWORD', "ADMIN_YES/NO(bool)", "LAST_SESION_ID"]
$Users = array(
 array('1', 'zelon88', 'test@gmail.com', 'testpassword', "TRUE"),
 array('2', 'Nikki', 'test@gmail.com', 'password', "FALSE") 
 array('3', 'Leo', 'test@gmail.com', 'password', "FALSE") 
 array('4', 'Ralph', 'test@gmail.com', 'password', "FALSE") 
 array('5', 'Mikey', 'test@gmail.com', 'password', "FALSE") 
 array('6', 'Donny', 'test@gmail.com', 'these-are-all-fake-passwords', "FALSE") );
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Directory declarations.
// / No trailing slash!!!
// /Specify the location where temporary shared data will be stored until it has been requested.
$StoreLoc = '/ShareLoc';
// / Specify the location where this application has been installed.
// / Must be hosted!!!
$InstLoc = '/var/www/html/HRProprietary/Sharer';
// / Set the location where temporary shared data will be stored during download operations.
$TempLoc = $InstLoc.DIRECTORY_SEPARATOR.'Temp';
// / ----------------------------------------------------------------------------------