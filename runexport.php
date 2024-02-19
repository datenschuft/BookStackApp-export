<?php
/*
Bookstack Export - Script
(c) wenger@unifox.at 2021
License: AGPL3

Version 1.0.0 (13072021)
*/

include('config.php');

// ---- some functions ----
function getbook($id,$books) {
  $matchingbook=array();
  foreach ($books as $book) {
    if ($book['id']==$id){
      $matchingbook=$book;
      break;
    }
  }
  return $matchingbook;
}

function getchapter($id,$bid,$chapters) {
  //echo "siche nach $id,$bid\n";
  $matchingchapter=array();
  foreach ($chapters as $chapter) {
    if ($chapter['id']==$id AND $chapter['book_id']==$bid){
      $matchingchapter=$chapter;
      break;
    }
  }
  return $matchingchapter;
}


function getpage($id,$pages) {
  //echo "siche nach $id,$bid\n";
  $matchingpage=array();
  foreach ($pages as $page) {
    if ($page['id']==$id ){
      $matchingpage=$page;
      break;
    }
  }
  return $matchingpage;
}

function getexportpath($pageid) {
  global $config,$pages,$books,$chapters;
  $exportpath="";
  $page=getpage($pageid,$pages);
  $book = getbook($page['book_id'],$books);
  $chapter = getchapter($page['chapter_id'],$page['book_id'],$chapters);
  $exportpath=$config['exportpath']."/".$book['slug']."/";
  if (isset($chapter['slug'])) {
    $exportpath.=$chapter['priority']."-".$chapter['slug']."/";
  }
  $exportpath.=$page['priority']."-".$page['slug'];
  return $exportpath;
}

// ------ Class
class RESTClient {
  const USER_AGENT = 'unifox.at';
}




$auth = 'Authorization: Token '.$config['wikitoken'].':'.$config['wikitokensecret'];
$opts = array (
        'http' => array (
            'method' => "GET",
            'header' => $auth,
            'user_agent' => RESTClient :: USER_AGENT,
        )
);
$context = stream_context_create($opts);

//-------------------------------------------------------
// read all Pages
echo "reading all pages...";
$pages=array();
$resultcount=1;
$skip=0;
$paging=100;
while ($resultcount <> 0) {
  //echo "<br />".$resultcount;
  $url = $config['wikiurl']."/api/pages?count=$paging&offset=$skip";;
  // reading Data
  if ($fp = fopen($url, 'r', false, $context)){
    $result = "";
    while ($str = fread($fp,1024)) {
      $result .= $str;
    }
    fclose($fp);
  } else {
    echo "$url URL Fetch error - aborting";
    exit(99);
  }
  // processing data
  $resultarray=json_decode($result, true)['data'];
  $resultcount=count($resultarray);
  //echo "count pages: $resultcount\n";
  //print_r($resultarray);
  foreach ($resultarray as $result) {
       //do the magic
        //print_r($result);
        array_push($pages,$result);
  }
  $skip=$skip+$paging;
}
echo "done\n";

//-------------------------------------------------------
// read all chapters
echo "reading all chapters...";
$chapters=array();
$resultcount=1;
$skip=0;
$paging=100;
while ($resultcount <> 0) {
  //echo "<br />".$resultcount;
  $url = $config['wikiurl']."/api/chapters?count=$paging&offset=$skip";
  //&$expand=NavEmployeeToPerStructure
  //echo "\n$url\n";
  // reading Data
  if ($fp = fopen($url, 'r', false, $context)){
    $result = "";
    while ($str = fread($fp,1024)) {
      $result .= $str;
    }
    fclose($fp);
  } else {
    echo "$url URL Fetch error - aborting";
    exit;
  }
  // processing data
  //print_r($result);
  $resultarray=json_decode($result, true)['data'];
  $resultcount=count($resultarray);
  //echo "count chapters: $resultcount\n";
  //print_r($resultarray);
  foreach ($resultarray as $result) {
       //do the magic
        //print_r($result);
        array_push($chapters,$result);
  }
  $skip=$skip+$paging;
}
echo "done\n";

//-------------------------------------------------------
// read all Books
echo "reading all books...";
$books=array();
$resultcount=1;
$skip=0;
$paging=100;
while ($resultcount <> 0) {
  //echo "<br />".$resultcount;
  $url = $config['wikiurl']."/api/books?count=$paging&offset=$skip";
  //&$expand=NavEmployeeToPerStructure
  //echo "\n$url\n";
  // reading Data
  if ($fp = fopen($url, 'r', false, $context)){
    $result = "";
    while ($str = fread($fp,1024)) {
      $result .= $str;
    }
    fclose($fp);
  } else {
    echo "$url URL Fetch error - aborting";
    exit;
  }
  // processing data
  //print_r($result);
  $resultarray=json_decode($result, true)['data'];
  $resultcount=count($resultarray);
  //echo "count books: $resultcount\n";
  //print_r($resultarray);
  foreach ($resultarray as $result) {
       //do the magic
        //print_r($result);
        array_push($books,$result);
  }
  $skip=$skip+$paging;
}
echo "done\n";


