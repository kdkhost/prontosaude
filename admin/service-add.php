<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	$name = trim($_POST['name'] ?? '');
	$price = trim($_POST['price'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$short_description = trim($_POST['short_description'] ?? '');
	$benefits = trim($_POST['benefits'] ?? '');
	$button_text = trim($_POST['button_text'] ?? 'Agendar Consulta');
	$button_url = trim($_POST['button_url'] ?? '');

	if(empty($name)) {
		$valid = 0;
		$error_message .= 'O nome do serviço é obrigatório.<br>';
	}

	if(empty($description)) {
		$valid = 0;
		$error_message .= 'A descrição detalhada é obrigatória.<br>';
	}

	if(empty($short_description)) {
		$valid = 0;
		$error_message .= 'A descrição curta do card é obrigatória.<br>';
	}

	$path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';

    if($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Envie um arquivo válido de imagem (JPG, PNG, WEBP ou GIF) para a foto de destaque.<br>';
        }
    } else {
    	$valid = 0;
        $error_message .= 'A foto de destaque do serviço é obrigatória.<br>';
    }

    $path1 = $_FILES['banner']['name'] ?? '';
    $path_tmp1 = $_FILES['banner']['tmp_name'] ?? '';

    if($path1 != '') {
        $ext1 = strtolower(pathinfo($path1, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext1, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Envie um arquivo válido de imagem (JPG, PNG, WEBP ou GIF) para o banner.<br>';
        }
    } else {
    	$valid = 0;
        $error_message .= 'O banner superior do serviço é obrigatório.<br>';
    }

	if($valid == 1) {
		// Obter próximo ID
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_service'");
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		$ai_id = $result['Auto_increment'] ?? time();

		$slug_input = trim($_POST['slug'] ?? '');
		if($slug_input == '') {
    		$temp_string = strtolower($name);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($slug_input);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// Checar se o slug já existe
		$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=?");
		$statement->execute(array($slug));
		$total = $statement->rowCount();
		if($total) {
			$slug = $slug.'-'.time();
		}

		$final_name = 'service-'.$ai_id.'-'.time().'.'.$ext;
        move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);

        $final_name1 = 'service-banner-'.$ai_id.'-'.time().'.'.$ext1;
        move_uploaded_file($path_tmp1, '../assets/uploads/'.$final_name1);

		$statement = $pdo->prepare("INSERT INTO tbl_service (
			name,
			price,
			slug,
			description,
			short_description,
			benefits,
			button_text,
			button_url,
			photo,
			banner
		) VALUES (?,?,?,?,?,?,?,?,?,?)");

		$statement->execute([
			$name,
			$price,
			$slug,
			$description,
			$short_description,
			$benefits,
			$button_text,
			$button_url,
			$final_name,
			$final_name1
		]);
			
		$success_message = 'Serviço e card configurados e publicados com sucesso!';

		unset($_POST['name']);
		unset($_POST['price']);
		unset($_POST['slug']);
		unset($_POST['description']);
		unset($_POST['short_description']);
		unset($_POST['benefits']);
		unset($_POST['button_text']);
		unset($_POST['button_url']);
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Serviço Médico</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Serviço Médico</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="service.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Serviço <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-8">
								<input type="text" autocomplete="off" class="form-control" name="name" placeholder="Ex: Consulta Cardiológica, Ultrassonografia, Exame de Sangue" value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required>
							</div>
						</div>

						<!-- Valor / Preço do Serviço -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Preço / Valor no Card</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="price" placeholder="Ex: 120,00 ou Sob Consulta" value="<?= htmlspecialchars($_POST['price'] ?? ''); ?>">
								</div>
								<small class="text-muted">Exibido em destaque no card do serviço na página inicial e catálogo.</small>
							</div>
						</div>

						<!-- Slug Automático -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<input type="text" autocomplete="off" class="form-control ps-slug-locked" name="slug" value="<?= htmlspecialchars($_POST['slug'] ?? ''); ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>

						<!-- Descrição Curta (Exibida no Card) -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição Curta (Card) <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-8">
								<textarea class="form-control" name="short_description" rows="3" placeholder="Resumo atrativo exibido no card do serviço..." required><?= htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Benefícios Inclusos -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<textarea class="form-control" name="benefits" rows="4" placeholder="Exemplo:
Consulta com especialista qualificado
Retorno garantido em até 15 dias
Prontuário e laudo 100% digital
Atendimento com hora marcada"><?= htmlspecialchars($_POST['benefits'] ?? ''); ?></textarea>
								<small class="text-muted">Itens com ícone de confirmação exibidos no card ou página de detalhes.</small>
							</div>
						</div>

						<!-- Botão de Ação e Link -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Texto do Botão do Card</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="button_text" placeholder="Ex: Agendar Consulta, Saiba Mais, Ver Detalhes" value="<?= htmlspecialchars($_POST['button_text'] ?? 'Agendar Consulta'); ?>">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL / Destino do Botão</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<input type="text" class="form-control" name="button_url" placeholder="Ex: https://wa.me/5598988887777?text=Quero%20agendar%20este%20serviço (Deixe vazio para usar a página do serviço)" value="<?= htmlspecialchars($_POST['button_url'] ?? ''); ?>">
								<small class="text-muted">Se preenchido, o botão levará direto para o WhatsApp ou página de contratação.</small>
							</div>
						</div>

						<!-- Descrição Completa -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição Completa <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control summernote" name="description"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
							</div>
						</div>

						<!-- Foto de Destaque -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto do Card <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="photo" class="form-control" accept="image/*" required>
								<small class="text-muted">Imagem que estampa o card do serviço (formato recomendado: 600x400px).</small>
							</div>
						</div>

						<!-- Banner do Serviço -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner da Página <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="banner" class="form-control" accept="image/*" required>
								<small class="text-muted">Imagem panorâmica do topo da página do serviço (ex: 1920x400px).</small>
							</div>
						</div>		

						<!-- Botão de Salvar -->
						<div class="row mb-3 mt-4">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Cadastrar Serviço</button>
								<a href="service.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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