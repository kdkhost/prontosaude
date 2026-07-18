<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {

    $dados = array($_POST['titulo'], $_POST['detalhes'], $_POST['status'], $_REQUEST['id']);
    
	if(empty($_POST['titulo'])) {
		$error_message .= 'O Título não pode ser vazio!!!<br>';
	}

	if(empty($_POST['detalhes'])) {
		$error_message .= 'Detalhes não pode ser vazio!<br>';
	}
	
    if($_POST <> NULL){
        $statement = $pdo->prepare("UPDATE tbl_termos SET titulo = ?, detalhes = ?,status = ? WHERE id = ?");
		$statement->execute($dados);
		

		$success_message = 'Termos Atualizados com Sucesso!';
    }

}
?>

<?php

   if(isset($_POST["UpdateBanner"])){
        $path = $_FILES['banner']['name'];
        $path_tmp = $_FILES['banner']['tmp_name'];
        $ext = pathinfo( $path, PATHINFO_EXTENSION );

    if($path == '') {
        $file_name = basename( $path_tmp, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $_SESSION['erro'] = 'Você deve ter que fazer upload de jpg, jpeg, gif ou png arquivo para banner<br>';
             header('location: termos.php');
        }
           
    }
    
    if($path != '') {
        
            $statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE id=?");
    		$statement->execute(array($_REQUEST['id']));
    		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
    		foreach($result as $row) {
    			$banner         = $row['banner'];
    		}
    		
    		
    		    unlink('../assets/uploads/termos/' . $banner);

			$final_name = 'termos-' . $row['id'] . '.' . $ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/termos/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_termos SET banner=? WHERE id=?");
    		$statement->execute(array($final_name,$row['id']));
    		
    		$_SESSION['sucesso'] = 'O Banner foi atualizado com sucesso!';
        header('location: termos.php');
		}
        
        
   }


if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Termos</h1>
	</div>
	<div class="content-header-right">
		<a href="termos.php" class="btn btn-primary btn-sm">Visualizar Todos</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$name              = $row['titulo'];
	$description       = $row['detalhes'];
}
?>

<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?php echo $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="titulo" value="<?php echo $name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Detalhes <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="3" rows="auto" class="form-control" name="detalhes"><?php echo $description; ?></textarea>
							</div>
						</div>
						<div class="form-group">
						    <label for="status" class="col-sm-2 control-label">Status</label>
						    <div class="col-sm-4">
						        <input type="radio" <?php if($row['status'] == 0){ echo "checked"; } ?> name="status" value="0" /> Bloqueado<br>
						        <input type="radio" <?php if($row['status'] == 1){ echo "checked"; } ?> name="status" value="1" /> Ativo
						    </div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">
								    Atualizar
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>