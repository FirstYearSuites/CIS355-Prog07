<?php
session_start();
//if (!isset($_SESSION["username"])){
//    header("Location: login.php");
//}
// include the class that handles database connections
require "database.php";
// include the class containing functions/methods for "clubs" table

require "clubs.class.php";
$club = new Club();

// set active record field values, if any
// (field values not set for display_list and display_create_form)
if(isset($_GET["id"]))                  $id = $_GET["id"];
if(isset($_POST["clubName"]))           $club->clubName = $_POST["clubName"];
if(isset($_POST["members"]))            $club->members = $_POST["members"];
if(isset($_POST["statement"]))          $club->statement = $_POST["statement"];
if (isset($_FILES['Filename']))         $club->fileName       = $_FILES['Filename']['name'];
if (isset($_FILES['Filename']))         $club->tempFileName   = $_FILES['Filename']['tmp_name'];
if (isset($_FILES['Filename']))         $club->fileSize       = $_FILES['Filename']['size'];
if (isset($_FILES['Filename']))         $club->fileType       = $_FILES['Filename']['type'];
// "fun" is short for "function" to be invoked
if(isset($_GET["fun"])) $fun = $_GET["fun"];
else $fun = "display_list";
switch ($fun) {
    case "display_list":        $club->list_records();
        break;
    case "display_create_form": $club->create_record();
        break;
    case "display_read_form":   $club->read_record($id);
        break;
    case "display_update_form": $club->update_record($id);
        break;
    case "display_delete_form": $club->delete_record($id);
        break;
    case "insert_db_record":    $club->insert_db_record();
        break;
    case "update_db_record":    $club->update_db_record($id);
        break;
    case "delete_db_record":    $club->delete_db_record($id);
        break;
    default:
        echo "Error: Invalid function call (clubs.php)";
        exit();
        break;
}