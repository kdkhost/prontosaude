<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['ide'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_especialidade WHERE ide=?");
	$statement->execute(array($_REQUEST['ide']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
	
// Delete from tbl_video
$statement = $pdo->prepare("DELETE FROM tbl_especialidade WHERE ide=?");
$statement->execute(array($_REQUEST['ide']));

header('location: extra.php');
?>