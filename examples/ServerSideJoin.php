<?php
/*
 * Surely, it's another class, your model, wich look after your data.
 * For example purpose, we will use directly pdo with a demo data set
 * on the database datatables_demo (you need to create it). The script
 * will load demo data set
 */
$pdo = new PDO('mysql:host=localhost', 'root', 'admin', array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
$pdo->exec('USE datatables_demo');
$pdo->exec(file_get_contents('datatables_demo_join.sql'));
/* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### */

include '../DataTable.php';
use rOpenDev\DataTablesPHP\DataTable;

$columns = array(
	array('data'=>'first_name', 'title'=>'First Name'),
	array('data'=>'last_name', 'title'=>'Last Name'),
	array('data'=>'email', 'title'=>'Email', 'orderable' => false, 'searchable'=>false),
	array('data'=>'office', 'title'=>'Office'),
	array('data'=>'age', 'title'=>'Age', 'class'=>'right'),
	array('data'=>'sal', 'title'=>'Salary', 'class'=>'right', 'formatter'=>null, 'sql_name' => 'salary', 'sql_table'=>'datatables_demo_join_salary'),
	array('title'=>'Delete', 'formatter'=>function($data){return '[X='.$data['id'].']';}, 'orderable'=>false, 'searchable'=>false)
);

$unsetColumns = array(
	'id' => array('table'=>'datatables_demo')
);

$ajax = $_SERVER["REQUEST_URI"];
$ajax = array(
	'uri' => $_SERVER["REQUEST_URI"],
	'type'=> 'POST'
);
$dataTable = DataTable::instance('oTable');
$dataTable->setColumns($columns)
		  ->setUnsetColumn(array('data'=>'id', 'table'=>'datatables_demo')) // We can use id in a formatter function
          ->setColumnFilterActive()
          ->setServerSide($ajax);


if(isset($_REQUEST['draw'])) {

	$dataTable->setFrom('datatables_demo_join');
	$dataTable->setJoin('datatables_demo_join_salary', array('datatables_demo_join'=>'id', 'datatables_demo_join_salary'=>'id'));

	$queries = $dataTable->generateSQLRequest($_REQUEST);

	$q = $pdo->query($queries['data']);
	if($pdo->errorInfo()[0] != '00000')
		$dataTable->sendFatal($pdo->errorInfo()[0].' - '.$pdo->errorInfo()[2]);
	$data = $q->fetchAll();

	$q = $pdo->query($queries['recordsFiltered']);
	if($pdo->errorInfo()[0] != '00000')
		$dataTable->sendFatal($pdo->errorInfo()[0].' - '.$pdo->errorInfo()[2]);
	$recordsFiltered = $q->fetch()['count'];

	$q = $pdo->query($queries['recordsTotal']);
	if($pdo->errorInfo()[0] != '00000')
		$dataTable->sendFatal($pdo->errorInfo()[0].' - '.$pdo->errorInfo()[2]);
	$recordsTotal = $q->fetch()['count'];

	$dataTable->sendData($data, $recordsFiltered, $recordsTotal);
	exit();//Juste au cas où
}
 // <script src="http://jquery-datatables-column-filter.googlecode.com/svn/trunk/media/js/jquery.dataTables.columnFilter.js"></script>
?>
<html>
	<head>
		<title>Server-Side</title>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1-rc2/jquery.min.js"></script>
		<link href="//cdn.datatables.net/1.10.0-rc.1/css/jquery.dataTables.css" rel="stylesheet">
		<script src="//cdn.datatables.net/1.10.0-rc.1/js/jquery.dataTables.js"></script>
		<script src="https://raw.githubusercontent.com/RobinDev/jquery.dataTables.columnFilter.js/master/jquery.dataTables.columnFilter.js"></script>
		<script>
		$(document).ready(function() {
			<?php echo $dataTable->getJavascript(); ?>
		});
		</script>
	</head>
	<body>

<?php echo $dataTable->getHtml();


function dbv($mixed, $exit = true){
	echo '<pre>'.CHR(10); var_dump($mixed); echo '</pre>'.CHR(10);
	if($exit) exit;
}