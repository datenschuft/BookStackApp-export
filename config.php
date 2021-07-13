<?php
/*
Bookstack Export - Script configuration
(c) wenger@unifox.at 2021
License: AGPL3

Version 1.0.0 (03072021)
*/
$config=array();
// MySQL - to the datzabase of the bookstack wiki
$config['sqlhost']="localhost";
$config['sqldb']="bookstack";
$config['sqluser']="bookstack";
$config['sqlpwd']="supergeheimespwd";
// url of the wiki (required für api-calls)
$config['wikiurl']="https://wiki.example.com";
// tokenid and tokensecret
// have a look at $config['wikiurl']/api/docs
$config['wikitoken']="youtrokenid";
$config['wikitokensecret']="yourtokensecret";
$config['exportpath']="/home/user/wikiexport";

$config['exportattachments']=true;
// copy = running on webserverhost to copy attachments from webroot-path
// web = download attachments
$config['exportattachmentsmeth']="copy";
// required config for exportmethod copy
$config['attachmentslocalpath']="/var/www/bookstack/storage";
