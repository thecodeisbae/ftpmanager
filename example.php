<?php 

include 'FTPManager.php';
use thecodeisbae\FTPManager\FTPManager;


function debug($args) /** Return info about variable $args **/
{
    echo '<pre style="background-color:black;color:white;padding:25px;font-size:150%">Debug information<br><br>', print_r($args, 1),'</pre>';
    exit;
}

/* Provide params */
FTPManager::$ftpHost = 'ftpupload.net';
FTPManager::$ftpUser = 'epiz_30483327';
FTPManager::$ftpPassword = 'YpBfyoGcbUHOp';

/* Initalize and login */
FTPManager::init();
FTPManager::connect();
FTPManager::passive(true);
debug(FTPManager::list('/'));