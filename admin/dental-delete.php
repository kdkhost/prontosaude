<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id_den'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
	$statement->execute(array($_REQUEST['id_den']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php

	// Getting photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
	$statement->execute(array($_REQUEST['id_den']));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$photo = $row['foto'];
	}

	// Unlink the photo
	if($photo!='') {
		unlink('../assets/uploads/'.$photo);	
	}

	// Delete from tbl_service
	$statement = $pdo->prepare("DELETE FROM tbl_dental WHERE id_den=?");
	$statement->execute(array($_REQUEST['id_den']));

	header('location: dental.php');
?>