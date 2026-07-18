<?php require_once('header.php'); ?>

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

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_especialidade(medico,clinica,semana,funcao,inicio,fim) 
		                                VALUES (?,?,?,?,?,?)");
		$statement->execute(array($_POST['medico'], $_POST['clinica'], $_POST['semana'], $_POST['especialidade'], $_POST['inicio'], $_POST['fim']));

    	$success_message = 'Função Atribuida com Sucesso.';
    	
    	unset($_POST['medico']);
    	unset($_POST['semana']);
    	unset($_POST['especialidade']);
    	unset($_POST['inicio']);
    	unset($_POST['fim']);
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar Funções Extras</h1>
	</div>
	<div class="content-header-right">
		<a href="extra.php" class="btn btn-primary btn-sm">Visualizar Todos</a>
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
							<label for="" class="col-sm-3 control-label">Início de Atendimento<span>*</span></label>
							<div class="col-sm-2">
								<input type="time" id="inicio" class="form-control" onkeyup="foco()" maxlength="4" minlength="4" name="inicio" value="<?php if(isset($_POST['inicio'])){echo $_POST['inicio'];} ?>">
							</div>
							<label for="" class="col-sm-3 control-label">Término de Atendimento<span>*</span></label>
							<div class="col-sm-2">
								<input type="time" id="fim" class="form-control" name="fim" value="<?php if(isset($_POST['fim'])){echo $_POST['fim'];} ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Selecione a Clínica<span>*</span></label>
							<div class="col-sm-7">
								<select class="form-control" name="clinica">
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) { ?>
										<option value="<?=$row['dep_id']?>" <?php if($row['dep_id'] == $_POST['clinica']){echo 'selected';} ?>><?=$row['dep_name']?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Selecione o Médico<span>*</span></label>
							<div class="col-sm-7">
								<select class="form-control select2" name="medico">
									<option value="">Selecione o Médico</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_doctor ORDER BY name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) { ?>
										<option value="<?=$row['id']?>" <?php if($row['id'] == $_POST['medico']){echo 'selected';} ?>><?=$row['name']?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Selecione o Dia<span>*</span></label>
							<div class="col-sm-7">
								<select class="form-control select2" name="semana">
									<option value="">Selecione o Dia</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_department_openning_hour ORDER BY oh_id ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) { ?>
									<option value="<?=$row['oh_id'] ?>" <?php if($row['oh_id'] == $_POST['semana']){echo 'selected';} ?>><?=$row['oh_day'] ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Selecione a Função <span>*</span></label>
							<div class="col-sm-7">
								<select class="form-control select2" name="especialidade">
									<option value="">Selecione a Especialidade</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_designation ORDER BY designation_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) { ?>
									<option value="<?=$row['designation_id'] ?>" <?php if($row['designation_id'] == $_POST['especialidade']){echo 'selected';} ?>><?=$row['designation_name'] ?></option>
									<?php } ?>
								</select>
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
	
	<style>
	    a {
        text-decoration: none;
    }

    /* Card Styles */

    .card-sl {
        border-radius: 8px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .card-image img {
        max-height: 100%;
        max-width: 100%;
        border-radius: 8px 8px 0px 0;
    }

    .card-action {
        position: relative;
        float: right;
        margin-top: -25px;
        margin-right: 20px;
        width: 50px;
        z-index: 2;
        color: #E26D5C;
        background: #fff;
        border-radius: 100%;
        padding: 15px;
        font-size: 15px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2), 0 1px 2px 0 rgba(0, 0, 0, 0.19);
    }

    .card-action:hover {
        color: #fff;
        background: #E26D5C;
        -webkit-animation: pulse 1.5s infinite;
    }

    .card-heading {
        font-size: 18px;
        font-weight: bold;
        background: #fff;
        padding: 10px 15px;
    }

    .card-text {
        padding: 10px 15px;
        background: #fff;
        font-size: 14px;
        color: #636262;
    }

    .card-button {
        display: flex;
        justify-content: center;
        padding: 10px 0;
        width: 100%;
        background-color: #1F487E;
        color: #fff;
        border-radius: 0 0 8px 8px;
    }

    .card-button:hover {
        text-decoration: none;
        background-color: #1D3461;
        color: #fff;

    }


    @-webkit-keyframes pulse {
        0% {
            -moz-transform: scale(0.9);
            -ms-transform: scale(0.9);
            -webkit-transform: scale(0.9);
            transform: scale(0.9);
        }

        70% {
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            -webkit-transform: scale(1);
            transform: scale(1);
            box-shadow: 0 0 0 50px rgba(90, 153, 212, 0);
        }

        100% {
            -moz-transform: scale(0.9);
            -ms-transform: scale(0.9);
            -webkit-transform: scale(0.9);
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(90, 153, 212, 0);
        }
    }
	</style>

