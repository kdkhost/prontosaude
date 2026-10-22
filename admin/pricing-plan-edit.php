<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Verifica se o ID do plano existe
	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$current_plan = $statement->fetch(PDO::FETCH_ASSOC);
	if($total == 0) {
		header('location: logout.php');
		exit;
	}
}

if(isset($_POST['form1'])) {
	$valid = 1;

    $name = trim($_POST['pricing_plan_name'] ?? '');
    $price = trim($_POST['pricing_plan_price'] ?? '');
    $period = trim($_POST['pricing_plan_period'] ?? '/mês');
    $badge = trim($_POST['pricing_plan_badge'] ?? '');
    $popular = isset($_POST['pricing_plan_popular']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    $button_text = trim($_POST['pricing_plan_button_text'] ?? 'Contratar Agora');
    $button_url = trim($_POST['pricing_plan_button_url'] ?? '');
    $benefits_text = trim($_POST['pricing_plan_benefits'] ?? '');

    if(empty($name)) {
        $valid = 0;
        $error_message .= "O nome do plano é obrigatório.<br>";
    } else {
    	// Checagem de duplicidade com outros planos
		$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_name=? AND pricing_plan_id!=?");
    	$statement->execute(array($name, $_REQUEST['id']));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'Já existe outro plano com este nome cadastrado.<br>';
    	}
    }

    if(empty($price)) {
        $valid = 0;
        $error_message .= "O valor do plano é obrigatório.<br>";
    }

    // Processamento de Upload da Nova Imagem (se enviada)
    $final_photo = $current_plan['pricing_plan_photo'] ?? '';
    if(isset($_FILES['pricing_plan_photo']) && !empty($_FILES['pricing_plan_photo']['name'])) {
        $path = $_FILES['pricing_plan_photo']['name'];
        $path_tmp = $_FILES['pricing_plan_photo']['tmp_name'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= "Formato de imagem inválido. Envie arquivos JPG, PNG, WEBP ou GIF.<br>";
        } else {
            $final_photo = 'plano-' . $_REQUEST['id'] . '-' . time() . '.' . $ext;
            if(move_uploaded_file($path_tmp, '../assets/uploads/' . $final_photo)) {
                // Remove imagem antiga se existir e não for uma das padrão
                $old_file = $current_plan['pricing_plan_photo'];
                if(!empty($old_file) && !in_array($old_file, ['top-saude.png', 'odonto.png', 'empresarial.png'])) {
                    if(file_exists('../assets/uploads/' . $old_file)) {
                        @unlink('../assets/uploads/' . $old_file);
                    }
                }
            } else {
                $valid = 0;
                $error_message .= "Falha ao enviar a nova imagem do plano.<br>";
            }
        }
    }

    if($valid == 1) {
    	// Atualizando o registro principal do plano
		$statement = $pdo->prepare("UPDATE tbl_pricing_plan SET 
            pricing_plan_name = ?,
            pricing_plan_badge = ?,
            pricing_plan_popular = ?,
            pricing_plan_photo = ?,
            pricing_plan_price = ?,
            pricing_plan_period = ?,
            pricing_plan_button_text = ?,
            pricing_plan_button_url = ?,
            status = ?
            WHERE pricing_plan_id = ?");

		$statement->execute([
            $name,
            $badge,
            $popular,
            $final_photo,
            $price,
            $period,
            $button_text,
            $button_url,
            $status,
            $_REQUEST['id']
        ]);

        // Sincronizando Benefícios do Plano de forma limpa e atômica
        $stmtDel = $pdo->prepare("DELETE FROM tbl_pricing_item WHERE pricing_plan_id = ?");
        $stmtDel->execute([$_REQUEST['id']]);

        if(!empty($benefits_text)) {
            $lines = explode("\n", str_replace("\r", "", $benefits_text));
            $stmtItem = $pdo->prepare("INSERT INTO tbl_pricing_item (pricing_item_name, pricing_plan_id) VALUES (?, ?)");
            foreach($lines as $line) {
                $benefit = trim($line);
                if(!empty($benefit)) {
                    $stmtItem->execute([$benefit, $_REQUEST['id']]);
                }
            }
        }

    	$success_message = 'Plano de saúde e seus benefícios foram atualizados com sucesso!';

        // Recarrega dados atualizados
        $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_id=?");
        $statement->execute(array($_REQUEST['id']));
        $current_plan = $statement->fetch(PDO::FETCH_ASSOC);
    }
}

// Carrega os benefícios existentes para exibição no textarea
$statement_items = $pdo->prepare("SELECT pricing_item_name FROM tbl_pricing_item WHERE pricing_plan_id=? ORDER BY pricing_item_id ASC");
$statement_items->execute([$_REQUEST['id']]);
$current_items = $statement_items->fetchAll(PDO::FETCH_COLUMN);
$current_benefits_str = implode("\n", $current_items);
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Plano de Saúde: <?= htmlspecialchars($current_plan['pricing_plan_name']); ?></h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Plano de Saúde: <?= htmlspecialchars($current_plan['pricing_plan_name']); ?></li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="pricing-plan.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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

                        <!-- Nome do Plano -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Plano <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_name" value="<?= htmlspecialchars($current_plan['pricing_plan_name']); ?>" required>
							</div>
						</div>

                        <!-- Selo / Badge -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Selo de Destaque (Badge)</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_badge" placeholder="Ex: Mais Escolhido, Popular, Melhor Custo-Benefício" value="<?= htmlspecialchars($current_plan['pricing_plan_badge'] ?? ''); ?>">
                                <small class="text-muted">Etiqueta destacada exibida no card do plano.</small>
							</div>
						</div>

                        <!-- Marcar como Popular -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Plano Recomendado / Destaque</label>
                            <div class="col-12 col-sm-9 col-lg-6">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pricing_plan_popular" name="pricing_plan_popular" value="1" <?= (!empty($current_plan['pricing_plan_popular'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pricing_plan_popular">Destacar visualmente este plano com moldura especial no site</label>
                                </div>
                            </div>
                        </div>

                        <!-- Status do Plano / Cartão -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status de Exibição</label>
                            <div class="col-12 col-sm-9 col-lg-6">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" <?= (($current_plan['status'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="status">Plano Ativo (visível no site)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Atual e Troca com Drag & Drop -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Imagem Atual do Card</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<?php 
                                    $photo = $current_plan['pricing_plan_photo'] ?? '';
                                    $photo_src = '';
                                    if(!empty($photo) && file_exists('../assets/uploads/' . $photo)) {
                                        $photo_src = BASE_URL . 'assets/uploads/' . $photo;
                                    } elseif(!empty($photo) && file_exists('../assets/images/planos/' . $photo)) {
                                        $photo_src = BASE_URL . 'assets/images/planos/' . $photo;
                                    } else {
                                        $photo_src = BASE_URL . 'assets/images/sem-foto.gif';
                                    }
                                ?>
                                <div class="p-2 border rounded bg-body-tertiary d-inline-block mb-2 shadow-sm">
                                    <img src="<?= $photo_src; ?>" alt="Foto do Plano" style="max-height: 120px; max-width: 250px; object-fit: contain;">
                                </div>
                                <input type="file" name="pricing_plan_photo" class="form-control" accept="image/*">
								<small class="text-muted d-block mt-1">Selecione ou arraste um novo arquivo para substituir a imagem atual.</small>
							</div>
						</div>

                        <!-- Valor e Período -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Valor / Preço do Plano <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="pricing_plan_price" value="<?= htmlspecialchars($current_plan['pricing_plan_price']); ?>" required>
								</div>
							</div>
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-text">Período</span>
									<input type="text" class="form-control" name="pricing_plan_period" placeholder="Ex: /mês, /ano" value="<?= htmlspecialchars($current_plan['pricing_plan_period'] ?? '/mês'); ?>">
								</div>
							</div>
						</div>

                        <!-- Benefícios Inclusos -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="pricing_plan_benefits" rows="7" placeholder="Digite cada benefício em uma linha separada..."><?= htmlspecialchars($current_benefits_str); ?></textarea>
								<small class="text-muted">Adicione, edite ou remova linhas para ajustar os itens exibidos no card do plano.</small>
							</div>
						</div>

                        <!-- Texto e URL do Botão -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Texto do Botão de Ação</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_button_text" value="<?= htmlspecialchars($current_plan['pricing_plan_button_text'] ?? 'Contratar Agora'); ?>">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Link de Destino do Botão</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_button_url" placeholder="Ex: https://wa.me/5598988887777 ou https://... ou fidelidade.php" value="<?= htmlspecialchars($current_plan['pricing_plan_button_url'] ?? ''); ?>">
								<small class="text-muted">Link externo (ex: WhatsApp, checkout) ou link interno para onde o botão direcionará.</small>
							</div>
						</div>

                        <!-- Botões -->
						<div class="row mb-3 mt-4">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Atualizar Informações do Plano</button>
                                <a href="pricing-plan.php" class="btn btn-outline-secondary ms-2">Voltar</a>
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