<?php 
require_once('header.php'); 

// 1. Validação de ID da Página
if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
	header('location: page.php');
	exit;
}

$page_id = (int) $_REQUEST['id'];

$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_id=?");
$statement->execute([$page_id]);
$current_page = $statement->fetch(PDO::FETCH_ASSOC);

if (!$current_page) {
	header('location: page.php');
	exit;
}

// 2. Processamento do Formulário (Suporte Híbrido: AJAX ou POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax']);

if (isset($_POST['form1']) || $is_ajax) {
	$valid = 1;
	$error_msg_list = [];

	$page_name = trim($_POST['page_name'] ?? '');
	$page_slug_input = trim($_POST['page_slug'] ?? '');
	$page_layout = trim($_POST['page_layout'] ?? 'Full Width Page Layout');
	$page_content = $_POST['page_content'] ?? '';
	$status = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

	$meta_title = trim(strip_tags($_POST['meta_title'] ?? ''));
	$meta_keyword = trim(strip_tags($_POST['meta_keyword'] ?? ''));
	$meta_description = trim(strip_tags($_POST['meta_description'] ?? ''));
	$meta_description = preg_replace('/[\r\n\t]+/', ' ', $meta_description);
	$meta_description = preg_replace('/\s+/', ' ', $meta_description);

	if (empty($page_name)) {
		$valid = 0;
		$error_msg_list[] = 'O nome da página é obrigatório.';
	} else {
		// Checar duplicidade de nome com outras páginas
		$stmt_check = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_name = ? AND page_id != ?");
		$stmt_check->execute([$page_name, $page_id]);
		if ($stmt_check->rowCount() > 0) {
			$valid = 0;
			$error_msg_list[] = 'Já existe outra página cadastrada com este nome.';
		}
	}

	// Tratamento e validação do slug
	if (empty($page_slug_input)) {
		$page_slug = pronto_generate_slug($page_name);
	} else {
		$page_slug = pronto_generate_slug($page_slug_input);
	}

	// Checar duplicidade de slug
	$stmt_slug = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug = ? AND page_id != ?");
	$stmt_slug->execute([$page_slug, $page_id]);
	if ($stmt_slug->rowCount() > 0) {
		$page_slug .= '-' . $page_id;
	}

	// Upload de Banner / Capa Social
	$final_banner = $current_page['banner'];
	if (isset($_FILES['banner']) && !empty($_FILES['banner']['name'])) {
		$file_name = $_FILES['banner']['name'];
		$file_tmp  = $_FILES['banner']['tmp_name'];
		$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

		if (!in_array($ext, $allowed_exts)) {
			$valid = 0;
			$error_msg_list[] = 'Formato de banner inválido. Envie arquivos JPG, PNG, WEBP ou GIF.';
		} else {
			$new_banner_name = 'page-banner-' . $page_id . '-' . time() . '.' . $ext;
			if (move_uploaded_file($file_tmp, '../assets/uploads/' . $new_banner_name)) {
				// Remover arquivo anterior se existir
				if (!empty($current_page['banner']) && file_exists('../assets/uploads/' . $current_page['banner'])) {
					@unlink('../assets/uploads/' . $current_page['banner']);
				}
				$final_banner = $new_banner_name;
			} else {
				$valid = 0;
				$error_msg_list[] = 'Não foi possível salvar o arquivo de banner no servidor.';
			}
		}
	}

	// Resposta e gravação no Banco de Dados
	if ($valid === 1) {
		$stmt_update = $pdo->prepare("UPDATE tbl_page SET 
			page_name = ?, 
			page_slug = ?, 
			page_content = ?, 
			page_layout = ?, 
			banner = ?, 
			status = ?, 
			meta_title = ?, 
			meta_keyword = ?, 
			meta_description = ? 
			WHERE page_id = ?");
		
		$stmt_update->execute([
			$page_name,
			$page_slug,
			$page_content,
			$page_layout,
			$final_banner,
			$status,
			$meta_title,
			$meta_keyword,
			$meta_description,
			$page_id
		]);

		// Atualiza dados na memória
		$current_page['page_name']        = $page_name;
		$current_page['page_slug']        = $page_slug;
		$current_page['page_content']     = $page_content;
		$current_page['page_layout']      = $page_layout;
		$current_page['banner']           = $final_banner;
		$current_page['status']           = $status;
		$current_page['meta_title']       = $meta_title;
		$current_page['meta_keyword']     = $meta_keyword;
		$current_page['meta_description'] = $meta_description;

		$success_message = 'A página foi atualizada com sucesso!';

		if ($is_ajax) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'status'  => 'success',
				'message' => $success_message,
				'banner'  => $final_banner,
				'banner_url' => (!empty($final_banner) && file_exists('../assets/uploads/' . $final_banner)) ? BASE_URL . 'assets/uploads/' . $final_banner : '',
				'page_slug' => $page_slug,
				'view_url'  => BASE_URL . 'page.php?slug=' . urlencode($page_slug)
			]);
			exit;
		}
	} else {
		$error_message = implode('<br>', $error_msg_list);

		if ($is_ajax) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'status'  => 'error',
				'message' => $error_message,
				'errors'  => $error_msg_list
			]);
			exit;
		}
	}
}

