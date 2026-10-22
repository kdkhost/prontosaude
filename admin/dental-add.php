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

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Serviço Odontológico</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Serviço Odontológico</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="dental.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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
				<p><?= $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?= $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form action="" method="post" enctype="multipart/form-data">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">

						<!-- Nome do Serviço -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Procedimento <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-8">
								<input type="text" autocomplete="off" class="form-control" name="servico" placeholder="Ex: Clareamento Dental a Laser, Implante Dentário, Aparelho Ortodôntico" value="<?= htmlspecialchars($_POST['servico'] ?? ''); ?>" required>
							</div>
						</div>

						<!-- Preço / Valor -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Valor no Card</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="valor" placeholder="Ex: 150,00 ou Sob Avaliação" value="<?= htmlspecialchars($_POST['valor'] ?? ''); ?>">
								</div>
								<small class="text-muted">Valor em destaque exibido no card do procedimento odontológico.</small>
							</div>
						</div>

						<!-- Descrição Curta (Card) -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição Curta (Card) <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-8">
								<textarea class="form-control" name="descricao" rows="3" placeholder="Resumo atrativo para o card do paciente..." required><?= htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Benefícios Inclusos -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<textarea class="form-control" name="beneficios" rows="4" placeholder="Exemplo:
Avaliação clínica e panorâmica digital inclusa
Materiais de alta durabilidade e estética premium
Anestesia computadorizada sem dor
Parcelamento em até 12x sem juros"><?= htmlspecialchars($_POST['beneficios'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Botão de Ação e Link -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Texto do Botão</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="button_text" placeholder="Ex: Agendar Avaliação, Saiba Mais" value="<?= htmlspecialchars($_POST['button_text'] ?? 'Agendar Avaliação'); ?>">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Link de Destino</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<input type="text" autocomplete="off" class="form-control" name="link" placeholder="Ex: https://wa.me/5598988887777?text=Quero%20agendar%20avaliacao ou #" value="<?= htmlspecialchars($_POST['link'] ?? ''); ?>">
								<small class="text-muted">Link do WhatsApp para agendamento direto com o consultório odontológico.</small>
							</div>
						</div>

						<!-- Descrição Completa -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição Completa <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control summernote" name="desc_total"><?= htmlspecialchars($_POST['desc_total'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Foto do Card -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto do Card <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="photo" class="form-control" accept="image/*" required>
								<small class="text-muted">Formatos permitidos: JPG, PNG, WEBP ou GIF.</small>
							</div>
						</div>
						
						<!-- Botão Salvar -->
						<div class="row mb-3 mt-4">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Cadastrar Serviço Odontológico</button>
								<a href="dental.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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