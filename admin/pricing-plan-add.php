<?php require_once('header.php'); ?>

<?php
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
    	// Checagem de duplicidade
    	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_name=?");
    	$statement->execute(array($name));
    	$total = $statement->rowCount();
    	if($total) {
    		$valid = 0;
        	$error_message .= "Já existe um plano com este nome cadastrado.<br>";
    	}
    }

    if(empty($price)) {
        $valid = 0;
        $error_message .= "O valor/preço do plano é obrigatório.<br>";
    }

    // Processamento de Upload da Foto do Plano
    $final_photo = '';
    if(isset($_FILES['pricing_plan_photo']) && !empty($_FILES['pricing_plan_photo']['name'])) {
        $path = $_FILES['pricing_plan_photo']['name'];
        $path_tmp = $_FILES['pricing_plan_photo']['tmp_name'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if(!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= "Formato de imagem inválido. Envie arquivos JPG, PNG, WEBP ou GIF.<br>";
        } else {
            $final_photo = 'plano-' . time() . '-' . rand(100, 999) . '.' . $ext;
            if(!move_uploaded_file($path_tmp, '../assets/uploads/' . $final_photo)) {
                $valid = 0;
                $error_message .= "Falha ao enviar a imagem do plano para o servidor.<br>";
            }
        }
    }

    if($valid == 1) {
		// Salvando o Plano no Banco de Dados
		$statement = $pdo->prepare("INSERT INTO tbl_pricing_plan (
            pricing_plan_name,
            pricing_plan_badge,
            pricing_plan_popular,
            pricing_plan_photo,
            pricing_plan_price,
            pricing_plan_period,
            pricing_plan_button_text,
            pricing_plan_button_url,
            status
        ) VALUES (?,?,?,?,?,?,?,?,?)");

		$statement->execute([
            $name,
            $badge,
            $popular,
            $final_photo,
            $price,
            $period,
            $button_text,
            $button_url,
            $status
        ]);

        $new_plan_id = $pdo->lastInsertId();

        // Processamento dos Benefícios (um benefício por linha)
        if(!empty($benefits_text) && $new_plan_id) {
            $lines = explode("\n", str_replace("\r", "", $benefits_text));
            $stmtItem = $pdo->prepare("INSERT INTO tbl_pricing_item (pricing_item_name, pricing_plan_id) VALUES (?, ?)");
            foreach($lines as $line) {
                $benefit = trim($line);
                if(!empty($benefit)) {
                    $stmtItem->execute([$benefit, $new_plan_id]);
                }
            }
        }

    	$success_message = 'Plano de saúde e seus benefícios foram cadastrados com sucesso!';
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Plano de Saúde / Preço</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Plano de Saúde / Preço</li>
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
								<input type="text" class="form-control" name="pricing_plan_name" placeholder="Ex: Top Saúde Individual, Plano Familiar, Empresarial" value="<?= htmlspecialchars($_POST['pricing_plan_name'] ?? ''); ?>" required>
							</div>
						</div>

                        <!-- Selo / Badge de Destaque -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Selo de Destaque (Badge)</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_badge" placeholder="Ex: Mais Escolhido, Popular, Melhor Custo-Benefício" value="<?= htmlspecialchars($_POST['pricing_plan_badge'] ?? ''); ?>">
                                <small class="text-muted">Exibe uma etiqueta colorida no topo do card do plano.</small>
							</div>
						</div>

                        <!-- Marcar como Popular -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Plano Recomendado / Destaque</label>
                            <div class="col-12 col-sm-9 col-lg-6">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pricing_plan_popular" name="pricing_plan_popular" value="1" <?= (!empty($_POST['pricing_plan_popular'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pricing_plan_popular">Destacar visualmente o card deste plano com borda e sombra diferenciada</label>
                                </div>
                            </div>
                        </div>

                        <!-- Status do Plano / Cartão -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status de Exibição</label>
                            <div class="col-12 col-sm-9 col-lg-6">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" <?= (!isset($_POST['pricing_plan_name']) || !empty($_POST['status'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="status">Plano Ativo (exibido na página inicial e fidelidade)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Imagem do Plano -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Imagem / Ilustração do Card</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="pricing_plan_photo" class="form-control" accept="image/*">
								<small class="text-muted d-block mt-1">Formato recomendado: PNG transparente ou JPG (resolução recomendada: 400x250px).</small>
							</div>
						</div>

                        <!-- Valor e Período -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Valor / Preço do Plano <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="input-group">
									<span class="input-group-text">R$</span>
									<input type="text" class="form-control mascara-moeda" name="pricing_plan_price" placeholder="Ex: 49,90 ou Consultar" value="<?= htmlspecialchars($_POST['pricing_plan_price'] ?? ''); ?>" required>
								</div>
							</div>
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-text">Período</span>
									<input type="text" class="form-control" name="pricing_plan_period" placeholder="Ex: /mês, /ano, ou deixe vazio" value="<?= htmlspecialchars($_POST['pricing_plan_period'] ?? '/mês'); ?>">
								</div>
							</div>
						</div>

                        <!-- Benefícios do Plano (1 por linha) -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Benefícios Inclusos (1 por linha)</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="pricing_plan_benefits" rows="6" placeholder="Exemplo:
Consultas com Clínico Geral e Especialistas
Descontos de até 70% em Exames Laboratoriais
Atendimento Odontológico de Emergência
Descontos Exclusivos em Farmácias Credenciadas
Sem Limite de Utilização ou Carência"><?= htmlspecialchars($_POST['pricing_plan_benefits'] ?? ''); ?></textarea>
								<small class="text-muted">Cada linha digitada será exibida como um item com ícone de confirmação dentro do card.</small>
							</div>
						</div>

                        <!-- Texto e URL do Botão -->
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Texto do Botão de Ação</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_button_text" placeholder="Ex: Contratar Agora, Falar com Atendente, Assinar" value="<?= htmlspecialchars($_POST['pricing_plan_button_text'] ?? 'Contratar Agora'); ?>">
							</div>
						</div>

						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Link de Destino do Botão</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_plan_button_url" placeholder="Ex: https://wa.me/5598988887777?text=Quero%20contratar%20o%20plano ou https://... ou fidelidade.php" value="<?= htmlspecialchars($_POST['pricing_plan_button_url'] ?? ''); ?>">
								<small class="text-muted">Se você inserir um link do WhatsApp, o visitante será direcionado diretamente para conversa personalizada.</small>
							</div>
						</div>

                        <!-- Botão de Salvar -->
						<div class="row mb-3 mt-4">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-success px-4" name="form1"><i class="bi bi-check-circle me-1"></i> Salvar e Publicar Plano</button>
                                <a href="pricing-plan.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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