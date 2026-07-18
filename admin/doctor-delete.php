<?php require_once('header.php'); ?>

<?php


if(!isset($_POST['del'])) {
	//header('location: logout.php');
	//exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE id=?");
	$statement->execute(array($_POST["id"]));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php

	// Getting photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE id=?");
	$statement->execute(array($_POST["id"]));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$photo  = $row['photo'];
		$banner = $row['banner'];
		$id     = $row['id'];
	}
	
	if(isset($id)){
	    // Delete from tbl_doctor
    	$apagar = $pdo->prepare("DELETE FROM tbl_doctor WHERE id={$id}");
    	$apagar->execute();
    	
    	// Unlink the photo
    	if(empty($photo)) {
    		unlink('../assets/uploads/'.$photo);	
    	}
    
    	if(empty($banner)) {
    		unlink('../assets/uploads/'.$banner);
    	}
    	
    
    	//header('location: doctor.php');
	}
		
		if(isset($id)){
		    // Delete from tbl_doctor
        	$state = $pdo->prepare("DELETE FROM tbl_especialidade WHERE medico=?");
        	$state->execute(array($_POST["id"]));
    	    
    	header('location: doctor.php');
    	
    	$_SESSION['msg'] = "Médico Deletado com Sucesso!";
	}
	
	
?>