<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['servico'])) {
		$valid = 0;
		$error_message .= 'O Serviço não pode ser vazio<br>';
	}
	
	if(empty($_POST['link'])) {
		$valid = 0;
		$error_message .= 'O Link não pode ser vazio<br>';
	}

	if(empty($_POST['desc_total'])) {
		$valid = 0;
		$error_message .= 'Descrição Longa não pode ser vazia<br>';
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
    } else {
    	$valid = 0;
        $error_message .= 'Você deve selecionar uma foto para a foto em destaque<br>';
    }

	if($valid == 1) {

		// getting auto increment id
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_dental'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		$final_name = 'dental-'.$ai_id.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

		$statement = $pdo->prepare("INSERT INTO tbl_dental (servico,link,desc_total,descricao,foto) VALUES (?,?,?,?,?)");
		$statement->execute(array($_POST['servico'],$_POST['link'],$_POST['desc_total'],$_POST['descricao'],$final_name));
			
		$success_message = 'O serviço foi adicionado com sucesso!';

		unset($_POST['servico']);
		unset($_POST['link']);
		unset($_POST['desc_total']);
		unset($_POST['descricao']);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar Serviço Odontológico</h1>
	</div>
	<div class="content-header-right">
		<a href="dental.php" class="btn btn-primary btn-sm">Visualizar todos</a>
	</div>
</section>


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
							<label for="" class="col-sm-2 control-label">Serviço <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="servico" value="<?php if(isset($_POST['servico'])){echo $_POST['servico'];} ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Link </label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="link" value="<?php if(isset($_POST['link'])){echo $_POST['link'];} ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição Longa <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="1" class="form-control" name="desc_total" id="1"><?php if(isset($_POST['desc_total'])){echo $_POST['desc_total'];} ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição Curta <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="2" class="form-control" name="descricao" style="height:140px;"><?php if(isset($_POST['descricao'])){echo $_POST['descricao'];} ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto <span>*</span></label>
							<div class="col-sm-9" style="padding-top:5px">
								<input type="file" name="photo">(Somente jpg, jpeg, gif and png são autorizadas)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Cadastrar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>