<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Checar se o ID é válido
	$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$current_service = $statement->fetch(PDO::FETCH_ASSOC);
	if($total == 0) {
		header('location: logout.php');
		exit;
	}
}

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
	$final_name = $current_service['photo'];

    if($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Envie um arquivo válido de imagem (JPG, PNG, WEBP ou GIF) para a foto de destaque.<br>';
        } else {
			$final_name = 'service-'.$_REQUEST['id'].'-'.time().'.'.$ext;
			if(move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name)) {
				if(!empty($current_service['photo']) && file_exists('../assets/uploads/'.$current_service['photo'])) {
					@unlink('../assets/uploads/'.$current_service['photo']);
				}
			} else {
				$valid = 0;
				$error_message .= 'Falha ao salvar a nova foto de destaque.<br>';
			}
		}
    }

    $path1 = $_FILES['banner']['name'] ?? '';
    $path_tmp1 = $_FILES['banner']['tmp_name'] ?? '';
	$final_name1 = $current_service['banner'];

    if($path1 != '') {
        $ext1 = strtolower(pathinfo($path1, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext1, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Envie um arquivo válido de imagem (JPG, PNG, WEBP ou GIF) para o banner.<br>';
        } else {
			$final_name1 = 'service-banner-'.$_REQUEST['id'].'-'.time().'.'.$ext1;
			if(move_uploaded_file($path_tmp1, '../assets/uploads/'.$final_name1)) {
				if(!empty($current_service['banner']) && file_exists('../assets/uploads/'.$current_service['banner'])) {
					@unlink('../assets/uploads/'.$current_service['banner']);
				}
			} else {
				$valid = 0;
				$error_message .= 'Falha ao salvar o novo banner.<br>';
			}
		}
    }

	if($valid == 1) {
		$slug_input = trim($_POST['slug'] ?? '');
		if($slug_input == '') {
    		$temp_string = strtolower($name);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($slug_input);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// Checar se o slug já existe para outro serviço
		$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=? AND id!=?");
		$statement->execute(array($slug, $_REQUEST['id']));
		$total = $statement->rowCount();
		if($total) {
			$slug = $slug.'-'.$_REQUEST['id'];
		}

		$statement = $pdo->prepare("UPDATE tbl_service SET 
			name = ?,
			price = ?,
			slug = ?,
			description = ?,
			short_description = ?,
			benefits = ?,
			button_text = ?,
			button_url = ?,
			photo = ?,
			banner = ?
			WHERE id = ?");

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
			$final_name1,
			$_REQUEST['id']
		]);

		$success_message = 'Serviço e card atualizados com sucesso!';

		// Recarrega dados atualizados
		$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE id=?");
		$statement->execute(array($_REQUEST['id']));
		$current_service = $statement->fetch(PDO::FETCH_ASSOC);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Serviço: <?= htmlspecialchars($current_service['name']); ?></h1>
	</div>
	<div class="content-header-right">
		<a href="service.php" class="btn btn-primary btn-sm"><i class="bi bi-list-ul me-1"></i> Visualizar Todos</a>
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
				<input type="hidden" name="current_photo" value="<?= htmlspecialchars($current_service['photo']); ?>">
				<input type="hidden" name="current_banner" value="<?= htmlspecialchars($current_service['banner']); ?>">

				<div class="box box-info">
					<div class="box-body">

						<!-- Nome -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Nome do Serviço <span>*</span></label>
							<div class="col-sm-8">
								<input type="text" autocomplete="off" class="form-control" name="name" value="<?= htmlspecialchars($current_service['name']); ?>" required>
							</div>
						</div>

						<!-- Valor / Preço -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Preço / Valor no Card</label>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="price" placeholder="Ex: 120,00 ou Sob Consulta" value="<?= htmlspecialchars($current_service['price'] ?? ''); ?>">
								</div>
								<small class="text-muted">Valor em destaque exibido no card do serviço.</small>
							</div>
						</div>

						<!-- Slug -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Slug </label>
							<div class="col-sm-8">
								<input type="text" autocomplete="off" class="form-control ps-slug-locked" name="slug" value="<?= htmlspecialchars($current_service['slug']); ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>

						<!-- Descrição Curta (Card) -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Curta (Card) <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="short_description" rows="3" required><?= htmlspecialchars($current_service['short_description']); ?></textarea>
							</div>
						</div>

						<!-- Benefícios Inclusos -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-sm-8">
								<textarea class="form-control" name="benefits" rows="4" placeholder="Digite cada benefício em uma linha separada..."><?= htmlspecialchars($current_service['benefits'] ?? ''); ?></textarea>
								<small class="text-muted">Itens com ícone de confirmação exibidos no card ou página.</small>
							</div>
						</div>

						<!-- Texto e Link do Botão -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Texto do Botão do Card</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="button_text" value="<?= htmlspecialchars($current_service['button_text'] ?? 'Agendar Consulta'); ?>">
							</div>
						</div>

						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">URL / Destino do Botão</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="button_url" placeholder="Ex: https://wa.me/5598988887777 (Deixe vazio para link interno do serviço)" value="<?= htmlspecialchars($current_service['button_url'] ?? ''); ?>">
								<small class="text-muted">Link externo ou WhatsApp para agendamento direto pelo card.</small>
							</div>
						</div>

						<!-- Descrição Completa -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Descrição Completa <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control summernote" name="description"><?= htmlspecialchars($current_service['description']); ?></textarea>
							</div>
						</div>

						<!-- Foto Atual e Troca -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Foto Atual do Card</label>
							<div class="col-sm-6">
								<div class="p-2 border rounded bg-body-tertiary d-inline-block mb-2 shadow-sm">
									<img src="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($current_service['photo']); ?>" alt="Foto do Serviço" style="max-height: 140px; max-width: 300px; object-fit: cover; border-radius: 6px;">
								</div>
								<input type="file" name="photo" class="form-control" accept="image/*">
								<small class="text-muted">Envie um novo arquivo caso deseje alterar a foto existente.</small>
							</div>
						</div>

						<!-- Banner Atual e Troca -->
						<div class="form-group mb-3">
							<label class="col-sm-2 control-label fw-bold">Banner Atual da Página</label>
							<div class="col-sm-6">
								<div class="p-2 border rounded bg-body-tertiary d-inline-block mb-2 shadow-sm">
									<img src="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($current_service['banner']); ?>" alt="Banner do Serviço" style="max-height: 140px; max-width: 300px; object-fit: cover; border-radius: 6px;">
								</div>
								<input type="file" name="banner" class="form-control" accept="image/*">
								<small class="text-muted">Envie um novo arquivo caso deseje alterar o banner superior.</small>
							</div>
						</div>		

						<!-- Botão de Salvar -->
						<div class="form-group mt-4">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Atualizar Informações do Serviço</button>
								<a href="service.php" class="btn btn-outline-secondary ms-2">Voltar</a>
							</div>
						</div>

					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>