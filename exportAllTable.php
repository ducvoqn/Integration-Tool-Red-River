
<?php   
    include_once "php/includes/dbh.inc.php";
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
    

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

function create_spreadsheet($table_names) {
    //create SpreadSheet
    $spreadsheet = new Spreadsheet();
    //create sheet with table's name
    $sheet_count = 0;
    foreach($table_names as $table_name) {
        if ($sheet_count == 0) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($table_name);
        } else {
            $workSheet = new Worksheet($spreadsheet, $table_name);
            $spreadsheet->addSheet($workSheet);
        }
        $sheet_count += 1;
    }
    return $spreadsheet;
}

//return mysqli result
function select_data_from_table($table_name) {
    global $conn;
    $sql = "select * from `". $table_name ."`";
    $rs = mysqli_query($conn, $sql);
    return $rs;
}

function create_sheet_header($sheet, $table_name, $mysqli_rs) {
    $column_chars = array();
    if($mysqli_rs != false) {
        $fields = mysqli_fetch_fields($mysqli_rs);
        $column_count = count($fields);
        for($i = 0, $column_char = 'A'; $i < $column_count; $i++, $column_char++) {
            $column_chars[] = $column_char;
            $field_name = get_fieldlabel ($table_name, $fields[$i]-> name);
            $sheet-> setCellValue($column_char . '1', $field_name);
            $sheet-> getColumnDimension($column_char)-> setAutoSize(true);
        }
    }
    return $column_chars;
}

function fill_sheet_data($sheet, $column_chars, $mysqli_rs) {
    $row_index = 2;
    while(null != ($row = mysqli_fetch_assoc($mysqli_rs))) {
        $column_index = 0;
        foreach($row as $index => $cell_value) {
            $sheet->setCellValue($column_chars[$column_index] . $row_index, $cell_value);
            $column_index++;
        }
        $row_index++;
    }
}


if(isset($_POST['exportEntireData'])) {
    $fileName = 'RedRiverDb' . '_' . date('Ymd') . ".xlsx";

    ob_end_clean();
    //init header
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    
    //select all name table
    $table_names = get_table_list();
    $spreadsheet = create_spreadsheet($table_names);
    foreach($table_names as $table_name) {
        //create sheet
        $sheet = $spreadsheet->getSheetByName($table_name);
        $sheet->setTitle($table_name);
        
        //select data from each table
        $result = select_data_from_table($table_name);
        //create header
        $column_chars = create_sheet_header($sheet, $table_name, $result);
        //fill data
        fill_sheet_data($sheet, $column_chars, $result);
    }
    //save, export
    // $result->free();
    //Create file excel.xlsx
    $writer = new Xlsx($spreadsheet);

    //return to client's  browser
    $writer->save('php://output');
    exit();
}

?>

<?php
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
                    <h1 class="page-header" style="color:#337ab7">
                        <!-- May be dataset later!-->
                        <h2> Export Entire Database</h2>
                    </h1>
                        
					<div class="panel-body">
                        <!-- May be dataset later!-->
                        <form role="form" action="?" method="post">
                            <button type="submit" class="btn btn-success" name="exportEntireData">Export Entire Database</button>
                        </form>


                    </div>
                </div>
            </div>
        </div>

    </div>


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

