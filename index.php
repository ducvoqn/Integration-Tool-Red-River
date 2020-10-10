
<?php   
    include_once "php/includes/dbh.inc.php";
    require 'vendor/autoload.php';
    
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

//=== chuan bi du lieu
$table_list = get_table_list();
$field_counter = 0;
foreach ($_fieldlabels as $k=>$v) $field_counter += count($v); 

$record_counter = 0;
foreach ($table_list as $k=>$v) {
    $sql = "SELECT COUNT(*) as soluong FROM `$v`";
    $query_rs = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query_rs);
    $record_counter += $row["soluong"];
}

//=== Show data
    include 'php/header.php';
?>
<!-- Timeline CSS -->
<link href="css/timeline.css" rel="stylesheet">
<!-- DataTables CSS -->
<link href="css/dataTables/dataTables.bootstrap.css" rel="stylesheet">
<!-- DataTables Responsive CSS -->
<link href="css/dataTables/dataTables.responsive.css" rel="stylesheet">

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
                    <div class="page-header">Dashboard</div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <i class="fa fa-database fa-5x"></i>
                                        </div>
                                        <div class="col-xs-9 text-right">
                                            <div class="huge"><?php echo count($table_list); ?></div>
                                            <div>Tables</div>
                                        </div>
                                    </div>
                                </div>
                                <a href="./viewData.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">Show all</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="panel panel-green">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <i class="fa fa-tasks fa-5x"></i>
                                        </div>
                                        <div class="col-xs-9 text-right">
                                            <div class="huge"><?php echo $field_counter;?></div>
                                            <div>Fields</div>
                                        </div>
                                    </div>
                                </div>
                                <a href="./exportTable.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">View details</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="panel panel-yellow">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <i class="fa fa-tasks fa-5x"></i>
                                        </div>
                                        <div class="col-xs-9 text-right">
                                            <div class="huge"><?php echo $record_counter;?></div>
                                            <div>Records</div>
                                        </div>
                                    </div>
                                </div>
                                <a href="./search.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">View details</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>

                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-clock-o fa-fw"></i> Processing
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <ul class="timeline">
                                <li>
                                    <div class="timeline-badge"><i class="fa fa-check"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Import data</h4>
                                            </p>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Import data from excel file. A worksheet corresponds a table in database. </p>
                                            <hr/>
                                            <a href="./importDataFromExcelFile.php">Click here to import from excel</a>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-inverted">
                                    <div class="timeline-badge warning"><i class="fa fa-credit-card"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">View data</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laudantium
                                                maiores
                                                odit qui est tempora eos, nostrum provident explicabo dignissimos
                                                debitis
                                                vel! Adipisci eius voluptates, ad aut recusandae minus eaque facere.</p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="timeline-badge danger"><i class="fa fa-bomb"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Export data to excel</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellendus
                                                numquam
                                                facilis enim eaque, tenetur nam id qui vel velit similique nihil iure
                                                molestias aliquam, voluptatem totam quaerat, magni commodi quisquam.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-inverted">
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Lorem ipsum dolor</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptates est
                                                quaerat asperiores sapiente, eligendi, nihil. Itaque quos, alias
                                                sapiente
                                                rerum quas odit! Aperiam officiis quidem delectus libero, omnis ut
                                                debitis!</p>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="timeline-badge info"><i class="fa fa-save"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Lorem ipsum dolor</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nobis minus
                                                modi
                                                quam ipsum alias at est molestiae excepturi delectus nesciunt, quibusdam
                                                debitis amet, beatae consequuntur impedit nulla qui! Laborum, atque.</p>
                                            <hr>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                                        data-toggle="dropdown">
                                                    <i class="fa fa-gear"></i> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a href="#">Action</a>
                                                    </li>
                                                    <li><a href="#">Another action</a>
                                                    </li>
                                                    <li><a href="#">Something else here</a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    <li><a href="#">Separated link</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Lorem ipsum dolor</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sequi fuga odio
                                                quibusdam. Iure expedita, incidunt unde quis nam! Quod, quisquam.
                                                Officia
                                                quam qui adipisci quas consequuntur nostrum sequi. Consequuntur,
                                                commodi.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-inverted">
                                    <div class="timeline-badge success"><i class="fa fa-graduation-cap"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Lorem ipsum dolor</h4>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deserunt
                                                obcaecati,
                                                quaerat tempore officia voluptas debitis consectetur culpa amet,
                                                accusamus
                                                dolorum fugiat, animi dicta aperiam, enim incidunt quisquam maxime neque
                                                eaque.</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
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
