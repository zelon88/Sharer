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
// / Determine if we need to prepare a DOCTYPE and open HTML tags for the session.
function opCheck() { 
  $DownloadRequired = $UploadRequired = FALSE;
  $Doctype = '<!DOCTYPE HTML><HTML>';
  if (isset($_POST['DownloadFiles'])) $Doctype = ''; $DownloadRequired = TRUE;
  if (isset($_POST['UploadFiles'])) $Doctype = ''; $UploadRequired = TRUE;
  return (array($Doctype, $DownloadRequired, $UploadRequired)); }
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
  global $ShareVersion, $ApplicationName, $Verbose, $DeleteThreshold, $MaintenanceMode, $AuthenticationRequired, $AllowUsers, $Salts, $EncryptionType, $VirusScan, $ThoroughAV, $Users, $ShareLoc, $InstLoc, $TempLoc;
  if (session_status() == PHP_SESSION_NONE) session_start();
  // / Check that config.php exists and load it if it does.
  if (!file_exists('config.php')) $ConfigIsLoaded = FALSE; 
  else { 
    require_once ('config.php'); 
    $ConfigIsLoaded = TRUE; } 
  if ($MaintenanceMode === TRUE) die('The requested application is currently unavailable due to maintenance.'.PHP_EOL); 
  return ($ConfigIsLoaded); }
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
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
  $Minute = (int)date('i');
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
  global $Date, $Time, $Salts, $EncryptionType, $ShareLoc, $ApplicationName;
  $dirCheck = $indexCheck = $dirExists = $indexExists = $logCheck = $cacheCheck = $shareLocCheck = $logDirCheck = TRUE;
  $requiredDirs = array('Logs', 'Cache', 'Temp');
  $InstallationIsVerified = FALSE;
  // / For servers with unprotected directory roots, we must verify (at minimum) that a local index file exists to catch unwanted traversal.
  if (!file_exists('index.html')) $indexCheck = FALSE;
  // / Iterate through the $requiredDirs hard-coded (in this function, under "Set variables" section above).
  foreach ($requiredDirs as $requiredDir) {
    $requiredDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$requiredDir;
    // / If a $requiredDir doesn't exist, we create it.
    if (!is_dir($requiredDir)) $dirExists = @mkdir($requiredDir, 0755);
    // / A sanity check to ensure the directory was actually created.
    if (!$dirExists) $dirCheck = FALSE;
    // / Copy an index file into the newly created directory to enable directory root protection the old fashioned way.
    if (!file_exists($requiredDir.DIRECTORY_SEPARATOR.'index.html')) $indexExists = @copy('index.html', $requiredDir.DIRECTORY_SEPARATOR.'index.html');
    // / A sanity check to ensure that an index file was created in the newly created directory.
    if (!$indexExists) $indexCheck = FALSE; }
  // / Check that the $ShareLoc exists.
  if (!file_exists($ShareLoc)) $shareLocCheck = FALSE;
  // / Create a unique identifier for today's $LogFile.
  $logHash = substr(hash($EncryptionType, $Salts[0].hash($EncryptionType, $Date.$Salts[1].$Salts[2].$Salts[3])), 0, 7);
  // / Define today's $LogFile.
  $logDir = 'Logs'.DIRECTORY_SEPARATOR.$Date.DIRECTORY_SEPARATOR;
  if (!is_dir($logDir)) $logDirCheck = @mkdir($logDir);
  $LogFile = $logDir.$ApplicationName.'_'.$logHash.'.log';
  $VirusLogFile = $logDir.'_'.$ApplicationName.'_ClamAV'.$logHash.'.log';
  // / Create today's $LogFile if it doesn't exist yet.
  if (!file_exists($LogFile)) $logCheck = file_put_contents($LogFile, 'OP-Act: '.$Time.' Created a log file, "'.$LogFile.'".');
  // / Create a unique identifier for the cache file.
  $CacheFile = 'Cache'.DIRECTORY_SEPARATOR.'Cache-'.hash($EncryptionType,$Salts[0].'CACHE').'.php';
  // / If no cache file exists yet (first run) we create one and write the $PostConfigUsers to it. 
  if (!file_exists($CacheFile)) $cacheCheck = file_put_contents($CacheFile, '<?php'.PHP_EOL.'$PostConfigUsers = array();');
  // / Make sure all sanity checks passed.
  if ($dirCheck && $logDirCheck && $indexCheck && $logCheck && $cacheCheck && $shareLocCheck) $InstallationIsVerified = TRUE;
  // / Clean up unneeded memory.
  $dirCheck = $indexCheck = $logCheck = $cacheCheck = $requiredDirs = $requiredDir = $dirExists = $indexExists = $logHash = $shareLocCheck = $logDir = $logDirCheck = NULL;
  unset($dirCheck, $indexCheck, $logCheck, $cacheCheck, $requiredDirs, $requiredDir, $dirExists, $indexExists, $logHash, $shareLocCheck, $logDir, $logDirCheck);
  return(array($LogFile, $VirusLogFile, $CacheFile, $InstallationIsVerified)); }
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
  global $Salts, $EncryptionType;
  $UserInput = $PasswordInput = $ClientTokenInput = $Mode = $FileKeys = $GlobalsAreVerified = FALSE;
  $saniString = '|\\/~#[](){};:$!#^&%@>*<"\'';
  // / Set authentication credentials from supplied inputs when inputs are supplied.
  if (isset($_POST['UserInput']) && isset($_POST['PasswordInput']) && isset($_POST['ClientTokenInput'])) { 
    $UserInput = $_SESSION['UserInput'] = str_replace(str_split($saniString), '', $_POST['UserInput']);
    $PasswordInput = $_SESSION['PasswordInput'] = str_replace(str_split($saniString), '', $_POST['PasswordInput']); 
    $ClientTokenInput = $_SESSION['ClientTokenInput'] = hash($EncryptionType, $_POST['ClientTokenInput']);
    $Mode = $_SESSION['Mode'] = str_replace(str_split($saniString), '', $_POST['Mode']); 
    $FileKeysInput = $_SESSION['FileKeysInput'] = str_replace(str_split($saniString), '', $_POST['FileKeysInput']);
    $ApprovedUserIDInput = $_SESSION['ApprovedUserIDInput'] = str_replace(str_split($saniString), '', $_POST['ApprovedUserIDInput']); }
  // / Detect if required variables are set.
  $GlobalsAreVerified = TRUE;
  // / Clean up unneeded memory.
  $saniString = NULL;
  unset($saniString);
  return(array($UserInput, $PasswordInput, $ClientTokenInput, $Mode, $FileKeys, $GlobalsAreVerified)); }
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
  global $Minute, $Salts, $LastMinute, $EncryptionType;
  $ServerToken = $ClientToken = NULL;
  $TokensAreValid = FALSE;
  $ServerToken = hash($EncryptionType, $Minute.$Salts[1].$Salts[3]);
  $ClientToken = hash($EncryptionType, $Minute.$PasswordInput); 
  $oldServerToken = hash($EncryptionType, $LastMinute.$Salts[1].$Salts[3]);
  $oldClientToken = hash($EncryptionType, $LastMinute.$PasswordInput);
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
  global $Users, $EncryptionType;
  $UserID = $UserName = $PasswordIsCorrect = $UserEmail = $UserIsAdmin = $AuthIsComplete = FALSE;
  // / Iterate through each defined user.
  foreach ($Users as $User) { 
    $UserID = $User[0];
    // / Continue ONLY if the $UserInput matches the a valid $UserName.
    if ($User[1] === $UserInput) { 
      $UserName = $User[1];
      // / Continue ONLY if all tokens match and the password hash is correct.
      if (hash($EncryptionType, $ServerToken.hash($EncryptionType, $ClientToken.$User[3])) === hash($EncryptionType, $ServerToken.hash($EncryptionType, $Salts[0].$PasswordInput.$Salts[0].$Salts[1].$Salts[2].$Salts[3]))) { 
        $PasswordIsCorrect = TRUE; 
        // / Here we grant the user their designated permissions and only then decide $AuthIsComplete.
        if (is_bool($User[4])) {
          $UserIsAdmin = $User[4]; 
          $UserEmail = $User[2];
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
// / A function to scan a supplied file for viruses with ClamAV using settings obtained from config.php.
// / Requires ClamAV to be installed on the server.
function virusScan($file) { 
  // / Set variables. Initialize check to FALSE.
  global $ThoroughAV, $VirusLogFile;
  $virusCheck = FALSE;
  // / Execute ClamAV on the target $file with $ThroughputAV settings from config.php and only output findings to the $VirusLogFile.
  // / Prepend the findings with "FOUND" so we have something easy to search for later to quantify results. 
  shell_exec(str_replace('  ', ' ', str_replace('  ', ' ', 'clamscan -r '.$ThoroughAV.' '.$file.' | grep FOUND >> '.$VirusLogFile)));
  // / Check that a logfile was created and read it if possible.
  if (file_exists($VirusLogFile)) { 
    $logFileDATA = @file_get_contents($VirusLogFile);
    // / Look for IoC in the logfile. Trigger the check TRUE or FALSE as needed.
    if ($strpos($logFileDATA, 'FOUND') !== FALSE) $virusCheck = FALSE; 
    else $virusCheck = TRUE; }
  // / Free un-needed memory.
  $virusCheck = NULL;
  unset($virusCheck);
  return ($virusCheck); }
// / ----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user initiates a file upload.
// / $files should be set to $_FILES['fileUpload'].
// / $fileKeys should be an array or CSV of keys that correspond to the $files array.
// / $userIDs should be an array of arrays or array of CSVs that correspond to the users who should access each $file. 
function upload($files, $fileKeys, $userIDs) {
  // / Set variables. Initialize internal checks to TRUE and the return check to FALSE.
  global $ShareLoc, $VirusLogFile, $VirusScan, $ThoroughAV;
  $UploadSuccess = FALSE;
  $keysFileCheck = $fileCheck = $virusCheck = TRUE;
  $DownloadURLs = array();
  if (!is_array($files['name'])) $files = array($files['name']);
  if (!is_array($fileKeys)) $fileKeys = array($fileKeys);
  if (!is_array($userIDs)) $userIDs = array($userIDs);
  foreach ($files as $key=>$file) {
    // / Reset variables for this iteration of the loop.
    $keysData = $url = '';
    // / Sanitize the input file for security.
    $file = Sanitize($file, FALSE);
    $fileKey = Sanitize($fileKeys[$key], TRUE);
    // / If $AuthenticationRequired is set to TRUE in config.php the $ApprovedUsers array from the KEYS file is prepared.
    if ($AuthenticationRequired) { 
      // / $userIDs[$key] needs to be an array (but it's probably a sting).
      // / $userIDs[$key] is an array of UserIDs that can access the currently selected file.
      // / $userIDs[$key] is controlled by the outer loop (above).
      if (!is_array($userIDs[$key])) $userIDs[$key] = array($userIDs[$key]);
      // / $userID is a handle for $userIDs[$key][$uKey]. It is a single user of the selected file.
      foreach ($userIDs[$key] as $uKey=>$userID) $userIDs[$key][$uKey] = int(Sanitize($userID, TRUE)); }
    // / If $AuthenticationRequired is set to FALSE in config.php we ignore the $userIDs argument and substitute a blank UserID (Anonymous).
    else $userIDs = '';
    // / Perform a sanity check on the input file.
    if ($file == '.' or $file == '..' or strpos($file, '.php') !== FALSE or strpos($file, '.js') !== FALSE or strpos($file, '.html') !== FALSE) continue;
    // / Remove duplicate directory separators.
    $filePath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file);
    $fileDataPath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file.'-KEYS.php');
    // / Craft a relative URL for the selected file and add it to an array that we can return later.
    $url = str_replace($dsds, $ds, 'Temp'.$ds.$fileKey.$ds.$file);
    $DownloadURLs = array_push($DownloadURLs, $url);
    // / Create a KEYS file containing the permissions settings for the selected file.
    $keysData = '<?php $ApprovedUsers = array('.implode(','.$userIDs).');';
    $keysFileCheck = @file_put_contents($fileDataPath, $keysData);
    // / Copy the uploaded file to the $filePath (in the $ShareLoc).
    $fileCheck = @copy($files['tmp_name'][$key], $filePath); 
    // / If $VirusScan is set to TRUE in config.php we check the $filePath with ClamAV.
    // / ClamAV is required for virus scanning!
    if ($VirusScan) {
      $virusCheck = virusScan($file);
      // / If viruses were found we delete all files that were created and stop processing the request.
      if (!$virusCheck) {
        @unlink($filePath);
        @unlink($fileDataPath); 
        $DownloadURLs = array(); 
        break; } } 
    // / Check that no errors have been triggered.
    if ($virusCheck && $fileCheck && $keysFileCheck && file_exists($filePath)) $UploadSuccess = TRUE; }
  // / Free un-needed memory.
  $txt = $file = $F0 = $F2 = $F3 = $ClamLogFileDATA = $Upload = $MAKELogFile = NULL;
  unset($txt, $file, $F0, $F2, $F3, $ClamLogFileDATA, $Upload, $MAKELogFile); 
  return (array($DownloadURLs, $UploadSuccess)); } 
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
    // / Reset variables for this iteration of the loop.
    $ApprovedUsers = array();
    $url = '';
    // / Sanitize the input file for security.
    $file = Sanitize($file, FALSE);
    $fileKey = Sanitize($fileKeys[$key], TRUE);
    // / Perform a sanity check on the input file.
    if ($file == '.' or $file == '..' or strpos($file, '.php') !== FALSE or strpos($file, '.js') !== FALSE or strpos($file, '.html') !== FALSE) continue;
    // / Remove duplicate directory separators.
    $filePath = str_replace($dsds, $ds, $ShareLoc.$ds.$file.$Key.$ds.$file);
    $fileDataPath = str_replace($dsds, $ds, $ShareLoc.$ds.$fileKey.$ds.$file.'-KEYS.php');
    $fileTempPath - str_replace($dsds, $ds, $TempLoc.$ds.$fileKey.$ds.$file); 
    // / Check for the existence of a key file for the selected file.
    if (file_exists($fileDataPath)) { 
      // / If a key file exists for the selected file, we load it into memory to retrieve permission settings and keys.
      require($fileDataPath);
      // / If the $AuthenticationRequired is set to TRUE in config.php we check that the current user has permission to view the selected file.
      // / Also validate the $ApprovedUsers array and make sure it is not empty.
      if ($AuthenticationRequired) if (!in_array($userID, $ApprovedUsers) && $ApprovedUsers) { 
        // / If the $ApprovedUsers array starts with an empty or 0 entry it means the file is approved for anonymous download. 
        // / Anonymous download means we skip tripping the $permsCheck variable to FALSE.
        if ($ApprovedUsers[0] != '' && $ApprovedUsers[0] != 0) { 
          $permsCheck = FALSE;
          // / If the user lacks permissions to view any of their selected files the loop will terminate and cleanup operations will begin.
          break; } } 
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

// / Check if any file operations are required. 
// / If we're being called upon asynchronously we won't build an entire HTML page.
list ($Doctype, $DownloadRequired, $UploadRequired) = opCheck();
echo $Doctype;

// / Perform some basic environment checks before we start writing to the filesystem.
// / Specifically we check that the PHP version is 7.0 or greater, the O/S is not Windows, & that the config file is readable.
if (phpCheck() && osCheck() && loadConfig()) {
  // / Set the time. $Minute and $LastMinute area used for token generation. 
  list ($Date, $Time, $Minute, $LastMinute) = verifyDate();

  // / This code verifies the integrity of the application.
  // / Also generates required directories in case they are missing & creates required log & cache files.
  list ($LogFile, $VirusLogFile, $CacheFile, $InstallationIsVerified) = verifyInstallation();
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
    list ($UserInput, $PasswordInput, $ClientTokenInput, $Mode, $FileKeysInput, $GlobalsAreVerified) = verifyGlobals();
    if (!$GlobalsAreVerified) { 
      requireLogin(); 
      dieGracefully(5, 'User is not logged in!'); }
    else if ($Verbose) logEntry('Verified global variables.');

    // / This code ensures that a same-origin UI element generated the login request.
    // / Also protects against packet replay attacks by ensuring that the request was generated recently and by making each request unique. 
    list ($ClientToken, $ServerToken, $TokensAreValid) = generateTokens($ClientTokenInput, $PasswordInput);
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
    $ClientToken = hash($EncryptionType, rand(10000000000,1000000000000).rand(10000000000,1000000000000));
    $ServerToken = hash($EncryptionType, rand(10000000000,1000000000000).rand(10000000000,1000000000000));
    $UserID = 0;
    $UserName = 'Anonymous';
    $UserEmail = 'Anonymous@anon.net';
    $UserIsAdmin = FALSE; } 

  // / If a Download operation is required we don't output a UI. Instead we ouput the download URL's for the requested files.
  if (isset($_POST['Downloadfiles']) or isset($_POST['UploadFiles'])) { 
    if ($DownloadRequired) { 
      list ($DownloadURLs, $DownloadSuccess) = download($_POST['DownloadFiles'], $FileKeysInput, $UserID);
      if (!$DownloadSuccess) dieGracefully(7, 'Download Error!');
      else if ($Verbose) logEntry('Download URL Generated.'); }

    // / If an Upload operation is required we don't output a UI. Instead we ouput the download URL's for the uploaded files.
    if ($UploadRequired) { 
      list ($DownloadURLs, $UploadSuccess) = upload($_POST['UploadFiles'], $FileKeysInput, $ApprovedUserIDInput); }
      if (!$UploadSuccess) dieGracefully(8, 'Upload Error!');
      else if ($Verbose) logEntry('Upload Complete.'); } 

  // / If there are no file operations to perform we prepare a dynamic HTML UI for the user.
  else { 
    // / Dynamically build the UI depending on which functionality is desired.
    require('header.php');
    if (!isset($Mode)) require('landing.php');
    else { 
      if ($Mode == 'UPLOAD') require('upload.php'); 
      if ($Mode == 'DOWNLOAD') require('download.php');
    }
    require('footer.php'); } }
// / ----------------------------------------------------------------------------------