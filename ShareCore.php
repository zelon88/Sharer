<!DOCTYPE HTML>
<html>
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
The code contained in this file was based off of the HonestRepair Diablo Engine.

This file is meant to be the main entry point of execution for this application.
The beginning of this file consists of all the functions required to support execution.
The end of this file contains the entry point which handles user requests & UI generation.

This file is responsible for making & receiving every API call this program is capable of.
Direct any and all API requests at this file.

This file will construct a dynamic UI for the session depending on which features are selected.
This file contains the <!DOCTYPE HTML> & opening <html> tags so critical errors still produce valid HTML.

Variable scope is very important within this file.
To make scope easier & more secure:
 1. Variables are manually destroyed once they are no longer required.
 2. Upper-case variables denote a globally intentioned scope.
 3. Lower-case variables denote a locally intentioned scope.
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
  // / Check that config.php exists and load it if it does.
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
  // / Check for proper input types before trusting user influenced variables. 
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
  $dirCheck = $indexCheck = $dirExists = $indexExists = $logCheck = $cacheCheck = $shareLocCheck = TRUE;
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
  // / Check that the $ShareLoc exists.
  if (!file_exists($ShareLoc)) $shareLocCheck = FALSE;
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
  if ($dirCheck && $indexCheck && $logCheck && $cacheCheck && $shareLocCheck) $InstallationIsVerified = TRUE;
  // / Clean up unneeded memory.
  $dirCheck = $indexCheck = $logCheck = $cacheCheck = $requiredDirs = $requiredDir = $dirExists = $indexExists = $logHash = $shareLocCheck = NULL;
  unset($dirCheck, $indexCheck, $logCheck, $cacheCheck, $requiredDirs, $requiredDir, $dirExists, $indexExists, $logHash, $shareLocCheck);
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
  // / Check that login.php exists and load it if it does.
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

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user initiates a file upload.
// / $files should be set to $_FILES['fileUpload'].
// / $fileKeys should be an array or CSV of keys that correspond to the $files array.
// / $userIDs should be an array of arrays or CSVs that correspond to the users who should access each $file. 
function upload($files, $fileKeys, $userIDs) {
  if (!is_array($files['name'])) $files = array($files['name']);
  if (!is_array($fileKeys)) $fileKeys = array($fileKeys);
  if (!is_array($userIDs)) $userIDs = array($userIDs);
  foreach ($files as $key=>$file) {
    // / Sanitize the input file for security.
    $file = Sanitize($file, FALSE);
    $fileKey = Sanitize($fileKeys[$key], TRUE);
    // / If $AuthenticationRequired is set to TRUE in config.php the $AllowedUsers array from the KEYS file is prepared.
    if ($AuthenticationRequired) { 
      // / $userIDs[$key] needs to be an array (but it's probably a sting).
      // / $userIDs[$key] is an array of UserIDs that can access the currently selected file.
      // / $userIDs[$key] is controlled by the outer loop (above).
      if (!is_array($userIDs[$key])) $userIDs[$key] = array($userIDs[$key]);
      // / $userID is a handle for $userIDs[$key][$uKey]. It is a single user of the selected file.
      foreach ($userIDs[$key] as $uKey=>$userID) $userIDs[$key][$uKey] = Sanitize($userID, TRUE); }
    // / If $AuthenticationRequired is set to FALSE in config.php we ignore the $userIDs argument and substitute UserID 0 (Anonymous).
    else $userIDs = 0;
    if ($file == '.' or $file == '..' or $file == 'index.html') continue;
    foreach ($DangerousFiles as $DangerousFile) { 
      if (strpos($file, $DangerousFile) !== FALSE) continue 2; }  
    $file = htmlentities(str_replace('..', '', str_replace(str_split('\\/[]{};:$!#^&%@>*<'), '', $file)), ENT_QUOTES, 'UTF-8'); 
    $F0 = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($F0, $DangerousFiles)) { 
      $txt = ("ERROR!!! HRC2103, Unsupported file format, $F0 on $Time.");
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      echo($txt.$br.$hr); 
      continue; }
    $F2 = str_replace('..', '', str_replace('//', '/', str_replace('///', '/', pathinfo($file, PATHINFO_BASENAME))));
    $F3 = str_replace('..', '', str_replace(str_split('()|&'), '', str_replace('//', '/', str_replace('///', '/', $CloudUsrDir.$F2))));
    if($file == "") {
      $txt = ("ERROR!!! HRC2160, No file specified on $Time.");
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND); 
      echo($txt.$br.$hr); 
      continue; }
    $COPY_TEMP = @copy($_FILES['filesToUpload']['tmp_name'][$key], $F3);
    if (file_exists($F3)) { 
      $txt = ('OP-Act: Uploaded '.$file.' to '.str_replace('//', '/', $Udir.'/'.$file).' on '.$Time.'.');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      echo($txt.$br.$hr); }
    if (!file_exists($F3)) { 
      $txt = ("ERROR!!! HRC289, Could not upload $F3 on $Time.");
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      echo($txt.$br.$hr); }
    @chmod($F3, $ILPerms); 
    // / The following code checks the Cloud Location with ClamAV after copying, just in case.
    if ($VirusScan == '1') {
      shell_exec(str_replace('  ', ' ', str_replace('  ', ' ', 'clamscan -r '.$Thorough.' '.$F3.' | grep FOUND >> '.$ClamLogDir)));
      $ClamLogFileDATA = @file_get_contents($ClamLogDir);
      if (strpos($ClamLogFileDATA, 'Virus Detected') !== FALSE or strpos($ClamLogFileDATA, 'FOUND') !== FALSE) {
        $txt = ('Warning!!! HRC2338, There were potentially infected files detected. The file
          transfer could not be completed at this time. Please check your file for viruses or
          try again later.');
        $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);          
        @unlink($F3);
        die($txt.$br.$hr); } } } 
  // / Free un-needed memory.
  $txt = $file = $F0 = $F2 = $F3 = $ClamLogFileDATA = $Upload = $MAKELogFile = NULL;
  unset($txt, $file, $F0, $F2, $F3, $ClamLogFileDATA, $Upload, $MAKELogFile); } 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user downloads a selection of files.
