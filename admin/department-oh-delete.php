<?php require_once('header.php'); ?>

<?php
if( !isset($_GET['id']) || !isset($_GET['id1']) ) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_department_openning_hour WHERE oh_id=?");
	$statement->execute(array($_GET['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
	
// Delete from tbl_video
$statement = $pdo->prepare("DELETE FROM tbl_department_openning_hour WHERE oh_id=?");
$statement->execute(array($_GET['id']));

header('location: department-edit.php?id='.$_GET['id1']);
$success_message = 'Horário de Funcionamento deletado com Sucesso!';
?>