<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['ide']) || !is_numeric($_REQUEST['ide'])) {
	header('location: extra.php');
	exit;
}

$ide = (int)$_REQUEST['ide'];

$compare = $pdo->prepare("SELECT * FROM tbl_doctor AS m 
                                INNER JOIN tbl_designation AS f 
                                INNER JOIN tbl_especialidade AS e 
                                INNER JOIN tbl_department_openning_hour AS d 
                          WHERE m.id = e.medico AND d.oh_id = e.semana AND f.designation_id = e.funcao AND e.ide = ?");
$compare->execute(array($ide));
$funcao = $compare->fetch(PDO::FETCH_ASSOC);

if(!$funcao) {
	header('location: extra.php');
	exit;
}

$error_message = '';
$success_message = '';

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
		$data = $pdo->prepare("UPDATE tbl_especialidade SET inicio=?, fim=?, semana=?, funcao=?, clinica=? WHERE ide=?");
		$data->execute(array($_POST['inicio'], $_POST['fim'], $_POST['semana'], $_POST['funcao'], $_POST['clinica'], $ide));
    	    	
    	$success_message = 'A Função de <b>' . htmlspecialchars($funcao['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</b> foi Atualizada com Sucesso.';
		$funcao['inicio'] = $_POST['inicio'];
		$funcao['fim'] = $_POST['fim'];
		$funcao['semana'] = $_POST['semana'];
		$funcao['funcao'] = $_POST['funcao'];
		$funcao['clinica'] = $_POST['clinica'];
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Especialidade de <?php echo htmlspecialchars($funcao['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Especialidade de <?php echo htmlspecialchars($funcao['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="extra.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

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

			<form action="" method="post">

				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
					    <div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Início de Atendimento <span class="text-danger">*</span></label>
							<div class="col col-sm-2">
								<input type="time" class="form-control" name="inicio" value="<?=$funcao['inicio']; ?>">
							</div>
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Término de Atendimento <span class="text-danger">*</span></label>
							<div class="col col-sm-2">
								<input type="time" class="form-control" name="fim" value="<?=$funcao['fim']; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Dia da Semana <span class="text-danger">*</span></label>
							<div class="col col-sm-7">
								<select class="form-control select2" name="semana">
									<?php
									$eth = $pdo->prepare("SELECT * FROM tbl_department_openning_hour ORDER BY oh_id ASC");
									$eth->execute();
									$resy = $eth->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($resy as $r) { ?>
										<option value="<?=$r['oh_id']?>" <?php if($r['oh_id'] == ($funcao['semana'] ?? '')){echo 'selected';} ?>><?=$r['oh_day']?></option>
									<?php }?>
								</select>
							</div>
						</div>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Especialidade <span class="text-danger">*</span></label>
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
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Clínica <span class="text-danger">*</span></label>
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
						
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col col-sm-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
<a href="extra.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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