// Extrai variáveis para renderização
$page_name        = $current_page['page_name'] ?? '';
$page_slug        = $current_page['page_slug'] ?? '';
$page_content     = $current_page['page_content'] ?? '';
$page_layout      = $current_page['page_layout'] ?? 'Full Width Page Layout';
$banner           = $current_page['banner'] ?? '';
$status           = $current_page['status'] ?? 'Active';
$meta_title       = $current_page['meta_title'] ?? '';
$meta_keyword     = $current_page['meta_keyword'] ?? '';
$meta_description = $current_page['meta_description'] ?? '';

$public_page_url = BASE_URL . 'page.php?slug=' . urlencode($page_slug);
?>

<!-- Cabeçalho da Página no Painel Admin -->
<div class="app-content-header pb-2 mb-3 border-bottom">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<div class="d-flex align-items-center gap-2 mb-1">
					<h3 class="mb-0 fw-bold">Editar Página</h3>
					<span class="badge <?= ($status === 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?> px-2 py-1" id="badgeTopStatus">
						<i class="bi <?= ($status === 'Active') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
						<?= ($status === 'Active') ? 'Página Ativa' : 'Página Oculta / Inativa' ?>
					</span>
					<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
						ID: #<?= $page_id ?>
					</span>
				</div>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item"><a href="page.php">Páginas Institucionais</a></li>
					<li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($page_name) ?></li>
				</ol>
			</div>
			
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="<?= $public_page_url ?>" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm shadow-xs" title="Visualizar página ao vivo no site">
					<i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
				</a>
				<a href="page.php" class="btn btn-outline-secondary btn-sm shadow-xs">
					<i class="bi bi-arrow-left me-1"></i> Voltar à Listagem
				</a>
				<button type="button" class="btn btn-primary btn-sm shadow-xs px-3" id="btnSalvarTopo" onclick="document.getElementById('btnSubmitForm').click();">
					<i class="bi bi-floppy-fill me-1"></i> Salvar Alterações
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Conteúdo Principal -->
<div class="app-content">
	<div class="container-fluid">

		<?php if (!empty($error_message)): ?>
			<div class="alert alert-danger alert-dismissible fade show shadow-xs mb-4" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
					<div><?= $error_message ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
			</div>
		<?php endif; ?>

		<?php if (!empty($success_message)): ?>
			<div class="alert alert-success alert-dismissible fade show shadow-xs mb-4" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-check-circle-fill fs-4 me-2"></i>
					<div><?= $success_message ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
			</div>
		<?php endif; ?>

		<form id="formPageEdit" action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="form1" value="1">
			<input type="hidden" name="id" value="<?= $page_id ?>">
			<input type="hidden" name="current_banner" value="<?= htmlspecialchars($banner) ?>">

			<!-- Navegação por Abas (Tabs) -->
			<ul class="nav nav-pills custom-pills-nav mb-4 gap-2 border-bottom pb-3" id="pageEditTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active fw-semibold px-3 py-2" id="tab-content-tab" data-bs-toggle="pill" data-bs-target="#tab-content" type="button" role="tab" aria-controls="tab-content" aria-selected="true">
						<i class="bi bi-file-earmark-richtext me-2"></i>Conteúdo & Layout
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link fw-semibold px-3 py-2" id="tab-banner-tab" data-bs-toggle="pill" data-bs-target="#tab-banner" type="button" role="tab" aria-controls="tab-banner" aria-selected="false">
						<i class="bi bi-image me-2"></i>Banner & Capa Social
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link fw-semibold px-3 py-2" id="tab-seo-tab" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab" aria-controls="tab-seo" aria-selected="false">
						<i class="bi bi-google me-2"></i>SEO & Redes Sociais
					</button>
				</li>
			</ul>

			<!-- Conteúdo das Abas -->
			<div class="tab-content" id="pageEditTabsContent">

				<!-- ==========================================
					 ABA 1: CONTEÚDO E ESTRUTURA DA PÁGINA
					 ========================================== -->
				<div class="tab-pane fade show active" id="tab-content" role="tabpanel" aria-labelledby="tab-content-tab">
					<div class="row g-4">
						
						<!-- Coluna Principal (Conteúdo & Editor) -->
						<div class="col-12 col-xl-8">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h5 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-pencil-square text-primary me-2"></i>Identificação e Conteúdo
									</h5>
								</div>
								<div class="card-body">
									
									<!-- Nome da Página -->
									<div class="mb-3">
										<label class="form-label fw-bold">Nome da Página <span class="text-danger">*</span></label>
										<input type="text" class="form-control form-control-lg" name="page_name" id="pageNameInput" value="<?= htmlspecialchars($page_name) ?>" required placeholder="Ex.: Sobre Nós & Estrutura">
										<div class="form-text small text-body-secondary">Nome institucional exibido no título superior da página e nos menus.</div>
									</div>

									<!-- Slug / URL Amigável -->
									<div class="mb-4">
										<label class="form-label fw-semibold">Slug da URL (Endereço Permanente)</label>
										<div class="input-group">
											<span class="input-group-text bg-body-tertiary text-body-secondary small"><i class="bi bi-link-45deg me-1"></i> <?= BASE_URL ?>page.php?slug=</span>
											<input type="text" class="form-control bg-body-tertiary text-body" name="page_slug" id="pageSlugInput" value="<?= htmlspecialchars($page_slug) ?>" readonly style="cursor: not-allowed;">
										</div>
										<div class="form-text small text-body-secondary mt-1 d-flex align-items-center gap-1">
											<i class="bi bi-lock-fill text-muted"></i> O slug é protegido para preservar links compartilhados e ranqueamento nos mecanismos de busca.
										</div>
									</div>

									<!-- Seletor de Layout -->
									<div class="mb-4">
										<label class="form-label fw-bold">Modelo de Layout</label>
										<select class="form-select select2" name="page_layout" id="pageLayoutSelect" onchange="handleLayoutChange(this.value)">
											<option value="Full Width Page Layout" <?= ($page_layout === 'Full Width Page Layout') ? 'selected' : '' ?>>
												📄 Layout de Largura Total (Conteúdo Completo, Sobre, Termos, Institucional)
											</option>
											<option value="FAQ Page Layout" <?= ($page_layout === 'FAQ Page Layout') ? 'selected' : '' ?>>
												❓ Layout de Perguntas Frequentes (FAQ)
											</option>
											<option value="Doctor Page Layout" <?= ($page_layout === 'Doctor Page Layout') ? 'selected' : '' ?>>
												👨‍⚕️ Layout de Corpo Clínico & Médicos
											</option>
											<option value="Photo Gallery Page Layout" <?= ($page_layout === 'Photo Gallery Page Layout') ? 'selected' : '' ?>>
												🖼️ Layout de Galeria de Fotos
											</option>
											<option value="Video Gallery Page Layout" <?= ($page_layout === 'Video Gallery Page Layout') ? 'selected' : '' ?>>
												🎥 Layout de Galeria de Vídeos
											</option>
											<option value="Blog Page Layout" <?= ($page_layout === 'Blog Page Layout') ? 'selected' : '' ?>>
												📰 Layout de Blog & Notícias
											</option>
											<option value="Contact Us Page Layout" <?= ($page_layout === 'Contact Us Page Layout') ? 'selected' : '' ?>>
												📍 Layout de Contato & Localização
											</option>
										</select>
										<div class="form-text small text-body-secondary mt-1">
											Selecione o estilo de exibição da página no site.
										</div>
									</div>

									<!-- Editor Summernote (Visível para Full Width) -->
									<div id="wrapperPageContent" style="<?= ($page_layout === 'Full Width Page Layout') ? 'display:block;' : 'display:none;' ?>">
										<div class="d-flex align-items-center justify-content-between mb-2">
											<label class="form-label fw-bold mb-0">Texto e Conteúdo da Página</label>
											<span class="badge bg-secondary-subtle text-secondary small">Editor Rico Formatado</span>
										</div>
										<textarea class="form-control" name="page_content" id="summernoteEditor" rows="14"><?= htmlspecialchars($page_content) ?></textarea>
										<div class="form-text small text-body-secondary mt-1">
											Você pode usar cabeçalhos, listas, formatações em negrito, tabelas e links para estruturar o texto institucional.
										</div>
									</div>

									<!-- Alerta Informativo para Outros Layouts Dinâmicos -->
									<div id="alertSpecialLayout" class="alert alert-info border-info-subtle shadow-xs mt-3" style="<?= ($page_layout !== 'Full Width Page Layout') ? 'display:block;' : 'display:none;' ?>">
										<div class="d-flex align-items-start gap-2">
											<i class="bi bi-info-circle-fill fs-5 mt-1 text-info"></i>
											<div>
												<h6 class="fw-bold mb-1">Layout Gerenciado Dinamicamente</h6>
												<p class="mb-0 small">
													O layout selecionado carrega dados automáticos cadastrados em módulos específicos (ex.: Corpo Clínico, Fotos, Vídeos ou Notícias). As informações gerais deste cabeçalho e SEO continuam ativas para esta página.
												</p>
											</div>
										</div>
									</div>

								</div>
							</div>
						</div>

						<!-- Coluna Lateral (Publicação, Status e Atalhos) -->
						<div class="col-12 col-xl-4">
							
							<!-- Card de Status e Publicação -->
							<div class="card card-outline card-success shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h6 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-sliders text-success me-2"></i>Controle de Publicação
									</h6>
								</div>
								<div class="card-body">
									<div class="form-check form-switch fs-5 mb-2">
										<input class="form-check-input" type="checkbox" role="switch" name="status_toggle" id="pageStatusToggle" value="1" <?= ($status === 'Active') ? 'checked' : '' ?>>
										<label class="form-check-label fs-6 fw-bold ms-2 <?= ($status === 'Active') ? 'text-success' : 'text-danger' ?>" for="pageStatusToggle" id="pageStatusLabel">
											<?= ($status === 'Active') ? 'Página Ativa e Visível' : 'Página Oculta / Desativada' ?>
										</label>
									</div>
									<input type="hidden" name="status" id="inputPageStatusHidden" value="<?= htmlspecialchars($status) ?>">
									
									<p class="small text-body-secondary mb-3">
										Quando desativada, a página é oculta dos menus, do rodapé e acessos diretos à URL são redirecionados automaticamente.
									</p>

									<hr class="my-3 text-muted opacity-25">

									<div class="d-grid gap-2">
										<button type="submit" class="btn btn-primary py-2 fw-semibold" id="btnSubmitForm" name="form1">
											<i class="bi bi-floppy2-fill me-1"></i> Salvar Alterações
										</button>
										<a href="<?= $public_page_url ?>" target="_blank" rel="noopener" class="btn btn-outline-info py-2 fw-semibold">
											<i class="bi bi-box-arrow-up-right me-1"></i> Visualizar Página
										</a>
										<a href="page.php" class="btn btn-outline-secondary py-2">
											<i class="bi bi-x-circle me-1"></i> Cancelar
										</a>
									</div>

									<div class="mt-3 text-center text-muted small">
										<i class="bi bi-keyboard me-1"></i> Dica: Pressione <strong>Ctrl + S</strong> para salvar rapidamente.
									</div>
								</div>
							</div>

							<!-- Card Resumo de Mídia -->
							<div class="card shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h6 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-card-image text-primary me-2"></i>Banner Atual
									</h6>
								</div>
								<div class="card-body text-center">
									<?php if (!empty($banner) && file_exists('../assets/uploads/' . $banner)): ?>
										<div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-xs mb-2 bg-dark">
											<img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($banner) ?>" id="sideBannerPreview" alt="Banner" class="w-100 h-100 object-fit-cover">
										</div>
										<span class="badge bg-success-subtle text-success small border border-success-subtle"><i class="bi bi-check-circle me-1"></i> Banner Configurado</span>
									<?php else: ?>
										<div class="p-4 bg-body-tertiary rounded-3 border border-dashed text-secondary mb-2">
											<i class="bi bi-image fs-1 d-block mb-1 text-muted"></i>
											<span class="small">Nenhum banner personalizado enviado. É utilizado o banner padrão institucional.</span>
										</div>
									<?php endif; ?>
									
									<div class="mt-3">
										<button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="document.getElementById('tab-banner-tab').click();">
											<i class="bi bi-upload me-1"></i> Alterar Banner na Aba Mídia
										</button>
									</div>
								</div>
							</div>

						</div>

					</div>
				</div>

				<!-- ==========================================
					 ABA 2: BANNER & CAPA SOCIAL
					 ========================================== -->
				<div class="tab-pane fade" id="tab-banner" role="tabpanel" aria-labelledby="tab-banner-tab">
					<div class="row g-4">
						<div class="col-12 col-xl-8">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h5 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-image text-primary me-2"></i>Banner Superior & Imagem de Compartilhamento (OpenGraph)
									</h5>
								</div>
								<div class="card-body">
									
									<div class="mb-4">
										<label class="form-label fw-bold">Enviar Novo Banner / Imagem Social</label>
										<input type="file" name="banner" class="form-control" id="pageBannerInput" accept="image/png, image/jpeg, image/webp, image/gif" data-dimension="1200 x 630 px">
										
										<div class="d-flex flex-wrap align-items-center gap-2 mt-2">
											<span class="badge bg-primary text-white"><i class="bi bi-arrows-fullscreen me-1"></i> Dimensão Recomendada: 1200 x 630 px</span>
											<span class="badge bg-dark text-white"><i class="bi bi-aspect-ratio me-1"></i> Proporção: 1.91:1 (Panorâmica)</span>
											<span class="badge bg-secondary-subtle text-body border"><i class="bi bi-file-earmark-image me-1"></i> Formatos: JPG, PNG, WEBP</span>
										</div>

										<div class="form-text small text-body-secondary mt-2">
											Esta imagem é aplicada no cabeçalho da página e também atua como a <strong>capa de prévia (Open Graph)</strong> ao compartilhar o link no WhatsApp, Facebook, LinkedIn e Twitter/X.
										</div>
									</div>

									<!-- Prévia do Novo Banner -->
									<div id="pageBannerPreviewBox" class="mt-4 p-3 border rounded-3 bg-body-tertiary d-none">
										<div class="d-flex align-items-center justify-content-between mb-2">
											<span class="fw-bold text-success small"><i class="bi bi-eye me-1"></i> Pré-visualização do Novo Arquivo Selecionado:</span>
											<span class="badge bg-success-subtle text-success small" id="previewFileSizeBadge"></span>
										</div>
										<div class="ratio ratio-16x9 rounded-3 overflow-hidden border bg-dark shadow-xs" style="max-height: 340px;">
											<img id="pageBannerPreviewElement" src="" alt="Nova Prévia" class="w-100 h-100 object-fit-cover">
										</div>
									</div>

									<!-- Exibição do Banner Atual -->
									<div class="mt-4 pt-3 border-top">
										<h6 class="fw-bold text-body mb-3"><i class="bi bi-check2-circle text-primary me-1"></i> Banner Atualmente em Uso no Site:</h6>
										<?php if (!empty($banner) && file_exists('../assets/uploads/' . $banner)): ?>
											<div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-sm bg-dark" style="max-height: 320px;">
												<img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($banner) ?>" id="currentBannerFullImg" alt="Banner Atual" class="w-100 h-100 object-fit-cover">
											</div>
											<div class="small text-body-secondary mt-2">
												Arquivo: <code><?= htmlspecialchars($banner) ?></code>
											</div>
										<?php else: ?>
											<div class="alert alert-secondary border mb-0 small">
												<i class="bi bi-info-circle me-1"></i> Nenhum banner específico enviado. O site exibe a imagem padrão institucional no cabeçalho desta página.
											</div>
										<?php endif; ?>
									</div>

								</div>
							</div>
						</div>

						<div class="col-12 col-xl-4">
							<div class="card shadow-sm border-0 bg-primary-subtle text-primary mb-4">
								<div class="card-body">
									<h6 class="fw-bold mb-2"><i class="bi bi-lightbulb-fill me-1"></i> Recomendações para Imagens</h6>
									<ul class="small mb-0 ps-3">
										<li class="mb-2">Utilize imagens em alta resolução na proporção <strong>1200 x 630 pixels</strong> para evitar cortes indesejados.</li>
										<li class="mb-2">Fotos nítidas de consultórios, equipamentos ou ambientação médica geram maior engajamento e credibilidade.</li>
										<li class="mb-0">Formatos modernos como <strong>WEBP</strong> e <strong>JPG otimizado</strong> carregam até 3x mais rápido em celulares.</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- ==========================================
					 ABA 3: SEO & REDES SOCIAIS
					 ========================================== -->
				<div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-tab">
					<div class="row g-4">
						
						<!-- Campos de Entrada SEO -->
						<div class="col-12 col-xl-7">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h5 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-search text-primary me-2"></i>Otimização para Mecanismos de Busca (Google & Bing)
									</h5>
								</div>
								<div class="card-body">

									<!-- Meta Title -->
									<div class="mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<label class="form-label fw-bold mb-0">Título SEO (Meta Title)</label>
											<span class="small text-muted" id="metaTitleCounter"><span id="titleCount">0</span> / 60 caracteres</span>
										</div>
										<input type="text" class="form-control" name="meta_title" id="metaTitleInput" value="<?= htmlspecialchars($meta_title) ?>" maxlength="80" placeholder="Ex.: Centro Médico Pronto Saúde | Excelência e Cuidado Há Mais de 5 Anos">
										<div class="progress mt-1" style="height: 4px;">
											<div class="progress-bar bg-success" id="titleProgressBar" role="progressbar" style="width: 0%;"></div>
										</div>
										<div class="form-text small text-body-secondary mt-1">
											Título exibido na aba do navegador e em destaque azul nos resultados de busca do Google.
										</div>
									</div>

									<!-- Meta Description -->
									<div class="mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<label class="form-label fw-bold mb-0">Descrição SEO (Meta Description)</label>
											<span class="small text-muted" id="metaDescCounter"><span id="descCount">0</span> / 160 caracteres</span>
										</div>
										<textarea class="form-control" name="meta_description" id="metaDescInput" rows="3" maxlength="200" placeholder="Ex.: Há mais de 5 anos oferecendo atendimento médico humanizado, consultas com especialistas qualificados e infraestrutura moderna..."><?= htmlspecialchars($meta_description) ?></textarea>
										<div class="progress mt-1" style="height: 4px;">
											<div class="progress-bar bg-success" id="descProgressBar" role="progressbar" style="width: 0%;"></div>
										</div>
										<div class="form-text small text-body-secondary mt-1">
											Resumo atraente que incentiva os usuários a clicarem no seu link ao pesquisarem no Google.
										</div>
									</div>

									<!-- Palavras-chave -->
									<div class="mb-3">
										<label class="form-label fw-semibold">Palavras-chave (Meta Keywords)</label>
										<textarea class="form-control" name="meta_keyword" id="metaKeywordInput" rows="3" placeholder="pronto saude, centro medico rj, consultas especializadas, exames rj"><?= htmlspecialchars($meta_keyword) ?></textarea>
										<div class="form-text small text-body-secondary mt-1">
											Separe os termos principais por vírgula.
										</div>
									</div>

								</div>
							</div>
						</div>

						<!-- Simulador em Tempo Real (Google & WhatsApp) -->
						<div class="col-12 col-xl-5">
							
							<!-- Prévia do Google Search -->
							<div class="card shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h6 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-google text-primary me-2"></i>Prévia no Google Search
									</h6>
								</div>
								<div class="card-body bg-body-tertiary">
									<div class="p-3 bg-body rounded-3 border shadow-xs">
										<div class="d-flex align-items-center gap-2 mb-1">
											<div class="rounded-circle bg-light border p-1 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
												<i class="bi bi-globe text-primary small"></i>
											</div>
											<div class="text-truncate">
												<div class="small text-body-secondary lh-1 mb-0" style="font-size: 0.75rem;">prontosaude.com</div>
												<div class="small text-muted text-truncate" style="font-size: 0.72rem;" id="googlePreviewUrl"><?= $public_page_url ?></div>
											</div>
										</div>
										<h6 class="text-primary fw-semibold mb-1 text-truncate" id="googlePreviewTitle" style="cursor: pointer; text-decoration: underline;">
											<?= !empty($meta_title) ? htmlspecialchars($meta_title) : htmlspecialchars($page_name) . ' | Pronto Saúde' ?>
										</h6>
										<p class="small text-body-secondary mb-0 line-clamp-2" id="googlePreviewDesc" style="font-size: 0.82rem; line-height: 1.35;">
											<?= !empty($meta_description) ? htmlspecialchars($meta_description) : 'Acesse a página oficial e conheça todos os detalhes, serviços e diferenciais oferecidos pelo Centro Médico Pronto Saúde.' ?>
										</p>
									</div>
									<div class="text-muted small mt-2 text-center">
										<i class="bi bi-eye me-1"></i> Simulação aproximada de resultado nos buscadores.
									</div>
								</div>
							</div>

							<!-- Prévia de Compartilhamento no WhatsApp -->
							<div class="card shadow-sm mb-4">
								<div class="card-header bg-transparent py-3">
									<h6 class="card-title fw-bold mb-0 text-body">
										<i class="bi bi-whatsapp text-success me-2"></i>Prévia no WhatsApp & Redes Sociais
									</h6>
								</div>
								<div class="card-body bg-body-tertiary">
									<div class="border rounded-3 overflow-hidden shadow-xs bg-body" style="max-width: 320px; margin: 0 auto;">
										<div class="ratio ratio-16x9 bg-dark">
											<img id="whatsappPreviewImg" src="<?= (!empty($banner) && file_exists('../assets/uploads/' . $banner)) ? BASE_URL . 'assets/uploads/' . htmlspecialchars($banner) : BASE_URL . 'assets/uploads/department-1.png' ?>" alt="Capa" class="w-100 h-100 object-fit-cover">
										</div>
										<div class="p-2 border-top">
											<div class="text-uppercase text-muted" style="font-size: 0.68rem; font-weight: 700;">prontosaude.com</div>
											<div class="fw-bold text-dark text-truncate small" id="whatsappPreviewTitle"><?= !empty($meta_title) ? htmlspecialchars($meta_title) : htmlspecialchars($page_name) ?></div>
											<div class="text-secondary small text-truncate" id="whatsappPreviewDesc"><?= !empty($meta_description) ? htmlspecialchars($meta_description) : 'Clique para ver mais detalhes no site da Pronto Saúde.' ?></div>
										</div>
									</div>
								</div>
							</div>

						</div>

					</div>
				</div>

			</div><!-- ./tab-content -->

		</form>

	</div>
