<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once "php/includes/dbh.inc.php";
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Search{
    function __construct($table_name){
        $this->table_name = $table_name;
        $this->fields_full_info = [];
        $this->set_filter();
    }

    //Lay danh sach cac fields
    function get_fields_list(){
        global $conn;
        
        $fields = [];
        $sql = "SELECT `column_name` FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`='" . constant('DB_NAME') . "' AND `TABLE_NAME`='" . $this->table_name . "';";
        
        $query_rs = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query_rs)) {
            $fields[] = $row['column_name'];
        }

        return $fields;
    }

    //Lay danh sach ten field va ca kieu du lieu cua no
    function get_fields_list_full_info(){
        if (!empty($this->fields_full_info)) return $this->fields_full_info;

        global $conn;
        $fields_full_info = [];
        if (empty($this->table_name)) return [];

        $fields = $this->get_fields_list();
        $sql = "SELECT * FROM `" . $this->table_name . "` LIMIT 0, 1";
        $query_rs = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($query_rs);
        foreach ($fields as $k=>$v){
            $full_info = [];
            $full_info["name"] = $v;
            $full_info["label"] = get_fieldlabel($this->table_name, $v);

            $full_info["value_example"] = $row[$v];
            $full_info["type"] = "text";
            $full_info["numeric"] = false;
            if (is_numeric($full_info["value_example"])) {
                $full_info["type"] = "number";
                $full_info["numeric"] = true;
            }

            $fields_full_info[] = $full_info;
        }

        $this->fields_full_info = $fields_full_info;
        return $this->fields_full_info;
    }

    //Hien thi search form
    function get_filter_form(){
        $filter_form_list = [];

        $fields = $this->get_fields_list_full_info();
        foreach ($fields as $k=>$v){
            if ($v["numeric"]) {//Truong dang so
                $filter_form = $v;
                $filter_form_list[] = $filter_form;
            } else {//Truong dang text
                $filter_form_list[] = $v;
            }
        }

        return $filter_form_list;
    }

    //Gan filter de search
    function set_filter($filter = null){
        if (empty($filter)) $this->filter = [];
        else $this->filter = $filter;
    }

    function get_where_sql(){
        if (empty($this->filter)) return " ";
        
        $filter = $this->filter;
        $fields = $this->get_fields_list_full_info();
        $cond = " TRUE ";
        //Xu ly tim kiem voi keyword
        if (!empty($filter["keyword"])){//co tim kiem theo keyword
            $cond = " FALSE ";
            $keyword = $filter["keyword"];
            foreach ($fields as $k=>$v){
                if (!$v["numeric"]) {//Truong dang text
                    if (normalizeString($v["name"]) == strtolower($v["name"])) {
                        $cond .= " OR (`" . $v["name"] . "`  like '%" . $keyword . "%') ";
                    } else {
                        //$cond .= normalizeString($v["name"]) . " vs " . $v["name"] . "<br/>";
                    }
                }
            }
            
            $cond = " (" . $cond . ") ";
        }
        
        //Xu ly tim kiem nang cao
        //var_dump($filter); ;//die();
        foreach ($fields as $k=>$v){
            //echo $v["name"] . "===<br/>";
            if ( isset( $filter[$v["name"]]) ){//Co tim kiem field nay
                //echo $v["name"] . "<br/>";
                if ($v["numeric"]) {//Truong dang so
                    if (($filter[$v["name"]]["min"] != "") && ($filter[$v["name"]]["max"]) != "") {
                        $cond .= " AND (`" . $v["name"] . "` BETWEEN " . $filter[$v["name"]]["min"] . " AND " . $filter[$v["name"]]["max"] . ") ";
                    } elseif (($filter[$v["name"]]["min"] != "") ) {
                        $cond .= " AND (`" . $v["name"] . "` >= " . $filter[$v["name"]]["min"] . ") ";
                    } elseif (($filter[$v["name"]]["max"] != "")) {
                        $cond .= " AND (`" . $v["name"] . "` <= " . $filter[$v["name"]]["max"] . ") ";
                    }
                    
                } else {//Truong dang text
                    if (!empty($filter[$v["name"]])){
                        $cond .= " AND (`" . $v["name"] . "` like '%" . $filter[$v["name"]] . "%') ";
                    }
                }
            }
        }
        
        //echo $cond; die();

        return " WHERE ($cond) ";
    }

    //Lay SQL de tim kiem
    function get_sql_search(){
        return "SELECT * FROM `" . $this->table_name  . "` " . $this->get_where_sql();
    }

    
}

?>

