<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_item_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}

    


if(isset($_POST['del'])){
    
    // Delete from tbl_video
    $statement = $pdo->prepare("DELETE FROM tbl_pricing_item WHERE pricing_item_id=?");
    $statement->execute(array($_POST['id']));
    
    header('location: pricing-item.php');
    $_SESSION['msg'] = "Item Deletado com Sucesso!";
}
?>