// / $files should be an array or CSV of filenames to be downloaded.
// / $fileKeys should be an array or CSV of keys that correspond to the selected $files.
// / $userID should be the valid $userID of the current user. 
// / If $AuthenticationRequired is set to FALSE in config.php the $userID argument is ignored.
function download($files, $fileKeys, $userID) {
  // / Set variables. Note that we initialize $DownloadSuccess to FALSE and the other checks to TRUE.
  // / If any of the other checks fail during a loop they will trip the $fileCheck or $copyCheck to FALSE.
  global $ShareLoc, $TempLoc, $AuthenticationRequired;
  $DownloadSuccess = FALSE;
  $DownloadURLs = array();
  $fileCheck = $copyCheck = $fileDataCheck = $permsCheck = TRUE;
  $ds = DIRECTORY_SEPARATOR;
  $dsds = DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR;
  // / If $AuthenticationRequired is set to TRUE in config.php we convert $userID to an integer to ensure its validity.
  if ($AuthenticationRequired) $userID = int($userID);
  // / This one-liner converts a string to an array so it runs in a loop. Strings only loop once.
  if (!is_array($files)) $files = array($files); 
  // / Iterate through the supplied array of files.
  foreach ($files as $key=>$file) {
    $AllowedUsers = array();
    $url = '';
    // / Sanitize the input file for security.
    $file = Sanitize($file, FALSE);
    $fileKey = Sanitize($fileKeys[$key], TRUE);
    // / Perform a sanity check on the input file.
    if ($file == '.' or $file == '..' or strpos($file, '.php') !== FALSE or strpos($file, '.js') !== FALSE or strpos($file, '.html') !== FALSE) continue;
    // / Remove duplicate directory separators.
    $filePath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file);
    $fileDataPath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file.'-KEYS.php');
    $fileTempPath - str_replace($dsds, $ds, $TempLoc.$ds.$fileKey.$ds.$file); 
    // / Check for the existence of a key file for the selected file.
    if (file_exists($fileDataPath)) { 
      // / If a key file exists for the selected file, we load it into memory to retrieve permission settings and keys.
      require($fileDataPath);
      // / If the $AuthenticationRequired is set to TRUE in config.php we check that the current user has permission to view the selected file.
      if ($AuthenticationRequired) if (!in_array($userID, $AllowedUsers)) { 
        $permsCheck = FALSE;
        // / If the user lacks permissions to view any of their selected files the loop will terminate and cleanup operations will begin.
        break; } 
      // / Craft a relative URL for the selected file and add it to an array that we can return later.
      $url = str_replace($dsds, $ds, 'Temp'.$ds.$fileKey.$ds.$file);
      $DownloadURLs = array_push($DownloadURLs, $url);
      // / Check to be sure the file exists before attempting to copy it and set a flag on error that we can check for once the loop is complete.
      if (!file_exists($filePath)) $fileCheck = FALSE;
      // / If no errors were found so far we copy the selected file to the "Temp" directory.
      else $copyCheck = @copy($filePath, $tempFilePath); } 
    // / Throw an error flag if the $fileDataPath does not exist. 
    else $fileDataCheck = FALSE; }
  // / Now that the loop has ended we can check for the existence of any error flags that may have been thrown.
  if (!$copyCheck or !$fileCheck or !$fileDataCheck or !$permsCheck) { 
    // / Iterate back through the array of input files and re-create the tempFile location for cleanup.
    foreach ($files as $key=>$file) {
      // / Sanitize the input file for security, again.
      $file = Sanitize($file, FALSE);
      $fileKey = Sanitize($fileKeys[$key], TRUE);
      // / Perform a sanity check on the input file, again.
      if ($file == '.' or $file == '..' or strpos($file, '.php') !== FALSE or strpos($file, '.js') !== FALSE or strpos($file, '.html') !== FALSE) continue;
      // / Remove duplicate directory separators, again.
      $filePath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file);
      // / Remove the selected temp file.
      if (file_exists($fileTempPath)) @unlink($fileTempPath); }
    // / Blank out any URL's that may have been crafted earlier.
    $DownloadURLs = array(); }
  // / If there were no errors during execution we can set the $DownloadSuccess variable to TRUE.
  else $DownloadSuccess = TRUE; 
  // / Free un-needed memory.
  $fileKeys = $fileKey = $fileCheck = $copyCheck = $fileDataCheck = $permsCheck = $dsds = $ds = $files = $file = $key = $url = $fileTempPath = $fileDataPath = $filePath = NULL;
  unset($fileKeys, $fileKey, $fileCheck, $copyCheck, $fileDataCheck, $permsCheck, $dsds, $ds, $files, $file, $key, $url, $fileTempPath, $fileDataPath, $filePath); 
  return (array($DownloadURLs, $DownloadSuccess)); }
