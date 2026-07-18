<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['servico'])) {
		$valid = 0;
		$error_message .= 'Serviço não pode ser vazio<br>';
	}

	if(empty($_POST['desc_total'])) {
		$valid = 0;
		$error_message .= 'A Descrição Longa não pode ser vazia<br>';
	}
	
	if(empty($_POST['link'])) {
		$valid = 0;
		$error_message .= 'O Link não pode ser vazio<br>';
	}

	if(empty($_POST['descricao'])) {
		$valid = 0;
		$error_message .= 'A Descrição curta não pode ser vazia<br>';
	}
	
    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, gif ou png para a foto em destaque<br>';
        }
    }

    $path1 = $_FILES['banner']['name'];
    $path_tmp1 = $_FILES['banner']['tmp_name'];

    if($path1!='') {
        $ext1 = pathinfo( $path1, PATHINFO_EXTENSION );
        $file_name1 = basename( $path1, '.' . $ext1 );
        if( $ext1!='jpg' && $ext1!='png' && $ext1!='jpeg' && $ext1!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload do arquivo jpg, jpeg, gif ou png para Banner<br>';
        }
    }

		if($path == '' && $path1 == '') {
			$statement = $pdo->prepare("UPDATE tbl_dental SET servico=?, link=?, descricao=?, desc_total=? WHERE id_den=?");
    		$statement->execute(array($_POST['servico'],$_POST['link'],$_POST['descricao'],$_POST['desc_total'],$_REQUEST['id_den']));
		}
		if($path != '' && $path1 == '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);

			$final_name = 'dental-'.$_REQUEST['id_den'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_dental SET servico=?, link=?, descricao=?, desc_total=?, foto=? WHERE id_den=?");
    		$statement->execute(array($_POST['servico'],$_POST['link'],$_POST['descricao'],$_POST['desc_total'],$final_name,$_REQUEST['id_den']));
		}
		if($path == '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name1 = 'dental-banner-'.$_REQUEST['id_den'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_dental SET servico=?, link=?, descricao=?, desc_total=? WHERE id_den=?");
    		$statement->execute(array($_POST['servico'],$_POST['link'],$_POST['descricao'],$_POST['desc_total'],$_REQUEST['id_den']));
		}
		if($path != '' && $path1 != '') {

			unlink('../assets/uploads/'.$_POST['current_photo']);
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name = 'dental-'.$_REQUEST['id_den'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

			$final_name1 = 'dental-banner-'.$_REQUEST['id_den'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_dental SET servico=?, link=?, descricao=?, desc_total=?, foto=? WHERE id_den=?");
    		$statement->execute(array($_POST['servico'],$_POST['link'],$_POST['descricao'],$_POST['desc_total'],$final_name,$_REQUEST['id_den']));
		}

		$success_message = 'Serviço Adicionado com Sucesso!!';
	}

?>

<?php
if(!isset($_REQUEST['id_den'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
	$statement->execute(array($_REQUEST['id_den']));
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
		<h1>Editar Serviço Dentário</h1>
	</div>
	<div class="content-header-right">
		<a href="dental.php" class="btn btn-primary btn-sm">Visualizar Todos</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
$statement->execute(array($_REQUEST['id_den']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$name               = $row['servico'];
	$link               = $row['link'];
	$description        = $row['descricao'];
	$desc_total         = $row['desc_total'];
	$photo              = $row['foto'];
}
?>

<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?= $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?= $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="current_photo" value="<?= $photo; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Serviço <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="servico" value="<?= $name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Link <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="link" value="<?= $link; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição Longa <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="1" class="form-control" name="desc_total" id="1"><?= $desc_total; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição Curta <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="2" class="form-control" name="descricao" style="height:140px;"><?= $description; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto Atual</label>
							<div class="col-sm-9" style="padding-top:5px">
								<?php if($photo != ""){?>
								    <img src="<?= BASE_URL; ?>assets/uploads/<?= $photo; ?>" alt="Foto Odonto" style="width:400px;">
								<?php }else{?>
								    <img src="<?= BASE_URL; ?>assets/uploads/dental/sem-foto.gif" alt="Foto Odonto" style="width:400px;">
								<?php } ?>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto </label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo">(Somente jpg, jpeg, gif and png are allowed)
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Atualizar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>