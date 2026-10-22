<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	$servico = trim($_POST['servico'] ?? '');
	$valor = trim($_POST['valor'] ?? '');
	$link = trim($_POST['link'] ?? '');
	$button_text = trim($_POST['button_text'] ?? 'Agendar Avaliação');
	$descricao = trim($_POST['descricao'] ?? '');
	$beneficios = trim($_POST['beneficios'] ?? '');
	$desc_total = trim($_POST['desc_total'] ?? '');

	if(empty($servico)) {
		$valid = 0;
		$error_message .= 'O nome do serviço odontológico é obrigatório.<br>';
	}

	if(empty($desc_total)) {
		$valid = 0;
		$error_message .= 'A descrição detalhada é obrigatória.<br>';
	}

	if(empty($descricao)) {
		$valid = 0;
		$error_message .= 'A descrição curta do card é obrigatória.<br>';
	}
	
    $path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';
    $final_name = '';

    if($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Você deve enviar um arquivo válido (JPG, PNG, WEBP ou GIF) para a foto do card.<br>';
        }
    } else {
    	$valid = 0;
        $error_message .= 'A foto de destaque do serviço é obrigatória.<br>';
    }

	if($valid == 1) {
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_dental'");
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		$ai_id = $result['Auto_increment'] ?? time();

		$final_name = 'dental-'.$ai_id.'-'.time().'.'.$ext;
        move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);

		$statement = $pdo->prepare("INSERT INTO tbl_dental (
			servico,
			valor,
			link,
			button_text,
			desc_total,
			descricao,
			beneficios,
			foto
		) VALUES (?,?,?,?,?,?,?,?)");

		$statement->execute([
			$servico,
			$valor,
			$link,
			$button_text,
			$desc_total,
			$descricao,
			$beneficios,
			$final_name
		]);
			
		$success_message = 'Serviço odontológico cadastrado com sucesso!';

		unset($_POST['servico']);
		unset($_POST['valor']);
		unset($_POST['link']);
		unset($_POST['button_text']);
		unset($_POST['descricao']);
		unset($_POST['beneficios']);
		unset($_POST['desc_total']);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar Serviço Odontológico</h1>
	</div>
	<div class="content-header-right">
		<a href="dental.php" class="btn btn-primary btn-sm"><i class="bi bi-list-ul me-1"></i> Visualizar Todos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p><?= $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?= $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">

						<!-- Nome do Serviço -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Procedimento <span>*</span></label>
							<div class="col-sm-8">
								<input type="text" autocomplete="off" class="form-control" name="servico" placeholder="Ex: Clareamento Dental a Laser, Implante Dentário, Aparelho Ortodôntico" value="<?= htmlspecialchars($_POST['servico'] ?? ''); ?>" required>
							</div>
						</div>

						<!-- Preço / Valor -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Valor no Card</label>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="valor" placeholder="Ex: 150,00 ou Sob Avaliação" value="<?= htmlspecialchars($_POST['valor'] ?? ''); ?>">
								</div>
								<small class="text-muted">Valor em destaque exibido no card do procedimento odontológico.</small>
							</div>
						</div>

						<!-- Descrição Curta (Card) -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Curta (Card) <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="descricao" rows="3" placeholder="Resumo atrativo para o card do paciente..." required><?= htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Benefícios Inclusos -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-sm-8">
								<textarea class="form-control" name="beneficios" rows="4" placeholder="Exemplo:
Avaliação clínica e panorâmica digital inclusa
Materiais de alta durabilidade e estética premium
Anestesia computadorizada sem dor
Parcelamento em até 12x sem juros"><?= htmlspecialchars($_POST['beneficios'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Botão de Ação e Link -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Texto do Botão</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="button_text" placeholder="Ex: Agendar Avaliação, Saiba Mais" value="<?= htmlspecialchars($_POST['button_text'] ?? 'Agendar Avaliação'); ?>">
							</div>
						</div>

						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Link de Destino</label>
							<div class="col-sm-8">
								<input type="text" autocomplete="off" class="form-control" name="link" placeholder="Ex: https://wa.me/5598988887777?text=Quero%20agendar%20avaliacao ou #" value="<?= htmlspecialchars($_POST['link'] ?? ''); ?>">
								<small class="text-muted">Link do WhatsApp para agendamento direto com o consultório odontológico.</small>
							</div>
						</div>

						<!-- Descrição Completa -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Completa <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control summernote" name="desc_total"><?= htmlspecialchars($_POST['desc_total'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Foto do Card -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Foto do Card <span>*</span></label>
							<div class="col-sm-6">
								<input type="file" name="photo" class="form-control" accept="image/*" required>
								<small class="text-muted">Formatos permitidos: JPG, PNG, WEBP ou GIF.</small>
							</div>
						</div>
						
						<!-- Botão Salvar -->
						<div class="form-group mt-4">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Cadastrar Serviço Odontológico</button>
								<a href="dental.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
							</div>
						</div>

					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>