// / -----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / The main logic of the program which makes use of the functions above.

// / Perform some basic environment checks before we start writing to the filesystem.
// / Specifically we check that the PHP version is 7.0 or greater, the O/S is not Windows, & that the config file is readable.
if (phpCheck() && osCheck() && loadConfig()) {
  // / Set the time. $Minute and $LastMinute area used for token generation. 
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
 
  // / When the $AuthenticationRequired variable is set to TRUE in config.php this code block is run to authenticate the user.
  if ($AuthenticationRequired) { 
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
    else if ($Verbose) logEntry('Authenticated '.$UserName.', '.$UserID.'.'); }
  
  // / When the $AuthenticationRequired variable is set to FALSE in config.php this code block is run, bypassing authentication.
  if (!$AuthenticationRequired) { 
    $ClientToken = hash('sha256', rand(10000000000).rand(10000000000));
    $ServerToken = hash('sha256', rand(10000000000).rand(10000000000));
    $UserID = 0;
    $UserName = 'Anonymous';
    $UserEmail = 'Anonymous@anon.net';
    $UserIsAdmin = FALSE; }



  // / Dynamically build the UI depending on which functionality is desired.
  require('header.php');
  if (isset(Sanitize($_POST['Mode'], TRUE) == 'UPLOAD') require('upload.php'); 
  if (isset(Sanitize($_POST['Mode'], TRUE) == 'DOWNLOAD') require('download.php');
  require('footer.php');

}
// / ----------------------------------------------------------------------------------