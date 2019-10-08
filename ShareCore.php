<!DOCTYPE HTML>
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

This is the primary Core file for the Sharer Web Application. 
Based off the HonestRepair Diablo Engine.
*/
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Specify our own time limit for script execution independant of php.ini.
set_time_limit(0);
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Detemine the version of PHP in use to run the application.
// / Any PHP version earlier than 7.0 IS STRICTLY NOT SUPPORTED!!!
// / Specifically, PHP versions earlier than 7.0 require the list() functions used to be unserialized. 
// / If you run this application on a PHP version earlier than 7.0 you may experience extremely bizarre or even dangerous behavior.
// / PLEASE DO NOT RUN THIS APPLICATION ON ANYTHING EARLIER THAN PHP 7.0!!! 
// / MAINTAINERSMAINTAINERS ASSUMES NO LIABILITY FOR USING THIS SOFTWARE!!!
function phpCheck() { 
  if (version_compare(PHP_VERSION, '7.0.0') <= 0) die('<a class="errorMessage">ERROR!!! 0, This application is NOT compatible with PHP versions earlier than 7.0. Running this application on unsupported PHP versions WILL cause unexpected behavior!</a>'.PHP_EOL);
  return (TRUE); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Determine the operating system in use to run the application.
// / Any version of Windows IS STRICTLY NOT SUPPORTED!!!
// / Specifically, only Debian-based Linux distros.
// / PLEASE DO NOT RUN THIS APPLICATION ON A WINDOWS OPERATING SYSTEM!!! 
// / MAINTAINERS ASSUMES NO LIABILITY FOR USING THIS SOFTWARE!!!
function osCheck() { 
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') die('<a class="errorMessage">ERROR!!! 1, This application is NOT compatible with the Windows Operating System. Running this application on unsupported operating systems WILL cause unexpected behavior!</a>'.PHP_EOL); 
  return (TRUE);  }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Make sure there is a session started and load the configuration file.
// / Also kill the application if $MaintenanceMode is set to  TRUE.
function loadConfig() { 
  if (session_status() == PHP_SESSION_NONE) session_start();
  if (!file_exists('config.php')) $ConfigIsLoaded = FALSE; 
  else require_once ('config.php'); 
  $ConfigIsLoaded = TRUE; 
  if ($MaintenanceMode === TRUE) die('The requested application is currently unavailable due to maintenance.'.PHP_EOL); 
  return ($ConfigIsLoaded); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The following code sets the functions for the session.

// / A function for sanitizing input strings with varying degrees of tolerance.
// / Filters a given string of | \ ~ # [ ] ( ) { } ; : $ ! # ^ & % @ > * < " / '
// / This function will replace any of the above specified charcters with NOTHING. No character at all. An empty string.
// / Set $strict to TRUE to also filter out backslash characters as well. Example:  /
function sanitize($Variable, $Strict) { 
  // / Set variables.  
  $VariableIsSanitized = TRUE;
  if (!is_bool($Strict)) $Strict = TRUE; 
  if (!is_string($Variable)) $VariableIsSanitized = FALSE;
  else { 
    // / Note that when $strict is TRUE we also filter out backslashes. Not good if you're filtering a URL or path.
    if ($Strict === TRUE) $Variable = str_replace(str_split('|\\~#[](){};$!#^&%@>*<"\'/'), '', $Variable);
    if ($Strict === FALSE) $Variable = str_replace(str_split('|\\~#[](){};$!#^&%@>*<"\''), '', $Variable); }
  return (array($Variable, $VariableIsSanitized)); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to set the date and time for internal logic like file cleanup.
// / Set variables. 
function verifyDate() { 
  // / Set variables. Create an accurate human-readable date from the servers timezone.
  $Date = date("m-d-y");
  $Time = date("F j, Y, g:i a"); 
  $Minute = int(date('i'));
  $LastMinute = $Minute - 1;
  // / We need to accomodate the off-chance that execution spans multiple days. 
  // / In other words, the application starts at 11:59am and ends at 12:00am.
  // / I tried to think what would happen if we spanned multiple months or years but I threw up in my mouth. >D
  if ($LastMinute === 0) $LastMinute = 59;
  return(array($Date, $Time, $Minute, $LastMinute)); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to generate and validate the operational environment for the Diablo Engine.
function verifyInstallation() { 
  // / Set variables. 
  global $Date, $Time, $Salts;
  $dirCheck = $indexCheck = $dirExists = $indexExists = $logCheck = $cacheCheck = TRUE;
  $requiredDirs = array('Logs', 'Cache', 'Temp');
  $InstallationIsVerified = FALSE;
  // / For servers with unprotected directory roots, we must verify (at minimum) that a local index file exists to catch unwanted traversal.
  if (!file_exists('index.html')) $indexCheck = FALSE;
  // / Iterate through the $requiredDirs hard-coded (in this function, under "Set variables" section above).
  foreach ($requiredDirs as $requiredDir) {
    // / If a $requiredDir doesn't exist, we create it.
    if (!is_dir($requiredDir)) $dirExists = mkdir($requiredDir, 0755);
    // / A sanity check to ensure the directory was actually created.
    if (!$dirExists) $dirCheck = FALSE;
    // / Copy an index file into the newly created directory to enable directory root protection the old fashioned way.
    if (!file_exists($requiredDir.DIRECTORY_SEPARATOR.'index.html')) $indexExists = copy('index.html', $requiredDir.DIRECTORY_SEPARATOR.'index.html');
    // / A sanity check to ensure that an index file was created in the newly created directory.
    if (!$indexExists) $indexCheck = FALSE; }
  // / Create a unique identifier for today's $LogFile.
  $logHash = substr(hash('sha256', $Salts[0].hash('sha256', $Date.$Salts[1].$Salts[2].$Salts[3])), 0, 7);
  // / Define today's $LogFile.
  $LogFile = 'Logs'.DIRECTORY_SEPARATOR.$Date.'_'.$logHash.'.log';
  // / Create today's $LogFile if it doesn't exist yet.
  if (!file_exists($LogFile)) $logCheck = file_put_contents($LogFile, 'OP-Act: '.$Time.' Created a log file, "'.$LogFile.'".');
  // / Create a unique identifier for the cache file.
  $CacheFile = 'Cache'.DIRECTORY_SEPARATOR.'Cache-'.hash('sha256',$Salts[0].'CACHE').'.php';
  // / If no cache file exists yet (first run) we create one and write the $PostConfigUsers to it. 
  if (!file_exists($CacheFile)) $cacheCheck = file_put_contents($CacheFile, '<?php'.PHP_EOL.'$PostConfigUsers = array();');
  // / Make sure all sanity checks passed.
  if ($dirCheck && $indexCheck && $logCheck && $cacheCheck) $InstallationIsVerified = TRUE;
  // / Clean up unneeded memory.
  $dirCheck = $indexCheck = $logCheck = $cacheCheck = $requiredDirs = $requiredDir = $dirExists = $indexExists = $logHash = NULL;
  unset($dirCheck, $indexCheck, $logCheck, $cacheCheck, $requiredDirs, $requiredDir, $dirExists, $indexExists, $logHash);
  return(array($LogFile, $CacheFile, $InstallationIsVerified)); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to generate useful, consistent, and easily repeatable error messages.
function dieGracefully($ErrorNumber, $ErrorMessage) { 
  // / Set variables. 
  global $LogFile, $Time;
  // / Perform a sanity check on the $ErrorNumber. Hackers are creative and this is a sensitive operation
  // / that could be the target of XSS attacks.
  if (!is_numeric($ErrorNumber)) $ErrorNumber = 0;
  $ErrorOutput = 'ERROR!!! '.$ErrorNumber.', '.$Time.', '.$ErrorMessage.PHP_EOL;
  // / Write the log file. Note that we don't care about success or failure because we're about to kill the script regardless.
  file_put_contents($LogFile, $ErrorOutput, FILE_APPEND);
  die('<a class="errorMessage">'.$ErrorOutput.'</a>'); } 
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to generate useful, consistent, and easily repeatable log messages.
function logEntry($EntryText) { 
  // / Set variables. 
  global $LogFile, $Time;
  // / Format the actual log message.
  $EntryOutput = sanitize('OP-Act: '.$Time.', '.$EntryText.PHP_EOL, TRUE);
  // / Write the actual log file.
  $LogWritten = file_put_contents($LogFile, $EntryOutput, FILE_APPEND);
  return($LogWritten); } 
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to load the system cache, which contains the master user list.
// / Cache files are stored as .php files and cache data is stored as an array. This ensures the files
// / cannot simply be viewed with a browser to reveal sensitive content. The data must be programatically
// / displayed or opened locally in a text editor.
function loadCache() { 
  // / Set variables. 
  global $Users, $CacheFile;
  require ($CacheFile);
  if (!isset($PostConfigUsers)) $PostConfigUsers = array();
  $Users = array_merge($PostConfigUsers, $Users);
  // / Clean up unneeded memory.
  $CacheIsLoaded = TRUE;
  return(array($Users, $CacheIsLoaded)); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to validate and sanitize requried session and POST variables.
function verifyGlobals() { 
  // / Set variables. 
  global $Salts, $Data;
  $GlobalsAreVerified = FALSE;
  $saniString = '|\\/~#[](){};:$!#^&%@>*<"\'';
  // / Set authentication credentials from supplied inputs when inputs are supplied.
  if (isset($_POST['UserInput']) && isset($_POST['PasswordInput']) && isset($_POST['ClientTokenInput'])) { 
    $_SESSION['UserInput'] = $UserInput = str_replace(str_split($saniString), ' ', $_POST['UserInput']), ENT_QUOTES, 'UTF-8');
    $_SESSION['PasswordInput'] = $PasswordInput = str_replace(str_split($saniString), ' ', $_POST['PasswordInput']), ENT_QUOTES, 'UTF-8'); 
    $_SESSION['ClientTokenInput'] = $ClientTokenInput = hash('sha256', $_POST['ClientTokenInput']), ENT_QUOTES, 'UTF-8');
    $_SESSION['Mode'] = str_replace(str_split($saniString), ' ', $_POST['Mode']), ENT_QUOTES, 'UTF-8'); }
  // / Detect if required variables are set.
  $GlobalsAreVerified = TRUE;
  // / Clean up unneeded memory.
  $saniString = NULL;
  unset($saniString);
  return($UserInput, $PasswordInput, $ClientTokenInput, $Mode, $GlobalsAreVerified); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to throw the login page when needed.
function requireLogin() { 
 if (file_exists('login.php'))
 require ('login.php');
 return(TRUE); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to generate new user tokens and validate supplied ones.
// / This is the secret sauce behind full password encryption in-transit.
// / Please excuse the lack of comments. Security through obscurity is a bad practice.
// / But no lock is pick proof, especially ones that come with instructions for picking them.
function generateTokens($ClientTokenInput, $PasswordInput) { 
  // / Set variables. 
  global $Minute, $LastMinute;
  $ServerToken = $ClientToken = NULL;
  $TokensAreValid = FALSE;
  $ServerToken = hash('sha256', $Minute.$Salts[1].$Salts[3]);
  $CLientToken = hash('sha256', $Minute.$PasswordInput); 
  $oldServerToken = hash('sha256', $LastMinute.$Salts[1].$Salts[3]);
  $oldCLientToken = hash('sha256', $LastMinute.$PasswordInput);
  if ($ClientTokenInput === $oldClientToken) {
    $ClientToken = $oldClientToken;
    $ServerToken = $oldServerToken; }
  if ($ServerToken !== NULL && $ClientToken !== NULL) $TokensAreValid = TRUE;
  // / Clean up unneeded memory.
  $oldClientToken = $oldServerToken = NULL;
  unset($oldClientToken, $oldServerToken); 
  return(array($ClientToken, $ServerToken, $TokensAreValid)); } 
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to authenticate a user and verify an encrypted input password with supplied tokens.
function authenticate($UserInput, $PasswordInput, $ServerToken, $ClientToken) { 
  // / Set variables. Note that we try not to include anything here we don't have to because
  // / It's going to be hammered by someone, somewhere, eventually. Less is more in terms of code & security.
  global $Users;
  $UserID = $UserName = $PasswordIsCorrect = $UserIsAdmin = $AuthIsComplete = FALSE;
  // / Iterate through each defined user.
  foreach ($Users as $User) { 
    $UserID = $User[0];
    // / Continue ONLY if the $UserInput matches the a valid $UserName.
    if ($User[1] === $UserInput) { 
      $UserName = $User[1];
      // / Continue ONLY if all tokens match and the password hash is correct.
      if (hash('sha256', $ServerToken.hash('sha256', $ClientToken.$User[3])) === hash('sh256', $ServerToken.hash('sha256', $Salts[0].$PasswordInput.$Salts[0].$Salts[1].$Salts[2].$Salts[3]))) { 
        $PasswordIsCorrect = TRUE; 
        // / Here we grant the user their designated permissions and only then decide $AuthIsComplete.
        if (is_bool($User[4])) {
          $UserIsAdmin = $User[4]; 
          $AuthIsComplete = TRUE; 
          // / Once we authenticate a user we no longer need to continue iterating through the userlist, so we stop.
          break; } } } }
  // / Clean up unneeded memory.
  $UserInput = $PasswordInput = $User = $Users = NULL;
  unset($UserInput, $PasswordInput, $User, $Users); 
  return(array($UserID, $UserName, $UserEmail, $PasswordIsCorrect, $UserIsAdmin, $AuthIsComplete)); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The following code will clean up old files.
function cleanFiles($path) { 
  global $ScanLoc, $ScanTemp, $InstLoc;
  if (is_dir($path)) { 
    $i = scandir($path);
    foreach($i as $f) { 
      if (is_file($path.DIRECTORY_SEPARATOR.$f)) @unlink($path.DIRECTORY_SEPARATOR.$f);  
      if (is_dir($path.DIRECTORY_SEPARATOR.$f)) cleanFiles($path.DIRECTORY_SEPARATOR.$f); @rmdir($path.DIRECTORY_SEPARATOR.$f); } } }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / A function to clean the upper-level folders that this application manages.
function cleanFolders($dTarget, $DeleteThreshold) { 
  $DirectoriesAreClean = FALSE;
  if (file_exists($dTarget)) { 
    $DFiles = array_diff(scandir($dTarget), array('..', '.'));
    $now = time();
    foreach ($DFiles as $DFile) { 
      $DFilePath = $dTarget.DIRECTORY_SEPARATOR.$DFile;
      if ($DFilePath == $dTarget.DIRECTORY_SEPARATOR.'index.html') continue; 
      if ($now - fileTime($DFilePath) > ($DeleteThreshold * 60)) { // Time to keep files.
        if (is_dir($DFilePath)) { 
          @chmod ($DFilePath, 0755);
          cleanFiles($DFilePath);
          if (is_dir_empty($DFilePath)) @rmdir($DFilePath); } } } 
    if (!file_exists($dTarget)) $DirectoriesAreClean = TRUE; }
  return ($DirectoriesAreClean); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The main logic of the program which makes use of the functions above.
if (phpCheck() && osCheck() && loadConfig()) { 
  list($Date, $Time, $Minute, $LastMinute) = verifyDate();

  // / This code verifies the integrity of the application.
  // / Also generates required directories in case they are missing & creates required log & cache files.
  list ($LogFile, $CacheFile, $InstallationIsVerified) = verifyInstallation();
  if (!$InstallationIsVerified) dieGracefully(3, 'Could not verify installation!');
  else if ($Verbose) logEntry('Verified installation.');

  // / This code loads & sanitizes the global cache & prepares the user list.
  list ($Users, $CacheIsLoaded) = loadCache();
  if (!$CacheIsLoaded) dieGracefully(4, 'Could not load cache file!');
  else if ($Verbose) logEntry('Loaded cache file.');

  // / This code takes in all required inputs to build a session and ensures they exist & are a valid type.
  // / Also displays the login page when the user is not logged in.
  list ($UserInput, $PasswordInput, $ClientTokenInput, $Mode, $GlobalsAreVerified) = verifyGlobals();
  if (!$GlobalsAreVerified) requireLogin(); dieGracefully(5, 'User is not logged in!');
  else if ($Verbose) logEntry('Verified global variables.');

  // / This code ensures that a same-origin UI element generated the login request.
  // / Also protects against packet replay attacks by ensuring that the request was generated recently and by making each request unique. 
  list ($ClientToken, $ServerToken, $TokensAreVerified) = generateTokens($ClientTokenInput, $PasswordInput);
  if (!$TokensAreValid) dieGracefully(6, 'Invalid tokens!');
  else if ($Verbose) logEntry('Generated tokens.');

  // / This code validates credentials supplied by the user against the hashed ones stored on the server.
  // / Also removes the $Users user list from memory so it can not be leaked.
  // / Displays a login screen when authentication fails and kills the application. 
  list ($UserID, $UserName, $UserEmail, $PasswordIsCorrect, $UserIsAdmin, $AuthIsComplete) = authenticate($UserInput, $PasswordInput, $ClientToken, $ServerToken);
  if (!$PasswordIsCorrect or !$AuthIsComplete) dieGracefully(7, 'Invalid username or password!'); 
  else if ($Verbose) logEntry('Authenticated '.$UserName.', '.$UserID.'.');

  // / Dynamically build the UI depending on which functionality is desired.
  require('header.php');
  if (isset($_POST['Mode'] == 'UPLOAD') require('upload.php');
  if (isset($_POST['Mode'] == 'DOWNLOAD') require('download.php');
  require('footer.php');

}
// / ----------------------------------------------------------------------------------