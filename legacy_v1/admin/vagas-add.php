<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['titulo'])) {
		$valid = 0;
		$error_message .= 'O Serviço não pode ser vazio<br>';
	}
	
	if(empty($_POST['requisitos'])) {
		$valid = 0;
		$error_message .= 'O requisito não pode ser vazio<br>';
	}
	
	if(empty($_POST['local'])) {
		$valid = 0;
		$error_message .= 'O local não pode ser vazio<br>';
	}
	
	if(empty($_POST['carga'])){
	    $valid = 0;
	    $error_message .= 'A carga horária foi definida como à Consultar<br>';
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
    } else {
    	$valid = 0;
        $error_message .= 'Você deve selecionar uma foto para a foto em destaque<br>';
    }

	if($valid == 1) {

		// getting auto increment id
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_trabalhe'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		$final_name = 'vagas-'.$ai_id.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

		$statement = $pdo->prepare("INSERT INTO tbl_trabalhe (titulo, local, carga, status, beneficios, salario, requisitos, foto) VALUES (?,?,?,?,?,?,?,?)");
		$statement->execute(array($_POST['titulo'],$_POST['requisitos'],$_POST['local'],$_POST['carga'],$_POST['status'],$_POST['beneficios'],$_POST['salario'],$final_name));
			
		$success_message = 'Vaga adicionada com sucesso!';

		unset($_POST['titulo']);
		unset($_POST['requisitos']);
		unset($_POST['local']);
		unset($_POST['detalhes']);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar Vagas de Emprego</h1>
	</div>
	<div class="content-header-right">
		<a href="trabalhe-conosco.php" class="btn btn-primary btn-sm">Visualizar todos</a>
	</div>
</section>


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