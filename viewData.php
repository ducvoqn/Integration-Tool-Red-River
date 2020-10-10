
<?php   
    include_once "php/includes/dbh.inc.php";
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
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

?>

<?php

$tableName = empty($_POST["tableName"])?"":$_POST["tableName"];

$table_name = $tableName;
$table_list = get_table_list();

if (isset($_POST['exportData'])) {

ob_end_clean(); // this is solution

$fileName = $tableName . '_' . date('Ymd') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');

// Create new Spreadsheet object
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
// set the names of header cells
    //   $sheet->setCellValue('A1', 'ID');
      
    // // Add some data
    //   $x=2;
    //     $sheet->setCellValue('A'.$x, 'ID');
        
        $query_select_data = "SELECT * FROM `$table_name`";
        $result = mysqli_query($conn, $query_select_data);
        $row_count = mysqli_num_rows($result);
        if ($row_count > 0) {
            
            $field_objs = mysqli_fetch_fields($result);

            //get column names of result
            foreach($field_objs as $field) {
                $field_names[] = $field->name;
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

                    	<?php 
                    	if (empty($table_name)) {
                            echo "<h2>Please choose a table to view data</h2>";    
                            echo "
                                <form name='tableName' action='?' method='POST' class='form-inline'>
                                <select name='tableName' id='tableName' class='form-control'>";
                            echo "<option value='' >--- Select a table ---</option>";
                            foreach ($table_list as $k=>$v){
                                echo "<option value='$v' " . (($v == $table_name)?"selcted":"") . ">" . $v . "</option>";
                            }
                            echo "</select>
                                <input type='hidden' name='showData' value='tableName'/>
                                </form>";
                    	  } else {
                            echo "<h2>";
                            echo "
                                <form name='tableName' action='?' method='POST' class='form-inline'>
                                  <div class='form-group'>
                                    Showing data from table: 
                                    <select name='tableName' id='tableName' class='form-control'>";
                                foreach ($table_list as $k=>$v){
                                    echo "<option value='$v' " . (($v == $table_name)?"selcted":"") . ">" . $v . "</option>";
                                }
                            echo    "</select>
                                    <input type='hidden' name='showData' value='tableName'/>
                                  </div>
                                </form>";
                            echo    "</h2>";
                    	  }
                        ?>
                                    
                    </h1>
                        
					<div class="panel-body">
                        <div class="table-responsive">
                            <?php
                            if(isset($_POST['showData'])) {
                                echo "<form action='' method='post' name='exportData' id='exportData'>" .
                                            '<div>' .
                                                "<button type='submit' id='submit' name='exportData' class='btn btn-primary'>Export Data From Table $table_name </button> " .
                                                "<input type='hidden' value='$table_name' name='tableName' /> " .
                                            '</div>' .
                                    '</form>';
                                }
                            ?>
                            
                            <table class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-view">

                            	<?php 
                            		if(isset($_POST['showData'])) {
                            			$tableName = $_POST["tableName"];
                            			echo '<thead class="info">';
                                        $query_select_column_names = "SELECT `column_name` 
                                        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                                        WHERE `TABLE_SCHEMA`='" . constant('DB_NAME') . "'
                                        AND `TABLE_NAME`='$tableName';";

                                        $query_rs = mysqli_query($conn, $query_select_column_names);

                                        while ($row = mysqli_fetch_assoc($query_rs)) {
                                            echo "<th>". get_fieldlabel($table_name, $row['column_name']) . "</th>";
                                        }
                                		echo '</thead>
                                		<tbody>';

	                                    $sql = "SELECT * FROM  `$tableName`";
	                                    $result = mysqli_query($conn, $sql);

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
	                                    echo "</tbody>";
                            		}
                                ?>
                            </table>
                        </div>
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

<script>
$(document).ready(function(){
    $("select[name=tableName]").change(function(){
        console.log("co ma");
        $("form[name=tableName]").submit();
    });
    
    $('#dataTables-view').DataTable({
        responsive: true
    });
})
</script>
