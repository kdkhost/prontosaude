<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['name'])) {
		$valid = 0;
		$error_message .= 'O nome não pode ficar vazio<br>';
	}

	if(empty($_POST['designation'])) {
		$valid = 0;
		$error_message .= 'A designação não pode ficar vazia<br>';
	}

	if(empty($_POST['company'])) {
		$valid = 0;
		$error_message .= 'O nome da empresa não pode ficar vazio<br>';
	}

	
    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, gif ou png<br>';
        }
    }

    if(empty($_POST['comment'])) {
		$valid = 0;
		$error_message .= 'O Depoimento não pode ficar vazio<br>';
	}

	if($valid == 1) {

		if($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_testimonial SET name=?, designation=?, company=?, comment=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$_POST['designation'],$_POST['company'],$_POST['comment'],$_REQUEST['id']));
		} else {

			unlink('../assets/uploads/'.$_POST['current_photo']);

			$final_name = 'testimonial-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_testimonial SET name=?, designation=?, company=?, photo=?, comment=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$_POST['designation'],$_POST['company'],$final_name,$_POST['comment'],$_REQUEST['id']));
		}	   

	    $success_message = 'O testemunho foi atualizado com sucesso!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_testimonial WHERE id=?");
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
		<h1>Editar Depoimento</h1>
	</div>
	<div class="content-header-right">
		<a href="testimonial.php" class="btn btn-primary btn-sm">Ver tudo</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_testimonial WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$name        = $row['name'];
	$designation = $row['designation'];
	$company     = $row['company'];
	$photo       = $row['photo'];
	$comment     = $row['comment'];
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
				<input type="hidden" name="current_photo" value="<?php echo $photo; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nome <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="name" value="<?php echo $name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Designação <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="designation" value="<?php echo $designation; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Empresa <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="company" value="<?php echo $company; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto existente</label>
							<div class="col-sm-9" style="padding-top:5px">
								<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo; ?>" alt="Slider Photo" style="width:180px;">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto </label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo">
								<p class="text-danger">
								    <tt style="color:#ccc;">
								        (Somente jpg, jpeg, gif e png são permitidos)
								    </tt>
								</p>
							</div>
						</div>						
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Depoimento <span>*</span></label>
							<div class="col-sm-7">
								<textarea id="1" class="form-control" name="comment" style="height:140px;"><?php echo $comment; ?></textarea>
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