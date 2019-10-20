<?php
/*
HonestRepair Diablo Engine  -  Sharer
https://www.HonestRepair.net
https://github.com/zelon88

Licensed Under GNU GPLv3
https://www.gnu.org/licenses/gpl-3.0.html

Author: Justin Grimes
Date: 10/20/2019
<3 Open-Source

This is the uploader UI file for the Sharer Web Application. 
Based off the HonestRepair Diablo Engine.

This file was meant to be "included()" or "required()" by ShareCore.php.

The <!DOCTYPE HTML> and <html> opening tags are located at the top of ScanCore.php.
The opening <html> tags, <meta> tags, <head> tags, scripts & stylesheets are located in header.php.
The closing </html> tags are located in footer.php.
The upload.php and download.php files are meant to output the body of this HTML application.
*/
// / ----------------------------------------------------------------------------------

// / ----------------------------------------------------------------------------------
// / Set some variables to pre-populate the form fields with.
if (isset($_SESSION['ClientTokenInput'])) $CTI = $_SESSION['ClientTokenInput'];
else $CTI = '';
if (isset($_SESSION['UserIDInput'])) $UIDI = $_SESSION['UserIDInput'];
else $UIDI = '';
if (isset($_SESSION['PasswordInput'])) $PI = $_SESSION['PasswordInput'];
else $PI = '';
if (isset($_SESSION['Mode'])) $MI = $_SESSION['Mode'];
else $MI = '';
// / ----------------------------------------------------------------------------------

?>

<form action="ShareCore.php" class="dropzone">
  <input type="hidden" id="ClientTokenInput" name="ClientTokenInput" value="<?php echo $CTI; ?>"/>
  <input type="hidden" id="UserIDInput" name="UserIDInput" value="<?php echo $UIDI; ?>"/>
  <input type="hidden" id="PasswordIDInput" name="PasswordIDInput" value="<?php echo $PI; ?>"/>
  <input type="hidden" id="Mode" name="Mode" value="<?php echo $MI; ?>"/>
</form>
