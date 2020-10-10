<?php   
    include "php/includes/dbh.inc.php";
    include 'vendor/autoload.php';
    include 'php/header.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<?php
    function selectAllTableNames() {
        $query_select_tables_name = "SELECT table_name FROM information_schema.tables WHERE table_type = 'base table'
                                and table_schema = '" . constant('DB_NAME') . "' and table_name != 'user'";
        $result = mysqli_query($conn, $query_select_tables_name);
        while ($row = mysqli_fetch_assoc($result)) {
            $table_names[] = $row['table_name'];
        }
        return $table_names;

    }
?>


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
        <!-- DataTables CSS -->
        <link href="css/dataTables/dataTables.bootstrap.css" rel="stylesheet">
        <!-- DataTables Responsive CSS -->
        <link href="css/dataTables/dataTables.responsive.css" rel="stylesheet">

        <script type="text/javascript">
        
		$(document).ready(function(){

            $('#table_name').multiselect({
                nonSelectedText: 'Select Table',
                buttonWidth:'400px'
            });
            $('#property_type').multiselect({
                nonSelectedText: 'Select Property',
                buttonWidth:'400px'
            });
        });

        </script>

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
                    <h1 class="page-header" style="color:#337ab7">Export Red River Data </h1>
                    <form role="form" action="" method="post"> 

                        <h3>Select Table </h3>

                        <select id="table_name" name="table_name"  class="form-control" >
                            <?php

                                //TODO: create a function selectAllTableNames
                                //$table_names = selectAllTableNames();
                                $query_select_tables_name = "SELECT table_name FROM information_schema.tables WHERE table_type = 'base table'
                                and table_schema = '" . constant('DB_NAME') . "' and table_name != 'user'";
                                $result = mysqli_query($conn, $query_select_tables_name);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $table_names[] = $row['table_name'];
                                }
                                
                                //
                                foreach($table_names as $name) {
                                    echo "<option value='" . $name . "'>" . $name . "</option>";
                                }
                            ?>  
                        </select>

                        <h3>Select Properties</h3>
                        <select id="property_type" name="property_type" class="form-control" >
                            <option value="location">Location</option>
                            <option value="physical">Physical Property</option>
                            <option value="chemical">Chemical Property</option>
                            <option value="ent_chemical">Environmental Chemical Properties</option>
                            <option value="chemical_quality">Chemical Properties of Quality</option>
                        </select>

                        </br>
                        </br>
                        <button type="submit" class="btn btn-primary" name="viewExportData">View Data</button> 
                        <button type="submit" class="btn btn-success" name="exportDataToFile">Export Data</button>
                    </form>
					<div class="panel-body">
						<div class="row">

							<!-- main form end -->
							
							
						</div>

                    

                    <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-view">
                                <?php

                                    if(isset($_POST['viewExportData'])) {
                                        $table_name = $_POST['table_name'];
                                        $property_type = $_POST['property_type'];
                                        $selected_fields = get_property_fields($_POST['property_type']);
                                        
                                        //
                                        echo '<thead class="info">';
                                        $query_select_data = "select ";
                                        foreach ($selected_fields as $k=>$v){
                                            $fieldlabel = get_fieldlabel2($table_name, $v);
                                            if ($fieldlabel != null) {
                                                echo "<th>". $fieldlabel . "</th>";
                                                $query_select_data .= $v . ",";
                                            }
                                        }
                                        echo '</thead>';


                                        $query_select_data = rtrim($query_select_data, ",");
                                        $query_select_data .= " from `$table_name`;" ;
                                        
                                        
                                        //echo $sql;
                                        $result = mysqli_query($conn, $query_select_data);

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
                                    } else if (isset($_POST['exportDataToFile'])) {
                                        $table_name = $_POST['table_name'];
                                        $property_type = $_POST['property_type'];
                                        $selected_fields = get_property_fields($_POST['property_type']);
                                        
                                        ob_end_clean(); // this is solution

                                        $fileName = $table_name . '_' . date('Ymd') . ".xlsx";
                                        
                                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                                        header('Content-Disposition: attachment; filename="' . $fileName . '"');
                                        header('Content-Transfer-Encoding: binary');
                                        
                                        // Create new Spreadsheet object
                                          $spreadsheet = new Spreadsheet();
                                          $sheet = $spreadsheet->getActiveSheet();
                                        
                                        $query_select_data = "select ";
                                        foreach ($selected_fields as $k=>$v){
                                            $fieldlabel = get_fieldlabel2($table_name, $v);
                                            if ($fieldlabel != null) {
                                                $field_names[] = $fieldlabel;
                                                $query_select_data .= $v . ",";
                                            }
                                        }

                                        $query_select_data = rtrim($query_select_data, ",");
                                        $query_select_data .= " from `$table_name`;" ;
                                        
                                        
                                        //echo $sql;
                                        $result = mysqli_query($conn, $query_select_data);
                                        $row_count = mysqli_num_rows($result);
                                        if ($row_count > 0) {
                                            //get number of columns
                                            $column_count = count($field_names);
                                
                                            //create column index ABCDEF...AA..$column_count
                                            //set auto fit for column
                                            for ($char_excel = 'A', $char_index = 0; $char_index < $column_count; $char_index++, $char_excel++) {
                                                $column_character_excel[] = $char_excel;
                                                $sheet->getColumnDimension($char_excel)->setAutoSize(true);
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