<?php

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
//Lay danh sach cac Table
function get_table_list(){
    global $conn;

    $list = [];
    $query_select_tables_name = "SELECT table_name FROM information_schema.tables WHERE table_type = 'base table'
        and table_schema = '" . constant('DB_NAME') . "' and table_name != 'user'";
    $query_rs = mysqli_query($conn, $query_select_tables_name);

    while ($row = mysqli_fetch_assoc($query_rs)) {
        $list[] = $row['table_name'];
    }

    return $list;
}

//Lay gia tri da duoc gui qua phuong thuc POST
function post_value($name, $op = ""){
    $data = $_POST;
    
    if (!isset($data[$name])) return "";
    if (!empty($op)) {
        return $data[$name][$op];
    }
    return $data[$name];
}

//
function export_excel($table_name, $sql){
    global $conn;

    ob_end_clean(); // this is solution
    
    $fileName = $table_name . '_' . date('Ymd') . ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $query_select_data = $sql;
    $result = mysqli_query($conn, $query_select_data);
    $row_count = mysqli_num_rows($result);
    if ($row_count > 0) {
        $field_objs = mysqli_fetch_fields($result);

        //get column names of result
        foreach($field_objs as $field) {
            $field_names[] = get_fieldlabel( $table_name, $field->name );
        }

        //get number of columns
        $column_count = count($field_names);

        //create column index ABCDEF...AA..$column_count
        //set auto fit for column
        for ($char_excel = 'A', $char_index = 0; $char_index < $column_count; $char_index++, $char_excel++) {
            $column_character_excel[] = $char_excel;
            $spreadsheet->getActiveSheet()->getColumnDimension($char_excel)->setAutoSize(true);
        }
        

        //write header
        foreach ($column_character_excel as $index => $column_char) {
            $sheet->setCellValue($column_char . '1', $field_names[$index]);
        }


        //create row index, start from 2. Exp: A2, B2, C2
        $row_index = 2;
        while(null != ($row = mysqli_fetch_assoc($result))) {
            $column_index = 0;
            foreach($row as $index => $cell_value) {
                $sheet->setCellValue($column_character_excel[$column_index] . $row_index, $cell_value);
                $column_index++;
            }
            $row_index++;
        }
    }
        
    // $result->free();
    //Create file excel.xlsx
    $writer = new Xlsx($spreadsheet);

    //return to client's  browser
    $writer->save('php://output');
}

//Tiep nhan thong tin
$table_name = empty($_POST["tableName"])?"":$_POST["tableName"];
$active_form = empty($_POST["active_form"])?"keyword-search":$_POST["active_form"];

//Chuan bi du lieu
$table_list = get_table_list();
$search = new Search($table_name);
$search->set_filter($_POST);
$fields_full_info = $search->get_fields_list_full_info();
$sql_search = $search->get_sql_search();
$filter_form_list = $search->get_filter_form();

if (isset($_POST["exportData"])){
    $sql = $_POST["request"];
    $sql = urldecode($sql);
    export_excel($table_name, $sql);
    exit();
}

//Hien thi du lieu
include 'php/header.php';

?>

<head>
    <!-- DataTables CSS -->
    <link href="css/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="css/dataTables/dataTables.responsive.css" rel="stylesheet">
</head>