</div>

<style>
.custom-pills-nav .nav-link {
	border-radius: 8px;
	color: var(--bs-body-color);
	background: var(--bs-tertiary-bg, #f8f9fa);
	border: 1px solid var(--bs-border-color, #dee2e6);
	transition: all 0.2s ease-in-out;
}
.custom-pills-nav .nav-link:hover {
	background: var(--bs-primary-subtle, #e0f2fe);
	color: var(--bs-primary);
}
.custom-pills-nav .nav-link.active {
	background-color: var(--bs-primary, #0d6efd) !important;
	color: #ffffff !important;
	border-color: var(--bs-primary, #0d6efd);
	box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
}
.line-clamp-2 {
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
	// 1. Inicialização do Editor Summernote
	if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.summernote !== 'undefined') {
		$('#summernoteEditor').summernote({
			placeholder: 'Digite ou formate aqui o conteúdo completo da página...',
			tabsize: 2,
			height: 420,
			lang: 'pt-BR',
			toolbar: [
				['style', ['style']],
				['font', ['bold', 'italic', 'underline', 'clear']],
				['fontname', ['fontname']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['table', ['table']],
				['insert', ['link', 'picture', 'video', 'hr']],
				['view', ['fullscreen', 'codeview', 'help']]
			],
			callbacks: {
				onChange: function(contents) {
					// Dispara atualização se necessário
				}
			}
		});
	}

	// 2. Controle de Exibição Dinâmica por Modelo de Layout
	window.handleLayoutChange = function(layout) {
		const wrapper = document.getElementById('wrapperPageContent');
		const alertBox = document.getElementById('alertSpecialLayout');
		if (layout === 'Full Width Page Layout') {
			if (wrapper) wrapper.style.display = 'block';
			if (alertBox) alertBox.style.display = 'none';
		} else {
			if (wrapper) wrapper.style.display = 'none';
			if (alertBox) alertBox.style.display = 'block';
		}
	};

	// 3. Controle do Interruptor (Switch) de Status
	const statusToggle = document.getElementById('pageStatusToggle');
	const statusLabel = document.getElementById('pageStatusLabel');
	const statusHidden = document.getElementById('inputPageStatusHidden');
	const badgeTopStatus = document.getElementById('badgeTopStatus');

	if (statusToggle && statusLabel && statusHidden) {
		statusToggle.addEventListener('change', function () {
			if (this.checked) {
				statusHidden.value = 'Active';
				statusLabel.textContent = 'Página Ativa e Visível';
				statusLabel.className = 'form-check-label fs-6 fw-bold ms-2 text-success';
				if (badgeTopStatus) {
					badgeTopStatus.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
					badgeTopStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Página Ativa';
				}
				if (window.toastr) toastr.info('Status alterado para Ativa (salve para confirmar).');
			} else {
				statusHidden.value = 'Inactive';
				statusLabel.textContent = 'Página Oculta / Desativada';
				statusLabel.className = 'form-check-label fs-6 fw-bold ms-2 text-danger';
				if (badgeTopStatus) {
					badgeTopStatus.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1';
					badgeTopStatus.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Página Oculta / Inativa';
				}
				if (window.toastr) toastr.warning('Status alterado para Inativa.');
			}
		});
	}

	// 4. Pré-visualização do Banner em Tempo Real
	const bannerInput = document.getElementById('pageBannerInput');
	const bannerPreviewBox = document.getElementById('pageBannerPreviewBox');
	const bannerPreviewEl = document.getElementById('pageBannerPreviewElement');
	const sideBannerPreview = document.getElementById('sideBannerPreview');
	const whatsappPreviewImg = document.getElementById('whatsappPreviewImg');
	const previewFileSizeBadge = document.getElementById('previewFileSizeBadge');

	if (bannerInput) {
		bannerInput.addEventListener('change', function () {
			const file = this.files && this.files[0];
			if (file) {
				// Validação de tipo
				if (!file.type.match('image.*')) {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'warning',
							title: 'Formato Inválido',
							text: 'Por favor, selecione uma imagem válida (JPG, PNG, WEBP ou GIF).'
						});
					}
					bannerInput.value = '';
					return;
				}

				const reader = new FileReader();
				reader.onload = function (e) {
					const dataUrl = e.target.result;
					if (bannerPreviewEl) bannerPreviewEl.src = dataUrl;
					if (sideBannerPreview) sideBannerPreview.src = dataUrl;
					if (whatsappPreviewImg) whatsappPreviewImg.src = dataUrl;
					if (bannerPreviewBox) bannerPreviewBox.classList.remove('d-none');
					
					if (previewFileSizeBadge) {
						const sizeKb = (file.size / 1024).toFixed(1);
						previewFileSizeBadge.textContent = `${sizeKb} KB`;
					}
				};
				reader.readAsDataURL(file);
			} else {
				if (bannerPreviewBox) bannerPreviewBox.classList.add('d-none');
			}
		});
	}

	// 5. Contadores de Caracteres e Pré-visualizações de SEO ao Vivo
	const metaTitleInput = document.getElementById('metaTitleInput');
	const titleCount = document.getElementById('titleCount');
	const titleProgressBar = document.getElementById('titleProgressBar');
	const googlePreviewTitle = document.getElementById('googlePreviewTitle');
	const whatsappPreviewTitle = document.getElementById('whatsappPreviewTitle');
	const pageNameInput = document.getElementById('pageNameInput');

	function updateTitleSEO() {
		const val = metaTitleInput ? metaTitleInput.value.trim() : '';
		const nameVal = pageNameInput ? pageNameInput.value.trim() : 'Página';
		const len = val.length;

		if (titleCount) titleCount.textContent = len;
		if (titleProgressBar) {
			const pct = Math.min(100, Math.round((len / 60) * 100));
			titleProgressBar.style.width = pct + '%';
			if (len <= 60) {
				titleProgressBar.className = 'progress-bar bg-success';
			} else if (len <= 70) {
				titleProgressBar.className = 'progress-bar bg-warning';
			} else {
				titleProgressBar.className = 'progress-bar bg-danger';
			}
		}

		const displayTitle = val !== '' ? val : (nameVal + ' | Pronto Saúde');
		if (googlePreviewTitle) googlePreviewTitle.textContent = displayTitle;
		if (whatsappPreviewTitle) whatsappPreviewTitle.textContent = displayTitle;
	}

	if (metaTitleInput) metaTitleInput.addEventListener('input', updateTitleSEO);
	if (pageNameInput) pageNameInput.addEventListener('input', updateTitleSEO);
	updateTitleSEO();

	const metaDescInput = document.getElementById('metaDescInput');
	const descCount = document.getElementById('descCount');
	const descProgressBar = document.getElementById('descProgressBar');
	const googlePreviewDesc = document.getElementById('googlePreviewDesc');
	const whatsappPreviewDesc = document.getElementById('whatsappPreviewDesc');

	function updateDescSEO() {
		const val = metaDescInput ? metaDescInput.value.trim() : '';
		const len = val.length;

		if (descCount) descCount.textContent = len;
		if (descProgressBar) {
			const pct = Math.min(100, Math.round((len / 160) * 100));
			descProgressBar.style.width = pct + '%';
			if (len <= 160) {
				descProgressBar.className = 'progress-bar bg-success';
			} else if (len <= 180) {
				descProgressBar.className = 'progress-bar bg-warning';
			} else {
				descProgressBar.className = 'progress-bar bg-danger';
			}
		}

		const displayDesc = val !== '' ? val : 'Acesse a página oficial e conheça todos os detalhes, serviços e diferenciais oferecidos pelo Centro Médico Pronto Saúde.';
		if (googlePreviewDesc) googlePreviewDesc.textContent = displayDesc;
		if (whatsappPreviewDesc) whatsappPreviewDesc.textContent = displayDesc;
	}

	if (metaDescInput) metaDescInput.addEventListener('input', updateDescSEO);
	updateDescSEO();

	// 6. Submissão Inteligente via AJAX com SweetAlert2
	const form = document.getElementById('formPageEdit');
	const btnSubmit = document.getElementById('btnSubmitForm');
	const btnSalvarTopo = document.getElementById('btnSalvarTopo');

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			// Sincroniza o conteúdo do Summernote para o textarea
			if (typeof window.jQuery !== 'undefined' && $('#summernoteEditor').length) {
				$('#summernoteEditor').val($('#summernoteEditor').summernote('code'));
			}

			const formData = new FormData(form);
			formData.append('is_ajax', '1');

			const originalBtnHtml = btnSubmit ? btnSubmit.innerHTML : '';
			if (btnSubmit) {
				btnSubmit.disabled = true;
				btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
			}
			if (btnSalvarTopo) {
				btnSalvarTopo.disabled = true;
				btnSalvarTopo.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
			}

			fetch(window.location.href, {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (btnSubmit) {
					btnSubmit.disabled = false;
					btnSubmit.innerHTML = originalBtnHtml;
				}
				if (btnSalvarTopo) {
					btnSalvarTopo.disabled = false;
					btnSalvarTopo.innerHTML = '<i class="bi bi-floppy-fill me-1"></i> Salvar Alterações';
				}

				if (data.status === 'success') {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Página Atualizada!',
							text: data.message || 'Todas as alterações foram salvas com sucesso no banco de dados.',
							showCancelButton: true,
							confirmButtonText: '<i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site',
							cancelButtonText: 'Continuar Editando',
							confirmButtonColor: '#0d6efd',
							cancelButtonColor: '#6c757d'
						}).then((result) => {
							if (result.isConfirmed && data.view_url) {
								window.open(data.view_url, '_blank');
							}
						});
					} else if (window.toastr) {
						toastr.success(data.message || 'Página atualizada com sucesso!');
					}

					// Se um novo banner foi gravado, atualiza as prévias definitivas
					if (data.banner_url) {
						const currentFull = document.getElementById('currentBannerFullImg');
						if (currentFull) currentFull.src = data.banner_url;
						if (sideBannerPreview) sideBannerPreview.src = data.banner_url;
						if (whatsappPreviewImg) whatsappPreviewImg.src = data.banner_url;
						if (bannerPreviewBox) bannerPreviewBox.classList.add('d-none');
						if (bannerInput) bannerInput.value = '';
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Não foi possível salvar',
							html: data.message || 'Verifique os campos obrigatórios e tente novamente.',
							confirmButtonColor: '#0d6efd'
						});
					} else if (window.toastr) {
						toastr.error(data.message || 'Erro ao salvar página.');
					}
				}
			})
			.catch(err => {
				console.error(err);
				if (btnSubmit) {
					btnSubmit.disabled = false;
					btnSubmit.innerHTML = originalBtnHtml;
				}
				if (btnSalvarTopo) {
					btnSalvarTopo.disabled = false;
					btnSalvarTopo.innerHTML = '<i class="bi bi-floppy-fill me-1"></i> Salvar Alterações';
				}
				// Se falhar o JSON AJAX, faz o submit padrão como fallback seguro
				form.submit();
			});
		});
	}

	// 7. Atalho Global: Pressione Ctrl+S / Cmd+S para salvar rapidamente
	document.addEventListener('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
			e.preventDefault();
			if (btnSubmit) btnSubmit.click();
		}
	});
});
</script>

<?php require_once('footer.php'); ?>
