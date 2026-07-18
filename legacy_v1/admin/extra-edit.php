<?php require_once('header.php'); ?>

<?php							

$compare = $pdo->prepare("SELECT * FROM tbl_doctor AS m 
                                INNER JOIN tbl_designation AS f 
                                INNER JOIN tbl_especialidade AS e 
                                INNER JOIN tbl_department_openning_hour AS d 
                          WHERE m.id = e.medico AND d.oh_id = e.semana AND f.designation_id = e.funcao AND e.ide = ?");
$compare->execute(array($_REQUEST['ide']));
	            	$resultado = $compare->fetchAll(PDO::FETCH_ASSOC);
	            	foreach ($resultado as $funcao){
	            	    
	            	}
?>


<?php
if(isset($_POST['form1'])) {
	$valid = 1;
	
	if(empty($_POST['inicio'])) {
        $valid = 0;
        $error_message .= "Hora de Início não pode ser Vazia!<br>";
    }

    if(empty($_POST['fim'])) {
        $valid = 0;
        $error_message .= "Hora de Fim não pode ser Vazia!<br>";
    }
        
    if($valid == 1) {
    	// updating into the database
		$data = $pdo->prepare("UPDATE tbl_especialidade SET inicio=?, fim=?, semana=?, funcao=?, clinica=? WHERE ide={$_REQUEST['ide']}");
		$data->execute(array($_POST['inicio'], $_POST['fim'], $_POST['semana'], $_POST['funcao'], $_POST['clinica']));
    	    	
    	$success_message = 'A Função de <b>' . $funcao['name'] . '</b> foi Atualizada com Sucesso.';
    }
}
?>

<?php
if(!isset($_REQUEST['ide'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_especialidade WHERE ide=?");
	$statement->execute(array($_REQUEST['ide']));
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
		<h1>Editar Especialidade de <?php if($funcao['medico'] == $funcao['id']){ echo $funcao['name'];} ?></h1>
	</div>
	<div class="content-header-right">
		<a href="extra.php" class="btn btn-primary btn-sm">Visualizar Todas</a>
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

			<form class="form-horizontal" action="" method="post">

				<div class="box box-info">
					<div class="box-body">
					    <div class="form-group">
							<label for="" class="col col-sm-3 control-label">Início de Atendimento <span>*</span></label>
							<div class="col col-sm-2">
								<input type="time" class="form-control" name="inicio" value="<?=$funcao['inicio']; ?>">
							</div>
							<label for="" class="col col-sm-3 control-label">Término de Atendimento <span>*</span></label>
							<div class="col col-sm-2">
								<input type="time" class="form-control" name="fim" value="<?=$funcao['fim']; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col col-sm-3 control-label">Dia da Semana <span>*</span></label>
							<div class="col col-sm-7">
								<select class="form-control select2" name="semana">
									<?php
									$eth = $pdo->prepare("SELECT * FROM tbl_department_openning_hour ORDER BY oh_id ASC");
									$eth->execute();
									$resy = $eth->fetchAll();							
									foreach ($resy as $r) {
									foreach ($resultado as $funcao){?>
										<option value="<?=$r['oh_id']?>" <?php if($r['oh_id'] == $funcao['semana']){echo 'selected';} ?>><?=$r['oh_day']?></option>
									<?php }}?>
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col col-sm-3 control-label">Especialidade <span>*</span></label>
							<div class="col col-sm-7">
								<select class="form-control select2" name="funcao">
									<?php
									$statementy = $pdo->prepare("SELECT * FROM tbl_designation ORDER BY designation_name ASC");
									$statementy->execute();
									$resulty = $statementy->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($resulty as $rows) { ?>
										<option value="<?=$rows['designation_id']?>" <?php if($rows['designation_id'] == $funcao['funcao']){echo 'selected';} ?>><?=$rows['designation_name']?></option>
									<?php }?>
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col col-sm-3 control-label">Clínica <span>*</span></label>
							<div class="col col-sm-7">
								<select class="form-control select2" name="clinica">
									<?php
									$statementy = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
									$statementy->execute();
									$resulty = $statementy->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($resulty as $rows) { ?>
										<option value="<?=$rows['dep_id']?>" <?php if($rows['dep_id'] == $funcao['clinica']){echo 'selected';} ?>><?=$rows['dep_name']?></option>
									<?php }?>
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col col-sm-2 control-label"></label>
							<div class="col col-sm-6">
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