<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Home</a>
        </div>

        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        

        <?php include('php/menu.php'); ?>
    </nav>

    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header" style="color:#337ab7">
                    	<?php 
                    	  if (empty($table_name)) {
                            echo "<h2>Please choose a table to search!</h2>";    
                            echo "
                                <form name='tableName' action='?' method='POST' class='form-inline'>
                                <select name='tableName' id='tableName' class='form-control'>";
                            echo "<option value='' >--- Select a table ---</option>";
                            foreach ($table_list as $k=>$v){
                                echo "<option value='$v' " . (($v == $table_name)?"selected":"") . ">" . $v . "</option>";
                            }
                            echo "</select>
                                <input type='hidden' name='showData' value='tableName'/>
                                </form>";
                    	  } else {
                            echo "<h2>";
                            echo "
                                <form name='tableName' action='?' method='POST' class='form-inline'>
                                  <div class='form-group'>
                                    Current table: 
                                    <select name='tableName' id='tableName' class='form-control'>";
                                foreach ($table_list as $k=>$v){
                                    echo "<option value='$v' " . (($v == $table_name)?"selected":"") . ">" . $v . "</option>";
                                }
                            echo    "</select>
                                    <input type='hidden' name='showData' value='tableName'/>
                                  </div>
                                </form>";
                            echo    "</h2>";
                            
                            echo "<form id='keyword-search' class='form-horizontal' style='display: none;' method='POST' action='?'>";
                                echo '
                                <div class="form-group col-sm-4">
                                    <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">Keyword</span>
                                    <input type="text" class="form-control" name="keyword" placeholder="Keyword" value="'.post_value("keyword").'" aria-describedby="basic-addon1"/>
                                    </div>
                                </div>
                                <input type="hidden" name="tableName" value="'.$table_name.'"/>
                                <input type="hidden" name="showData" value="search"/>
                                <input type="hidden" name="active_form" value="keyword-search"/>
                                <input type="submit" name="submit" value="Search"/>
                                <div class="action show-advanced-search">Advanced search...</div>
                            </form>
                            <div class="clearfix"></div>';

                            echo "<form id='advanced-search' class='form-horizontal' style='display: none;' method='POST' action='?'>";
                            foreach($filter_form_list as $k=>$v){
                                echo '
                                <div class="form-group col-sm-4">
                                  <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1">' . $v["label"] . '</span>';
                                if ($v["numeric"]) {
                                    echo '<input type="' . $v["type"] . '" step="any" class="form-control" name="' . $v["name"] . '[min]" placeholder="Min of ' . $v["label"] . '" value="'.post_value($v["name"], "min").'"aria-describedby="basic-addon1"/>';
                                    echo '<input type="' . $v["type"] . '" step="any" class="form-control" name="' . $v["name"] . '[max]" placeholder="Max of ' . $v["label"] . '" value="'.post_value($v["name"], "max").'" aria-describedby="basic-addon1"/>';

                                } else {
                                    echo '<input type="' . $v["type"] . '" class="form-control" name="' . $v["name"] . '" placeholder="' . $v["label"] . '" value="'.post_value($v["name"]).'" aria-describedby="basic-addon1"/>';
                                }
                                echo '
                                  </div>
                                </div>';
                            }
                            echo "
                                <div class='form-group col-sm-4'>
                                    <input type='hidden' name='tableName' value='$table_name'/>
                                    <input type='hidden' name='showData' value='search'/>
                                    <input type='hidden' name='active_form' value='advanced-search'/>
                                    <input type='submit' name='submit' value='Search'/>
                                    <div class='action show-keyword-search'>Hide advanced search</div>
                                </div>
                            </form>
                            <form action='' method='post' name='exportData' style='float:right'>
                                <div>
                                    <button type='submit' id='submit' name='exportData' class='btn btn-primary'>Export exel</button>
                                    <input type='hidden' name='request' value='". urlencode($search->get_sql_search()) ."' />
                                    <input type='hidden' name='tableName' value='". $table_name . "' />
                                </div>
                            </form>
                            <div class='clearfix'></div>";
                        }
                        ?>
                    </div>
                        
					<div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-search">
                            	<?php 
                            		if(isset($_POST['showData'])) {
                            			echo '<thead class="info">';
                                        foreach ($fields_full_info as $k=>$v){
                                            echo "<th>". $v['label'] . "</th>";
                                        }                                    
                                		echo '</thead>';

	                                    $sql = $sql_search;
	                                    //echo $sql;
	                                    $result = mysqli_query($conn, $sql);

                                        echo '<tbody>';
	                                    $i = 0;
	                                    while ($row = mysqli_fetch_assoc($result)) {
	                                        if ($i % 2 == 0) {
	                                            echo '<tr class="success">';
	                                        } else {
	                                            echo '<tr class="warming">';
	                                        }
	                                        foreach($row as $value) {
	                                            echo '<td>' . $value . '</td>';
	                                        }
	                                        echo '</tr>';
	                                        $i = $i + 1;
	                                    }
	                                    echo '</tbody>';
                            		}
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<style>
    .action{cursor: pointer}}
</style>

<!-- jQuery -->
<script src="js/jquery.min.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>
<!-- DataTables JavaScript -->
<script src="js/dataTables/jquery.dataTables.min.js"></script>
<script src="js/dataTables/dataTables.bootstrap.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="js/startmin.js"></script>

<?php
echo '<script>$(document).ready(function(){$("#' . $active_form . '").show(50);})</script>';
?>
<script>
$(document).ready(function(){
    $(".action.show-advanced-search").click(function(){
        $("#keyword-search").hide(100);
        $("#advanced-search").show(100);
    });
    $(".action.show-keyword-search").click(function(){
        $("#advanced-search").hide(100);
        $("#keyword-search").show(100);
    });
    $("select[name=tableName]").change(function(){
        $("form[name=tableName]").submit();
    });
    
    $('#dataTables-search').DataTable({
        responsive: true
    });
})

</script>