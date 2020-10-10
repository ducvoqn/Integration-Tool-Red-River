<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Import Excel File To Database</title>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

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
	<!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
	  
            <div class="row">
			
                <div class="col-lg-12">
                    <h1 class="page-header" style="color:#337ab7">Import Red River Data </h1>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										<i style="color:#337ab7" class="fa fa-group fa-fw"></i> Choose an excel file to import
										<br>
										<form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
											<div>
												<input type="file" name="file" id="file" accept=".xls,.xlsx"> <p></p>									
												<button type="submit" id="submit" name="import" class="btn btn-primary">Import Red River Data</button>
											</div>
									
										</form>
									</div>
								</div>
							</div>
							<!-- main form end -->
							
							
						</div>
					</div>
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading" >
								<i class="fa fa-bell fa-fw" ></i> Imported Data
							</div>
							<!-- /.panel-heading -->
							<div class="panel-body">
							

							

					<?php 		
						if (isset($_POST["import"]))
						{		
							$allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
							if(in_array($_FILES["file"]["type"],$allowedFileType)){
								$targetPath = 'uploads/'.$_FILES['file']['name'];
								move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

								//  Identify the type of $targetPath
								$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($targetPath);
								//  Create a new Reader of the type that has been identified
								$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
								//  Load $inputFileName to a Spreadsheet Object
								$spreadSheet = $reader->load($targetPath);

								// init variables
								$sheet_index = 0;
								$primary_key = "Tên mẫu";
								$std_pk = normalizeString(convert_vi_to_en($primary_key));

								$master_table = "";
								$master_header = "";
								
								//all worksheets to read
								$workSheetCollection = $spreadSheet->getWorksheetIterator();
								//working on a sheet
								foreach ($workSheetCollection as $workSheet) {
									echo '<div class="table-responsive">';
									echo '<table class="table" border="1">';

									$sheetTitle0 = $workSheet->getTitle();
									$table_name = $sheetTitle0;
									if ($sheet_index == 0) {
										$master_table = $sheetTitle0;
									}
									//insert query
									//echo "sheet name:" . $sheetTitle0;

									// khoi tao mang header [H1, H2, H3]
									$header_count = 0;
									//header block "H1, H2, H3"
									$header_block = "";
									$has_std_key = false;
									$i = 0;
									$stop = false;
									foreach ($workSheet->getRowIterator() as $row) {
										$cellIterator = $row->getCellIterator(); // By default, only cells that have a value set will be iterated.
										$cellIterator->setIterateOnlyExistingCells(TRUE);


										//column count
										$column_index = 0;
										if ($i == 0){
											echo '<thead>';
											echo '<tr class="info">';
										    foreach ($cellIterator as $cell) {
										    	$cellValue = $cell->getFormattedValue();
										    	if($cellValue == '') break;

												$label = $cellValue;
												$cellValue = normalizeString($cellValue);
												
												//add to header
												$header[] = $cellValue;
												$labels[$table_name][$cellValue] = $label;
										    	//prepare for insert_into_str
										    	$header_block .= ' `' . $cellValue . '`,';
										    	echo '<td>'. $label .'</td>';
										    }
										    echo '</tr>';
											echo '</thead>';

											save_field_label($labels);

											echo '<tbody>';
											$header_count = count($header);
											//fix header_block H1,H2,H3, => H1,H2,H3
											$header_block = rtrim($header_block, ",");

											//check table already exist or not exist
											//get column names of a table (sheet_name)
											$query_select_column_names = "SELECT `column_name` 
											FROM `INFORMATION_SCHEMA`.`COLUMNS` 
											WHERE `TABLE_SCHEMA`='" . constant('DB_NAME') . "' 
			    							AND `TABLE_NAME`='$sheetTitle0';";

			    							$query_rs = mysqli_query($conn, $query_select_column_names);
			    							$row_count = mysqli_num_rows($query_rs);

			    							//if table is exist already
			    							if ($row_count > 0) {
			    								//compare header and column names
			    								while($query_row = mysqli_fetch_assoc($query_rs)) {
			    									$column_names[] = $query_row["column_name"];
			    								}

												$arr_diff = array_diff($header, $column_names);
												//$arr_ints_count = count(array_intersect($header, $column_names));

			    								//check new column
			    								if ( count($arr_diff) != 0) {
			    									//add new column if exist
			    									$query_add_column = "ALTER TABLE `$sheetTitle0` ";
			    										foreach($arr_diff as $new_column) {
			    											$query_add_column .= " ADD COLUMN `$new_column` VARCHAR(500),";
			    										}
			    										$query_add_column = rtrim($query_add_column, ",");
			    										mysqli_query($conn, $query_add_column);
			    								}
			    								
			    							} else {
			    								//create table
			    								$query_create_table = "create table `" . $sheetTitle0 . "` (";
												foreach ($header as $value) {

													//convert and normalizeString
													$std_column = normalizeString(convert_vi_to_en($value));
													if ($std_column == $std_pk) {
														$has_std_key = TRUE;
													}

													//query
													$query_create_table = $query_create_table . '`' . $std_column . '`' . ' varchar(500),';
												}


												if ($has_std_key == TRUE) {
													// //add constraint primary key ten_mau to first table
													if ($sheet_index == 0) {
		                    							$query_create_table .= "PRIMARY KEY(`$std_pk`)";
		                    						} else {
		                    							//add constraint foreign key ten_mau to another table
		                    							$query_create_table .= "FOREIGN KEY(`$std_pk`) REFERENCES `$master_table` (`$std_pk`)";
		                    						}
												}
												
												//
												$query_create_table = rtrim($query_create_table, ",");
												//TODO: add origin name
		                    					
		                    					$query_create_table .= ")";
		                    					//echo $query_create_table;
												mysqli_query($conn, $query_create_table);

											}
											
											$insert_into_str = "insert into `$sheetTitle0` (" . $header_block . ") values (";

										} else {
											if ($i % 2 == 0){
												echo '<tr class="success">';
											}else{
												echo '<tr class="warming">';
											}

											//"insert into $sheetTitle0 (" . $header_block . ") values (";
											$query_insert_data = $insert_into_str;


											// echo '<td><form method="post" action="printb.php?id='.$i.'" target="_blank"></td>';
											foreach ($cellIterator as $cell) {
												if ($column_index < $header_count) {
													$cellValue = $cell->getFormattedValue();

													//check no more data and stop
													if ($cellValue == '' && $column_index == 0) {
														$stop = true;
														break;
													}

													//show in view
											    	echo '<td>' . $cellValue . '</td>';

											    	//add to query
											    	$escapedCellValue = mysqli_real_escape_string($conn, $cellValue);
											    	$query_insert_data .= "'$escapedCellValue',";
											    	//echo "$query_insert_data";
											    	//continue
											    	$column_index = $column_index + 1;
										    	}
										    }

										    if ($stop == true) { break; }

										    //execute query insert data
										    $query_insert_data = rtrim($query_insert_data, ',') . ");";
										    mysqli_query($conn, $query_insert_data);
										}

									    $i = $i + 1;
									}
	    							//final
	    							//unset all array
	    							unset($header);
	    							unset($column_names);
	    							$sheet_index = $sheet_index + 1;

	    							//add ending of table
	    							echo '</tbody>';
									echo '</table>';
    							}
							}
						}
							?>
								
							
					
						</div>
						
					</div>
							<!-- notification panel end -->
				</div>
			</div>
		</div>
	</div>
	
</div>

<!-- jQuery -->
<script src="../js/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../js/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../js/startmin.js"></script>

</body>
</html>
