<?php require_once('header.php'); ?>

<?php
if(isset($_POST['BtnStatus'])){
    $id = $_POST['id'];
    $statement = $pdo->prepare("UPDATE tbl_trabalhe SET status=? WHERE id=?");
    $statement->execute(array($_POST['status'],$id));
    
    $_SESSION['msg'] = 'Status atualizado com Sucesso!!';
    header('location: trabalhe-conosco.php');
    
}

if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['titulo'])) {
		$valid = 0;
		$error_message .= 'O Título não pode ser vazio<br>';
	}

	if(empty($_POST['requisitos'])) {
		$valid = 0;
		$error_message .= 'O requisito não pode ser vazio<br>';
	}
	
	if(empty($_POST['local'])) {
		$valid = 0;
		$error_message .= 'O local não pode ser vazio<br>';
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
			$statement = $pdo->prepare("UPDATE tbl_trabalhe SET titulo=?, local=?, carga=?, status=?, beneficios=?, salario=?, requisitos=? WHERE id=?");
    		$statement->execute(array($_POST['titulo'],$_POST['local'],$_POST['carga'],$_POST['status'],$_POST['beneficios'],$_POST['salario'],$_POST['requisitos'],$_REQUEST['id']));
		}
		if($path != '' && $path1 == '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);

			$final_name = 'vagas-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_trabalhe SET titulo=?, local=?, carga=?, status=?, beneficios=?, salario=?, requisitos=?, foto=? WHERE id=?");
    		$statement->execute(array($_POST['titulo'],$_POST['local'],$_POST['carga'],$_POST['status'],$_POST['beneficios'],$_POST['salario'],$_POST['requisitos'],$final_name,$_REQUEST['id']));
		}
		if($path == '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name1 = 'vagas-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_trabalhe SET titulo=?, local=?, carga=?, status=?, beneficios=?, salario=?, requisitos=? WHERE id=?");
    		$statement->execute(array($_POST['titulo'],$_POST['local'],$_POST['carga'],$_POST['status'],$_POST['beneficios'],$_POST['salario'],$_POST['requisitos'],$_REQUEST['id']));
		}
		if($path != '' && $path1 != '') {

			unlink('../assets/uploads/'.$_POST['current_photo']);
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name = 'vagas-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

			$final_name1 = 'vagas-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

        	$statement = $pdo->prepare("UPDATE tbl_trabalhe SET titulo=?, local=?, carga=?, status=?, beneficios=?, salario=?, requisitos=?, foto=? WHERE id=?");
    		$statement->execute(array($_POST['titulo'],$_POST['local'],$_POST['carga'],$_POST['status'],$_POST['beneficios'],$_POST['salario'],$_POST['requisitos'],$final_name,$_REQUEST['id']));
		}

		$success_message = 'Vaga atualizada com Sucesso!!';
	}
        unset($_POST['form1']);
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe WHERE id=?");
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
		<h1>Editar Serviço Dentário</h1>
	</div>
	<div class="content-header-right">
		<a href="trabalhe-conosco.php" class="btn btn-primary btn-sm">Visualizar Todos</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$name               = $row['titulo'];
	$local              = $row['local'];
	$beneficios         = $row['beneficios'];
	$carga              = $row['carga'];
	$description        = $row['detalhes'];
	$requisitos         = $row['requisitos'];
	$photo              = $row['foto'];
	$status             = $row['status'];
	$salario            = $row['salario'];
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
								<input type="text" autocomplete="off" class="form-control" name="titulo" value="<?= $name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Status da Vaga<span>*</span></label>
							<div class="col-sm-6">
							    <p>
							        <input type="radio" name="status" <?php if($status == 1){ echo "checked";}?> value="1"/> Ativo
                                <input type="radio" name="status" <?php if($status == 0){ echo "checked";}?> value="0"/> Inativo
							    </p>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">local <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="local" value="<?= $local; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Salário <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="salario" value="<?= $salario; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Carga Horária <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="carga" value="<?= $carga; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Requisitos <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="1" class="form-control" name="requisitos" id="1"><?= $requisitos; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Benefícios <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="2" class="form-control" name="beneficios" style="height:140px;"><?= $beneficios; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto Atual</label>
							<div class="col-sm-9" style="padding-top:5px">
								<?php if($photo != ""){?>
								    <img src="<?= BASE_URL; ?>assets/uploads/<?= $photo; ?>" alt="Foto Odonto" style="width:400px;">
								<?php }else{?>
								    <img src="<?= BASE_URL; ?>assets/uploads/vagas/sem-foto.gif" alt="Foto Odonto" style="width:400px;">
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