echo "starting the export\n";
if (!is_writable($config['exportpath']."/")){
  echo "Export ".$config['exportpath']." not writeable - aborting\n";
  exit;
}
foreach ($pages as $page) {
  $exportpath=$config['exportpath']."/";
  //fetching slugs
  //echo "p".$page['id']." ".$page['name']."\n";
  //books
  $bookid=$page['book_id'];
  //echo "b".$bookid."\n";
  $book = getbook($bookid,$books);
  $bookslug=$book['slug'];
  $exportpath.=$bookslug."/";
  //echo "bookslug".$bookslug."\n".$exportpath."\n";
  // Book- Directory
  if (!is_dir($config['exportpath']."/".$bookslug."/")) {
    mkdir ($config['exportpath']."/".$bookslug."/");
    $fp = fopen($config['exportpath']."/".$bookslug."/bookinfo.txt", 'w');
    fwrite($fp, "[id] => ".$book['id']."\n".
    "[name] => ".$book['name']."\n".
    "[slug] => ".$book['slug']."\n".
    "[description] => ".$book['description']."\n".
    "[created_at] => ".$book['created_at']."\n".
    "[updated_at] => ".$book['updated_at']."\n");
    fclose($fp);
  }
  //chapter
  $chapterid=$page['chapter_id'];
  #echo "c".$chapterid."\n";
  $chapter = getchapter($chapterid,$bookid,$chapters);
  if (count($chapter)<>0){
    $chapterslug=$chapter['slug'];
    $chapterprio=$chapter['priority'];
    //echo "-------> chapterslug".$chapterprio."-".$chapterslug."\n";
    $exportpath.=$chapterprio."-".$chapterslug."/";
    if (!is_dir($config['exportpath']."/".$bookslug."/".$chapterprio."-".$chapterslug."/")) {
      mkdir ($config['exportpath']."/".$bookslug."/".$chapterprio."-".$chapterslug."/");
      $fp = fopen($config['exportpath']."/".$bookslug."/".$chapterprio."-".$chapterslug."/chapterinfo.txt", 'w');
      fwrite($fp, "[id] => ".$chapter['id']."\n".
      "[book_id] => ".$chapter['book_id']."\n".
      "[name] => ".$chapter['name']."\n".
      "[slug] => ".$chapter['slug']."\n".
      "[description] => ".$chapter['description']."\n".
      "[priority] => ".$chapter['priority']."\n".
      "[created_at] => ".$chapter['created_at']."\n".
      "[updated_at] => ".$chapter['updated_at']);
      fclose($fp);
    }
  }
  echo "exporting".$exportpath.$page['priority']."-".$page['slug']."\n";
  $url=$config['wikiurl']."/api/pages/".$page['id']."/export/pdf";
  if ($pdf = file_get_contents($url, false, $context)){
    $fp = fopen($exportpath.$page['priority']."-".$page['slug'].".pdf", 'w');
    fwrite($fp,$pdf);
    fclose($fp);
  } else {
    echo "PDF URL Fetch error - try HTML\n";
    //try html export
    $url=$config['wikiurl']."/api/pages/".$page['id']."/export/html";
    if ($html = file_get_contents($url, false, $context)){
      $fp = fopen($exportpath.$page['priority']."-".$page['slug'].".html", 'w');
      fwrite($fp,$html);
      fclose($fp);
    } else {
      echo "HTML URL Fetch error - abort\n";
      exit;
    }
  }
}
if (isset($config['exportattachments']) AND $config['exportattachments']==true){
  // Attachments ?
  echo "---Attachments----\n";
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $mysqli = new mysqli($config['sqlhost'], $config['sqluser'], $config['sqlpwd'], $config['sqldb']);
  $result = $mysqli->query("SELECT `id`, `name`, `path`, `uploaded_to` FROM `attachments`");

  while ($row = $result->fetch_assoc()) {
    $exportpath=getexportpath($row["uploaded_to"]);
    //echo "creating $exportpath/".$row['name'];
    if (!file_exists($exportpath."/")){
      mkdir ($exportpath."/",0777,true);
    }
    if ($config['exportattachmentsmeth']=="copy") {
      if (file_exists($config['attachmentslocalpath'].'/'.$row['path'])){
        if (!copy($config['attachmentslocalpath'].'/'.$row['path'],$exportpath."/".$row['name'])) {
          echo "copy ".$config['attachmentslocalpath'].'/'.$row['path']." to ".$exportpath."/".$row['name']." not successful\n";
        }
      } else {
        echo "local file missing ".$config['attachmentslocalpath'].'/'.$row['path']."\n";
      }
    } elseif ($config['exportattachmentsmeth']=="web") {
      //not implemented jet
    }

  }
}

echo "done";
?>
