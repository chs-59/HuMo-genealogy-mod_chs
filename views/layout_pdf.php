<?php
session_start();

// *** Seperate file for PDF scripts. Copy of layout.php ***

include_once(__DIR__ . "/../include/db_login.php"); //Inloggen database.
include_once(__DIR__ . '/../include/show_tree_text.php');
include_once(__DIR__ . "/../include/db_functions_cls.php");
$db_functions = new db_functions($dbh);

include_once(__DIR__ . "/../include/safe.php");
include_once(__DIR__ . "/../include/settings_global.php"); // System variables
include_once(__DIR__ . "/../include/settings_user.php"); // User variables

include_once(__DIR__ . "/../include/get_visitor_ip.php");
$visitor_ip = visitorIP();

// *** Debug HuMo-genealogy front pages ***
if ($humo_option["debug_front_pages"] == 'y') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// *** Check if visitor is allowed access to website ***
if (!$db_functions->check_visitor($visitor_ip, 'partial')) {
    echo 'Access to website is blocked.';
    exit;
}

// *** Set timezone ***
include_once(__DIR__ . "/../include/timezone.php"); // set timezone 
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Check if visitor is a bot or crawler ***
$bot_visit = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
// *** Line for bot test! ***
//$bot_visit=true;

// *** Language processing after header("..") lines. *** 
include_once(__DIR__ . "/../languages/language.php"); //Taal

// *** Process LTR and RTL variables ***
$dirmark1 = "&#x200E;";  //ltr marker
$dirmark2 = "&#x200F;";  //rtl marker
$rtlmarker = "ltr";
$alignmarker = "left";
// *** Switch direction markers if language is RTL ***
if ($language["dir"] == "rtl") {
    $dirmark1 = "&#x200F;";  //rtl marker
    $dirmark2 = "&#x200E;";  //ltr marker
    $rtlmarker = "rtl";
    $alignmarker = "right";
}
if (isset($screen_mode) and $screen_mode == "PDF") {
    $dirmark1 = '';
    $dirmark2 = '';
}

// *** Default values
$page = 'index';
$head_text = $humo_option["database_name"];
$tmp_path = '';


// *** Generate BASE HREF for use in url_rewrite ***
// SERVER_NAME   127.0.0.1
//     PHP_SELF: /url_test/index/1abcd2345/
// OF: PHP_SELF: /url_test/index.php
// REQUEST_URI: /url_test/index/1abcd2345/
// REQUEST_URI: /url_test/index.php?variabele=1
$base_href = '';
if ($humo_option["url_rewrite"] == "j" and $tmp_path) {
    // *** url_rewrite ***
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    } else {
        $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    }
    $base_href = $uri_path;
} else {
    // *** Use standard uri ***
    $position = strrpos($_SERVER['PHP_SELF'], '/');
    $uri_path = substr($_SERVER['PHP_SELF'], 0, $position) . '/';
}

// *** To be used to show links in several pages ***
include_once(__DIR__ . '/../include/links.php');
$link_cls = new Link_cls($uri_path);

// *** For PDF reports: remove html tags en decode ' characters ***
function pdf_convert($text)
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES);
    //$text=@iconv("UTF-8","cp1252//IGNORE//TRANSLIT",$text);	// Only needed if FPDF is used. We now use TFPDF.
    return $text;
}

// *** Set default PDF font ***
$pdf_font = 'DejaVu';

// *** june 2022: FPDF supports romanian and greek characters ***
//define('FPDF_FONTPATH',"include/fpdf16//font/unifont");
require(__DIR__ . '/../include/tfpdf/tfpdf.php');
require(__DIR__ . '/../include/tfpdf/tfpdfextend.php');

// *** TODO check if this is still needed. Set variabele for queries ***
//$tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);

// *** Added in nov 2023 (used in outline_report_pdf.php) ***
$tree_id = 0;
if (isset($_POST['tree_id']) and is_numeric($_POST['tree_id'])) {
    $tree_id = $_POST['tree_id'];
}
