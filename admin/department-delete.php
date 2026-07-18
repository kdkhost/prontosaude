<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php

	// Getting photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$photo = $row['dep_photo'];
		$banner = $row['dep_banner'];
	}

	// Unlink the photo
	if($photo!='') {
		unlink('../assets/uploads/'.$photo);
	}
	if($banner!='') {
		unlink('../assets/uploads/'.$banner);
	}

	// Delete from tbl_department
	$statement = $pdo->prepare("DELETE FROM tbl_department WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_department_faq
	$statement = $pdo->prepare("DELETE FROM tbl_department_faq WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_department_openning_hour
	$statement = $pdo->prepare("DELETE FROM tbl_department_openning_hour WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_department_appointment
	$statement = $pdo->prepare("DELETE FROM tbl_department_appointment WHERE dep_id=?");
	$statement->execute(array($_REQUEST['id']));

	header('location: department.php');
?>