<!-- Anuncio do programador -->
    <div class="container-fluid" style="margin-top:30px;">
        <div class="row">
            <div class="col-sm-4" style="margin-bottom:10px;">
                <div class="card-sl">
                    <div class="card-image">
                        <img src="../assets/img/marketing/img1.png" />
                    </div>

                    <a class="card-action" href="https://yellowestrategias.com.br">
                        <center>
                            <i class="fa fa-globe"></i>
                        </center>
                    </a>
                    <div class="card-heading">
                        Planejamento
                    </div>
                    <div class="card-text">
                        Serviços de Designer, Marketing, Gerenciamento de Rede Sociais, Hospedagens de Sites, Revendas de Hospedagens,
                        serviços Gráficos, Serviços Digitais e muitos mais
                    </div>
                    <div class="card-text">
                        Orçamento e Serviços
                    </div>
                    <a href="https://yellowestrategias.com.br/contact" class="card-button" style="background:#f88800; color:#fff;"> Contato</a>
                </div>
            </div>
            
            <div class="col-sm-4" style="margin-bottom:10px;">
                <div class="card-sl">
                    <div class="card-image">
                        <img src="../assets/img/marketing/img2.png" />
                    </div>

                    <a class="card-action" href="https://yellowestrategias.com.br">
                        <center>
                            <i class="fa fa-star"></i>
                        </center>
                    </a>
                    <div class="card-heading">
                        Divulgação
                    </div>
                    <div class="card-text">
                        Serviços de Designer, Marketing, Gerenciamento de Rede Sociais, Hospedagens de Sites, Revendas de Hospedagens,
                        serviços Gráficos, Serviços Digitais e muitos mais
                    </div>
                    <div class="card-text">
                        Orçamento e Serviços
                    </div>
                    <a href="https://yellowestrategias.com.br/pricing" class="card-button" style="background:#3e4095; color:#fff;"> Contato</a>
                </div>
            </div>
            
            <div class="col-sm-4" style="margin-bottom:10px;">
                <div class="card-sl">
                    <div class="card-image">
                        <img src="../assets/img/marketing/img3.png" />
                    </div>

                    <a class="card-action" href="https://kdkhost.com.br">
                        <center>
                            <i class="fa fa-heart"></i>
                        </center>
                    </a>
                    <div class="card-heading">
                        Executar Projeto
                    </div>
                    <div class="card-text">
                        Serviços de Designer, Marketing, Gerenciamento de Rede Sociais, Hospedagens de Sites, Revendas de Hospedagens,
                        serviços Gráficos, Serviços Digitais e muitos mais
                    </div>
                    <div class="card-text">
                        Orçamento e Serviços
                    </div>
                    <a href="mailto:site.kdkhost@gmail.com" class="card-button" style="background:#3e4095; color:#fff;"> Contato</a>
                </div>
            </div>
        </div>
    </div>
<!-- Fim do Anuncio do programador -->
</section>

<?php require_once('footer.php'); ?>