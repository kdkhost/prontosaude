<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php
	// Delete from tbl_pricing_plan
	$statement = $pdo->prepare("DELETE FROM tbl_pricing_plan WHERE pricing_plan_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_pricing_item
	$statement = $pdo->prepare("DELETE FROM tbl_pricing_item WHERE pricing_plan_id=?");
	$statement->execute(array($_REQUEST['id']));

	header('location: pricing-plan.php');
?>