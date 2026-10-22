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

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Termos</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Termos</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="termos.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$name              = $row['titulo'];
	$description       = $row['detalhes'];
}
?>

<div class="app-content">
<div class="container-fluid">

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

			<form action="" method="post" enctype="multipart/form-data">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="titulo" value="<?php echo $name; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Detalhes <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea id="3" rows="auto" class="form-control" name="detalhes"><?php echo $description; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
						    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status</label>
						    <div class="col-12 col-sm-9 col-lg-6">
						        <input type="radio" <?php if($row['status'] == 0){ echo "checked"; } ?> name="status" value="0" /> Bloqueado<br>
						        <input type="radio" <?php if($row['status'] == 1){ echo "checked"; } ?> name="status" value="1" /> Ativo
						    </div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
<a href="termos.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>


</div>
</div>

<?php require_once('footer.php'); ?>