<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once "php/includes/dbh.inc.php";

class User
{
    protected $info = [];
    protected $id = 0;
    
    function __construct($id = 0){
        $this->set_id($id);
    }
    function set_id($id){
        if ($this->id == $id) return;
        $this->id = $id;
        $this->info = [];
    }

    function set_info($info){
        unset($info["password"]);
        
        $info["userid"] = $this->id;
        $info["fullname"] = $info["username"];
        
        $this->info = $info; 
    }
    function get_info(){ 
        global $conn;
        
        if (empty($this->info)) {
            $sql = "SELECT * FROM `user` WHERE `id` = " . $this->id;
            $query_rs = mysqli_query($conn, $sql);
            $info = mysqli_fetch_assoc($query_rs);
            
            $this->set_info($info);
        }
        return $this->info; 
    }
    
    function get_value($field_name){
        $this->info = $this->get_info();
        if (empty($this->info[$field_name])) $this->info[$field_name] = null;
        return $this->info[$field_name];
    }
    
    function get_info_all_fields(){
        return $this->get_info();
    }

    function change_info($new_info){
        if ($new_info["id"] != $this->id ) return false;

        $w = "";
        $counter = count($new_info);
        foreach ($new_info as $k=>$v){
            if (is_numeric($k)) continue;
            $w .= " `$k` = " . (is_numeric($v)?$v: "'$v'");
            if ($counter > 1 ) $w .= ", ";
            $counter--;
        }

        $sql = "UPDATE `user` $w WHERE `id` = " . $this->id;
        $query_rs = mysqli_query($conn, $sql);
        $info = mysqli_fetch_assoc($query_rs);
        
        $this->set_info($info);
    }
}

class User_list{
    static $list;
    
    /** Nhan ds don vi tinh **/
    static function get_list(){
        global $conn;

        if (empty(static::$list)) {
            $sql = "SELECT * FROM `user` ";
            $query_rs = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query_rs)) {
                if (isset($row["password"])) unset($row["password"]);
                static::$list[$row["id"]] = $row;
            }
        }
        return static::$list;
    }
    
    static function get_record($id){
        $_tmp = static::get_list();
        if (empty($_tmp[$id])) return null; else return $_tmp[$id]; 
    }
}

?>

<?php
$user_list = User_list::get_list();

//show data
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
                    	Users management
                    </div>
                        
					<div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTable no-footer" id="dataTables-view">
                                <thead>
                                    <th>User name</th>
                                    <th>Fullname</th>
                                </thead>
                                <tbody>
                        	<?php 
                        	    foreach ($user_list as $k=>$v) {
                                    echo "<tr><td>" . $v["username"] . "</td><td>" . $v["fullname"] . "</td></tr>" ;
                                }
                            ?>
                                </tbody>
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
    
    $('#dataTables-view').DataTable({
        responsive: true
    });
})

</script>