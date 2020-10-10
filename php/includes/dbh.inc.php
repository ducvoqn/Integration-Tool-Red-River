<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'ducvodepzai');
// define('DB_PASSWORD', 'ducvodepzai');
// define('DB_NAME', 'red_river_database');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'id15023071_ducvodepzai');
define('DB_PASSWORD', 'Vominhduc911*');
define('DB_NAME', 'id15023071_red_river_database');
 
/* Attempt to connect to MySQL database */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

?>
<?php

if (file_exists("_fieldlabels.php")) include("_fieldlabels.php");
else if (file_exists("/storage/ssd1/071/15023071/public_html/RedRiver2/_fieldlabels.php")) include("/storage/ssd1/071/15023071/public_html/RedRiver2/_fieldlabels.php");

function get_fieldlabel($table_name, $field_name){
    global $_fieldlabels;
    
    if (empty($_fieldlabels[$table_name][$field_name])) return $field_name;
    return $_fieldlabels[$table_name][$field_name];
}

function get_fieldlabel2($table_name, $field_name) {
    global $_fieldlabels;
    
    if (empty($_fieldlabels[$table_name][$field_name])) return null;
    return $_fieldlabels[$table_name][$field_name];
}

?>

<?php
include("/storage/ssd1/071/15023071/public_html/RedRiver2/_property_fields.php");
function get_property_fields($property_name){
    global $_property_fields;
    
    if (empty($_property_fields[$property_name])) return "";
    return $_property_fields[$property_name];
}

?>