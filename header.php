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

This is the header UI file for the Sharer Web Application. 
Based off the HonestRepair Diablo Engine. 

This file was meant to be "included()" or "required()" by the Sharer UI.

The <!DOCTYPE HTML> and <html> opening tags are located at the top of ScanCore.php.
The opening <html> tags, <meta> tags, <head> tags, scripts & stylesheets are located in header.php.
The closing </html> tags are located in footer.php.
The upload.php and download.php files are meant to output the body of this HTML application.
*/
if (!isset($ApplicationName)) $ApplicationName = 'Sharer';
// / ----------------------------------------------------------------------------------
?>

  <head>
    <meta charset="UTF-8">
    <meta name="description" content="Self-Hosted File Sharing">
    <meta name="author" content="zelon88">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ApplicationName; ?> | Share Files</title>
    <script src="dropzone.js"></script>
    <link rel="stylesheet" href="dropzone.css">
  </head>
  <body>