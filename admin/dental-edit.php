<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id_den'])) {
	header('location: logout.php');
	exit;
} else {
	// Checar se o ID é válido
	$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
	$statement->execute(array($_REQUEST['id_den']));
	$total = $statement->rowCount();
	$current_dental = $statement->fetch(PDO::FETCH_ASSOC);
	if($total == 0) {
		header('location: logout.php');
		exit;
	}
}

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
	$final_name = $current_dental['foto'];

    if($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Você deve enviar um arquivo válido (JPG, PNG, WEBP ou GIF) para a foto.<br>';
        } else {
			$final_name = 'dental-'.$_REQUEST['id_den'].'-'.time().'.'.$ext;
			if(move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name)) {
				if(!empty($current_dental['foto']) && file_exists('../assets/uploads/'.$current_dental['foto'])) {
					@unlink('../assets/uploads/'.$current_dental['foto']);
				}
			} else {
				$valid = 0;
				$error_message .= 'Falha ao salvar a nova foto.<br>';
			}
		}
    }

	if($valid == 1) {
		$statement = $pdo->prepare("UPDATE tbl_dental SET 
			servico = ?,
			valor = ?,
			link = ?,
			button_text = ?,
			desc_total = ?,
			descricao = ?,
			beneficios = ?,
			foto = ?
			WHERE id_den = ?");

		$statement->execute([
			$servico,
			$valor,
			$link,
			$button_text,
			$desc_total,
			$descricao,
			$beneficios,
			$final_name,
			$_REQUEST['id_den']
		]);

		$success_message = 'Serviço odontológico e card atualizados com sucesso!';

		// Recarrega dados atualizados
		$statement = $pdo->prepare("SELECT * FROM tbl_dental WHERE id_den=?");
		$statement->execute(array($_REQUEST['id_den']));
		$current_dental = $statement->fetch(PDO::FETCH_ASSOC);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Serviço Odontológico: <?= htmlspecialchars($current_dental['servico']); ?></h1>
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
								<input type="text" autocomplete="off" class="form-control" name="servico" value="<?= htmlspecialchars($current_dental['servico']); ?>" required>
							</div>
						</div>

						<!-- Preço / Valor -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Valor no Card</label>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="valor" placeholder="Ex: 150,00 ou Sob Avaliação" value="<?= htmlspecialchars($current_dental['valor'] ?? ''); ?>">
								</div>
								<small class="text-muted">Valor em destaque exibido no card do procedimento odontológico.</small>
							</div>
						</div>

						<!-- Descrição Curta (Card) -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Curta (Card) <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="descricao" rows="3" required><?= htmlspecialchars($current_dental['descricao']); ?></textarea>
							</div>
						</div>

						<!-- Benefícios Inclusos -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-sm-8">
								<textarea class="form-control" name="beneficios" rows="4" placeholder="Digite cada benefício em uma linha separada..."><?= htmlspecialchars($current_dental['beneficios'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Botão de Ação e Link -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Texto do Botão</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="button_text" value="<?= htmlspecialchars($current_dental['button_text'] ?? 'Agendar Avaliação'); ?>">
							</div>
						</div>

						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Link de Destino</label>
							<div class="col-sm-8">
								<input type="text" autocomplete="off" class="form-control" name="link" value="<?= htmlspecialchars($current_dental['link']); ?>">
								<small class="text-muted">Link do WhatsApp para agendamento direto com o consultório odontológico.</small>
							</div>
						</div>

						<!-- Descrição Completa -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Completa <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control summernote" name="desc_total"><?= htmlspecialchars($current_dental['desc_total']); ?></textarea>
							</div>
						</div>

						<!-- Foto Atual e Troca -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Foto Atual do Card</label>
							<div class="col-sm-6">
								<div class="p-2 border rounded bg-body-tertiary d-inline-block mb-2 shadow-sm">
									<?php 
										$foto = $current_dental['foto'];
										$foto_src = (!empty($foto) && file_exists('../assets/uploads/'.$foto)) ? BASE_URL.'assets/uploads/'.$foto : BASE_URL.'assets/images/sem-foto.gif';
									?>
									<img src="<?= $foto_src; ?>" alt="Foto Odonto" style="max-height: 140px; max-width: 300px; object-fit: cover; border-radius: 6px;">
								</div>
								<input type="file" name="photo" class="form-control" accept="image/*">
								<small class="text-muted">Envie um novo arquivo caso deseje alterar a foto existente.</small>
							</div>
						</div>
						
						<!-- Botão Atualizar -->
						<div class="form-group mt-4">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Atualizar Informações</button>
								<a href="dental.php" class="btn btn-outline-secondary ms-2">Voltar</a>
							</div>
						</div>

					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>