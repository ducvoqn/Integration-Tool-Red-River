<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
session_start();
if(isset($_SESSION['userId']) == false) {
    header("location: ./php/login.php");
    exit;
}
require './php/includes/dbh.inc.php';
require 'vendor/autoload.php';
include 'php/header.php';
	
function convert_vi_to_en($str) {
    $str = preg_replace("/(a|à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    //$str = str_replace(" ", "-", str_replace(“&*#39;","",$str));
    return $str;
}

function normalizeString ($str = '')
{
    $str = strip_tags($str);
    $str = convert_vi_to_en($str);
    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
    $str = htmlentities($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
    $str = str_replace(' ', '_', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '_', $str);
    return $str;
}
function save_field_label($labels){
    global $_fieldlabels;
    
    $tenfile = "_fieldlabels.php";
    $myfile = fopen($tenfile, "w") or die("Unable to open file!");
	$txt = "<?php \n";
    fwrite($myfile, $txt);
	foreach ($labels as $k=>$v) {//table
		foreach ($v as $k2=>$v2) {//field
			$txt = '$'."_fieldlabels[\"".$k."\"][\"".$k2."\"] = \"".$v2."\";\n";
            fwrite($myfile, $txt);
		}
    }
    //Bo sung nhung cai con thieu
    foreach ($_fieldlabels as $k=>$v) {//table
		foreach ($v as $k2=>$v2) {//field
		    if (empty($labels[$k][$k2])) {
    			$txt = '$'."_fieldlabels[\"".$k."\"][\"".$k2."\"] = \"".$v2."\";\n";
                fwrite($myfile, $txt);
		    }
		}
    }
	$txt = " ?>";
    fclose($myfile);
}

/******** Class *******/
class Table {
    function __construct($table_name, $primary_key = "ten_mau"){
        $this->table_name = $table_name;
        $this->primary_key = $primary_key;
    }

    //Create table if not exists
    function create_table_dynamic(){
        global $conn;

        if ($this->check_if_exists()){//Table is already exist
            //add diff column
            echo "Da ton tai " . $this->table_name . "<br/>";
            $fields_already = $this->get_fields();
            $this->old_fields = $fields_already;
            $fields_excel = $this->fields;

            //Get additional fields
            $arr_diff = array_diff($fields_excel, $fields_already);

            //add new column if exist
            if ( count($arr_diff) != 0) {
                $query_add_column = "ALTER TABLE `" . $this->table_name . "` ";
                foreach($arr_diff as $new_column) {
                    $query_add_column .= " ADD COLUMN `$new_column` VARCHAR(500),";
                }
                $query_add_column = rtrim($query_add_column, ",");
                mysqli_query($conn, $query_add_column);
            }

        } else {//If not exists before
            echo "Chua ton tai " . $this->table_name . "<br/>";
            $header = $this->fields;
            $this->old_fields = $header;
            if (!empty($header)){
                $query_create_table = "CREATE TABLE `" . $this->table_name . "` (";
                foreach ($header as $value) {
                    //convert and normalizeString
                    $std_column = $value;
                    //query
                    $query_create_table .= ' `' . $std_column . '`' . ' varchar(255), ';
                }
                //primary key
                $query_create_table .= "PRIMARY KEY(`" . $this->primary_key . "`)";
                //
                $query_create_table .= ")";
                
                mysqli_query($conn, $query_create_table);
            }
        } 
    }
    //Add a field name
    function add_field($field_name){
        $this->fields[] = $field_name;
    }

    //name of fields
    function get_fields(){
        global $conn;
        
        $query_select_column_names = "SELECT `column_name` 
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA`='" . constant('DB_NAME') . "' 
        AND `TABLE_NAME`='" . $this->table_name . "';";

        $query_rs = mysqli_query($conn, $query_select_column_names);
        $row_count = mysqli_num_rows($query_rs);

        //if table is exist already
        $column_names = [];
        if ($row_count > 0) {
            //compare header and column names
            while($query_row = mysqli_fetch_assoc($query_rs)) {
                $column_names[] = $query_row["column_name"];
            }
        }

        return $column_names;
    }

    // data = ["field_name_1"=>"data 1", "field_name_2"=>"data 2", ...]
    function add_record($data){
        global $conn;

        if (empty($data)) return;

        if ($this->check_if_overlap($data)) {//if overlap 
            $this->merge_data($data);
        } else {//insert new data
            $sql_field = ""; $sql_value = "";
            foreach ($data as $field=>$value) {
                $sql_field .= "`" . $field . "`,";
                $sql_value .= "'" . $value . "',";
            }
            $sql_field = rtrim($sql_field, ',');
            $sql_value = rtrim($sql_value, ',');

            $query_insert_data = "INSERT INTO `" . $this->table_name . "` ($sql_field) VALUES ($sql_value) ";
            //echo $query_insert_data . "<br/>"; die();
            mysqli_query($conn, $query_insert_data);            
        }
    }

    // data = ["field_name_1"=>"data 1", "field_name_2"=>"data 2", ...]
    function check_if_overlap($new_data) {
        global $conn;

        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;

        $sql_where = " WHERE (`" . $this->primary_key . "` like '" . $new_data[ $this->primary_key ] . "') ";        
        $query_select = "SELECT * FROM `" . $this->table_name . "` $sql_where";
        $query_rs = mysqli_query($conn, $query_select);
        $counter = mysqli_num_rows($query_rs);

        return ($counter > 0);
    }

    //Merge data to exist record
    function merge_data($new_data){
        global $conn;

        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;

        //Update missing value, addtional field and new updated data 
        $sql_update = " SET ";
        foreach ($new_data as $field=>$value) {
            if (!empty($value)) {
                $sql_update .= " `" . $field . "` = '" . $new_data[$field] . "', ";
            }
        }
        $sql_update = rtrim($sql_update, ", ");
        $sql_where = " WHERE (`" . $this->primary_key . "` like '" . $new_data[ $this->primary_key ] . "') ";
        $query_update = "UPDATE `" . $this->table_name . "` $sql_update $sql_where";

        $query_rs = mysqli_query($conn, $query_update);
    }
    /*
    //Merge data with new field
    function merge_data_with_new_field($new_data){
        global $conn;

        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;

        $sql_where = " WHERE (`" . $this->primary_key . "` like '" . $new_data[ $this->primary_key ] . "') ";        
        
        $sql_update = " SET ";
        foreach ($new_data as $field=>$value) {
            $sql_update .= " `" . $field . "` = '" . $new_data[$field] . "', ";
        }
        $sql_update = rtrim($sql_update, ", ");

        $query_update = "UPDATE `" . $this->table_name . "` $sql_update $sql_where";
        $query_rs = mysqli_query($conn, $query_update);
    }

    //Replace empty cell in database by new data
    function merge_data_replacing_empty_cell($new_data){
        global $conn;

        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;

        $new_data_core = [];
        foreach ($new_data as $field=>$value) {
            if (empty($value)) continue;
            $new_data_core[$field] = $value;
        }

        //Kiem tra 
        foreach ($new_data_core as $field=>$value){
            //Check if there is a record overlap and missing a cell in this field
            if ($this->check_if_missing_cell($new_data_core, $field)){
                //Replace it
                $this->replace_missing_cell($new_data_core, $field);
            }
        }
    }

    function check_if_missing_cell($new_data, $field_name){
        global $conn;
        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;
        if (empty($new_data[ $field_name ])) return false;

        $sql_where = " WHERE (`" . $this->primary_key . "` like '" . $new_data[ $this->primary_key ] . "') ";        
        $sql_where .= " AND (`" . $field_name . "` IS NULL) ";
        
        $query_select = "SELECT * FROM `" . $this->table_name . "` $sql_where";
        $query_rs = mysqli_query($conn, $query_select);
        $counter = mysqli_num_rows($query_rs);

        echo $counter . ": ==> $field_name <br/>" .  $query_select . "<br/><br/>";

        return ($counter > 0);
    }
    function replace_missing_cell($new_data, $field_name){
        global $conn;
        if (empty($new_data)) return false;
        if (empty($new_data[ $this->primary_key ])) return false;
        if (empty($new_data[ $field_name ])) return false;

        $sql_where = " WHERE (`" . $this->primary_key . "` like '" . $new_data[ $this->primary_key ] . "') ";        
        $sql_where .= " AND (`" . $field_name . "` IS NULL) ";


        $sql_update = " SET `" . $field_name . "` = '" . $new_data[$field] . "'";
        
        $query_update = "UPDATE `" . $this->table_name . "` $sql_update $sql_where";
        echo $query_update . "<br/><br/>";
        $query_rs = mysqli_query($conn, $query_update);
    }
    */

    //check if table exists. Return: true/false
    function check_if_exists(){
        if (!empty($this->min_number_of_field)) return true;
        $this->min_number_of_field = count($this->get_fields());
        return (!empty($this->min_number_of_field));
    }
}

class Importer{
    function __construct(){

    }

    //Import from a file in server
    function import($file_path){
        //  Identify the type of $targetPath
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
        //  Create a new Reader of the type that has been identified
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        //  Load $inputFileName to a Spreadsheet Object
        $spreadSheet = $reader->load($file_path);

        // init variables
        $sheet_index = 0;
        $primary_key = "Tên mẫu";
        $std_pk = normalizeString($primary_key);
        
        //all worksheets to read
        $workSheetCollection = $spreadSheet->getWorksheetIterator();
        
        //working on a sheet
        foreach ($workSheetCollection as $workSheet) {
            $this->import_from_worksheet($workSheet);
        }
        //
    }
    
    function import_from_worksheet($workSheet){
        $table_name = $workSheet->getTitle();
        $table = new Table($table_name);
        $header = [];//header from excel

        $row_counter = 0;
        foreach ($workSheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator(); // By default, only cells that have a value set will be iterated.
            $cellIterator->setIterateOnlyExistingCells(TRUE);
            
            //Create table 
            if ($row_counter == 0){//First row (header)
                //get headers
                $labels = [];
                foreach ($cellIterator as $cell) {
                    $cellValue = $cell->getFormattedValue();
                    if($cellValue == '') break;
    
                    $label = $cellValue;
                    $field_name = normalizeString($cellValue);
                    
                    //add to header
                    $header[] = $field_name;
                    $labels[$table_name][$field_name] = $label;
                    $table->add_field($field_name);
                }
                save_field_label($labels);

                //=== Create table if not exists. Add different fields if exists
                $table->create_table_dynamic();

            } 

            //insert data
            else { 
                //Get row data
                $row_data = [];
                $column_counter = 0;
                $field_count = count($header);
                foreach ($cellIterator as $cell) {
                    $cellValue = $cell->getFormattedValue();
                    if ($column_counter < $field_count) {
                        $row_data[ $header[$column_counter] ] = $cellValue;
                    }
                    $column_counter++;
                }
                //Insert/Merge row data to table
                if (!empty($row_data)) $table->add_record($row_data);
            }
            $row_counter++;
        }
    }
}


/******** Main process *******/
$importer = new Importer();
if (isset($_POST["import"]))
{		
    $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if(in_array($_FILES["file"]["type"],$allowedFileType)){
        $targetPath = 'uploads/'.$_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

        $importer->import($targetPath);
    }
    echo "Done <hr/>";
} 
?>

<form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
    <div>
        <input type="file" name="file" id="file" accept=".xls,.xlsx"> <p></p>									
        <button type="submit" id="submit" name="import" class="btn btn-primary">Import Red River Data</button>
    </div>

</form>