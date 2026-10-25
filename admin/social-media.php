<?php
// Suporte a submissão via AJAX assíncrono isolado
$is_ajax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($is_ajax && $_SERVER['REQUEST_METHOD'] === 'POST') {
	while (ob_get_level()) {
		ob_end_clean();
	}
	ob_start();
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	require_once('config.php');

	if (!isset($_SESSION['user'])) {
		while (ob_get_level()) { ob_end_clean(); }
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['status' => 'error', 'message' => 'Sessão expirada. Faça login novamente.']);
		exit;
	}

	$social_names = [
		'Facebook', 'Twitter', 'LinkedIn', 'Google Plus', 'Pinterest',
		'YouTube', 'Instagram', 'Tumblr', 'Flickr', 'Reddit',
		'Snapchat', 'WhatsApp', 'Quora', 'StumbleUpon', 'Delicious', 'Digg'
	];

	try {
		$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
		foreach($social_names as $s_name) {
			$field = strtolower(str_replace(' ', '', $s_name));
			$url_value = trim($_POST[$field] ?? '');
			$statement->execute(array($url_value, $s_name));
		}

		while (ob_get_level()) { ob_end_clean(); }
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'status' => 'success',
			'message' => 'Links das redes sociais atualizados com sucesso no portal!'
		]);
	} catch (Exception $e) {
		while (ob_get_level()) { ob_end_clean(); }
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'status' => 'error',
			'message' => 'Erro ao salvar: ' . $e->getMessage()
		]);
	}
	exit;
}

require_once('header.php');

$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
	$social_names = [
		'Facebook', 'Twitter', 'LinkedIn', 'Google Plus', 'Pinterest',
		'YouTube', 'Instagram', 'Tumblr', 'Flickr', 'Reddit',
		'Snapchat', 'WhatsApp', 'Quora', 'StumbleUpon', 'Delicious', 'Digg'
	];

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	foreach($social_names as $s_name) {
		$field = strtolower(str_replace(' ', '', $s_name));
		$url_value = trim($_POST[$field] ?? '');
		$statement->execute(array($url_value, $s_name));
	}
	$success_message = 'Links das redes sociais atualizados com sucesso!';
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">
					<i class="bi bi-share text-primary me-2"></i>Redes Sociais & Canais Digitais
				</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Redes Sociais</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="<?php echo BASE_URL; ?>" target="_blank" class="btn btn-outline-primary btn-sm shadow-sm">
					<i class="bi bi-globe me-1"></i> Ver no Portal Público
				</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_social ORDER BY social_id ASC");
$statement->execute();
$social_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$socials = [];
$total_active = 0;
foreach ($social_rows as $row) {
	$field_key = strtolower(str_replace(' ', '', $row['social_name']));
	$socials[$field_key] = [
		'id'   => $row['social_id'],
		'name' => $row['social_name'],
		'url'  => $row['social_url'],
		'icon' => $row['social_icon']
	];
	if(!empty($row['social_url'])) {
		$total_active++;
	}
}
$total_networks = count($socials);
$total_inactive = $total_networks - $total_active;

// Configurações visuais personalizadas por rede
$meta_social = [
	'whatsapp' => [
		'color' => '#25D366',
		'bg_subtle' => 'rgba(37, 211, 102, 0.12)',
		'icon_class' => 'bi bi-whatsapp',
		'category' => 'main',
		'placeholder' => 'https://wa.me/5521999999999?text=Olá',
		'hint' => 'Canal direto de agendamento e suporte via WhatsApp'
	],
	'instagram' => [
		'color' => '#E1306C',
		'bg_subtle' => 'rgba(225, 48, 108, 0.12)',
		'icon_class' => 'bi bi-instagram',
		'category' => 'main',
		'placeholder' => 'https://instagram.com/prontosaude',
		'hint' => 'Perfil oficial no Instagram'
	],
	'facebook' => [
		'color' => '#1877F2',
		'bg_subtle' => 'rgba(24, 119, 242, 0.12)',
		'icon_class' => 'bi bi-facebook',
		'category' => 'main',
		'placeholder' => 'https://facebook.com/prontosaude',
		'hint' => 'Página institucional no Facebook'
	],
	'youtube' => [
		'color' => '#FF0000',
		'bg_subtle' => 'rgba(255, 0, 0, 0.12)',
		'icon_class' => 'bi bi-youtube',
		'category' => 'main',
		'placeholder' => 'https://youtube.com/@prontosaude',
		'hint' => 'Canal de vídeos e orientações de saúde'
	],
	'linkedin' => [
		'color' => '#0A66C2',
		'bg_subtle' => 'rgba(10, 102, 194, 0.12)',
		'icon_class' => 'bi bi-linkedin',
		'category' => 'main',
		'placeholder' => 'https://linkedin.com/company/prontosaude',
		'hint' => 'Página corporativa no LinkedIn'
	],
	'twitter' => [
		'color' => '#0f1419',
		'bg_subtle' => 'rgba(15, 20, 25, 0.12)',
		'icon_class' => 'bi bi-twitter-x',
		'category' => 'main',
		'placeholder' => 'https://x.com/prontosaude',
		'hint' => 'Perfil oficial no X (antigo Twitter)'
	],
	'pinterest' => [
		'color' => '#BD081C',
		'bg_subtle' => 'rgba(189, 8, 28, 0.12)',
		'icon_class' => 'bi bi-pinterest',
		'category' => 'secondary',
		'placeholder' => 'https://pinterest.com/prontosaude',
		'hint' => 'Painéis de saúde e dicas no Pinterest'
	],
	'reddit' => [
		'color' => '#FF4500',
		'bg_subtle' => 'rgba(255, 69, 0, 0.12)',
		'icon_class' => 'bi bi-reddit',
		'category' => 'secondary',
		'placeholder' => 'https://reddit.com/r/prontosaude',
		'hint' => 'Comunidade no Reddit'
	],
	'snapchat' => [
		'color' => '#FFFC00',
		'bg_subtle' => 'rgba(255, 252, 0, 0.25)',
		'icon_class' => 'bi bi-snapchat',
		'category' => 'secondary',
		'placeholder' => 'https://snapchat.com/add/prontosaude',
		'hint' => 'Perfil no Snapchat'
	],
	'tumblr' => [
		'color' => '#36465D',
		'bg_subtle' => 'rgba(54, 70, 93, 0.12)',
		'icon_class' => 'bi bi-tumblr',
		'category' => 'secondary',
		'placeholder' => 'https://prontosaude.tumblr.com',
		'hint' => 'Blog no Tumblr'
	],
	'flickr' => [
		'color' => '#FF0084',
		'bg_subtle' => 'rgba(255, 0, 132, 0.12)',
		'icon_class' => 'bi bi-camera-fill',
		'category' => 'secondary',
		'placeholder' => 'https://flickr.com/photos/prontosaude',
		'hint' => 'Galeria no Flickr'
	],
	'quora' => [
		'color' => '#A82400',
		'bg_subtle' => 'rgba(168, 36, 0, 0.12)',
		'icon_class' => 'bi bi-question-circle-fill',
		'category' => 'secondary',
		'placeholder' => 'https://quora.com/profile/prontosaude',
		'hint' => 'Perfil no Quora'
	],
	'googleplus' => [
		'color' => '#DB4437',
		'bg_subtle' => 'rgba(219, 68, 55, 0.12)',
		'icon_class' => 'bi bi-google',
		'category' => 'legacy',
		'placeholder' => 'https://plus.google.com/...',
		'hint' => 'Google Plus (Plataforma arquivada)'
	],
	'stumbleupon' => [
		'color' => '#EB4924',
		'bg_subtle' => 'rgba(235, 73, 36, 0.12)',
		'icon_class' => 'bi bi-signpost-split',
		'category' => 'legacy',
		'placeholder' => 'https://stumbleupon.com/...',
		'hint' => 'StumbleUpon (Plataforma arquivada)'
	],
	'delicious' => [
		'color' => '#3399FF',
		'bg_subtle' => 'rgba(51, 153, 255, 0.12)',
		'icon_class' => 'bi bi-bookmark-star',
		'category' => 'legacy',
		'placeholder' => 'https://del.icio.us/...',
		'hint' => 'Delicious Bookmarks (Plataforma arquivada)'
	],
	'digg' => [
		'color' => '#005BE2',
		'bg_subtle' => 'rgba(0, 91, 226, 0.12)',
		'icon_class' => 'bi bi-newspaper',
		'category' => 'legacy',
		'placeholder' => 'https://digg.com/...',
		'hint' => 'Digg Notícias'
	]
];
?>

<div class="app-content">
<div class="container-fluid">

	<!-- Barra de Resumo & Indicadores Rápidos -->
	<div class="row g-3 mb-4">
		<div class="col-md-4">
			<div class="card border-0 shadow-sm bg-body-tertiary">
				<div class="card-body d-flex align-items-center gap-3 py-3">
					<div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 48px; height: 48px;">
						<i class="bi bi-collection-fill fs-4"></i>
					</div>
					<div>
						<div class="text-muted small fw-semibold text-uppercase">Total Cadastrado</div>
						<div class="fs-4 fw-bold text-body" id="metricTotal"><?php echo $total_networks; ?> Redes</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm bg-body-tertiary">
				<div class="card-body d-flex align-items-center gap-3 py-3">
					<div class="rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success" style="width: 48px; height: 48px;">
						<i class="bi bi-check-circle-fill fs-4"></i>
					</div>
					<div>
						<div class="text-muted small fw-semibold text-uppercase">Ativas no Portal</div>
						<div class="fs-4 fw-bold text-success" id="metricActive"><?php echo $total_active; ?> Redes</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm bg-body-tertiary">
				<div class="card-body d-flex align-items-center gap-3 py-3">
					<div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary" style="width: 48px; height: 48px;">
						<i class="bi bi-eye-slash-fill fs-4"></i>
					</div>
					<div>
						<div class="text-muted small fw-semibold text-uppercase">Ocultas (Vazias)</div>
						<div class="fs-4 fw-bold text-body-secondary" id="metricInactive"><?php echo $total_inactive; ?> Redes</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Alerta Informativo Amigável -->
	<div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4 rounded-3" style="background: rgba(13, 202, 240, 0.12); color: #055160;">
		<i class="bi bi-info-circle-fill fs-3 text-info"></i>
		<div class="small">
			<strong>Gerenciamento em Tempo Real:</strong> As redes sociais com link preenchido são exibidas automaticamente no cabeçalho e rodapé do portal. Se desejar ocultar uma rede do site público, basta deixar o campo em branco ou clicar no botão <em>"Limpar"</em>.
		</div>
	</div>

	<?php if(!empty($success_message)): ?>
	<div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
		<div class="d-flex align-items-center">
			<i class="bi bi-check-circle-fill fs-5 me-2"></i>
			<div><?php echo $success_message; ?></div>
		</div>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
	<?php endif; ?>

	<form action="" method="post" id="formSocialMedia">
		<input type="hidden" name="form1" value="1">

		<!-- Abas de Navegação das Redes Sociais -->
		<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
			<div class="card-header p-0 border-bottom">
				<ul class="nav nav-tabs ps-tabs-scrollable" id="socialTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active fw-semibold" id="tabMain-btn" data-bs-toggle="tab" data-bs-target="#tab_main" type="button" role="tab" aria-controls="tab_main" aria-selected="true">
							<i class="bi bi-star-fill text-warning me-1"></i> Principais & Comunicação <span class="badge bg-primary-subtle text-primary ms-1">6</span>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link fw-semibold" id="tabSecondary-btn" data-bs-toggle="tab" data-bs-target="#tab_secondary" type="button" role="tab" aria-controls="tab_secondary" aria-selected="false">
							<i class="bi bi-people-fill text-info me-1"></i> Comunidades & Imagens <span class="badge bg-secondary-subtle text-secondary ms-1">6</span>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link fw-semibold" id="tabLegacy-btn" data-bs-toggle="tab" data-bs-target="#tab_legacy" type="button" role="tab" aria-controls="tab_legacy" aria-selected="false">
							<i class="bi bi-archive-fill text-secondary me-1"></i> Plataformas Legadas <span class="badge bg-secondary-subtle text-secondary ms-1">4</span>
						</button>
					</li>
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="socialTabsContent">

					<!-- ABA 1: Redes Principais -->
					<div class="tab-pane fade show active" id="tab_main" role="tabpanel" aria-labelledby="tabMain-btn">
						<div class="row g-4">
							<?php
							foreach (['whatsapp', 'instagram', 'facebook', 'youtube', 'linkedin', 'twitter'] as $key):
								if(!isset($socials[$key])) continue;
								$s = $socials[$key];
								$meta = $meta_social[$key];
								$isActive = !empty($s['url']);
							?>
							<div class="col-md-6 col-xl-4">
								<div class="card h-100 border shadow-xs social-card" data-key="<?php echo $key; ?>" style="transition: all 0.2s ease;">
									<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
										<div class="d-flex align-items-center gap-2">
											<div class="rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: <?php echo $meta['bg_subtle']; ?>; color: <?php echo $meta['color']; ?>;">
												<i class="<?php echo $meta['icon_class']; ?> fs-5"></i>
											</div>
											<span class="fw-bold text-body"><?php echo $s['name']; ?></span>
										</div>
										<span class="badge social-badge <?php echo $isActive ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-body-tertiary border'; ?> rounded-pill small">
											<?php echo $isActive ? '● Ativa' : '○ Oculta'; ?>
										</span>
									</div>
									<div class="card-body p-3">
										<label class="form-label small text-muted mb-1"><?php echo $meta['hint']; ?></label>
										<div class="input-group input-group-sm mb-2">
											<span class="input-group-text"><i class="<?php echo $meta['icon_class']; ?>" style="color: <?php echo $meta['color']; ?>;"></i></span>
											<input type="text" class="form-control social-input" name="<?php echo $key; ?>" id="input_<?php echo $key; ?>" value="<?php echo htmlspecialchars($s['url']); ?>" placeholder="<?php echo $meta['placeholder']; ?>" autocomplete="off">
										</div>
										<div class="d-flex align-items-center justify-content-between gap-1">
											<a href="<?php echo $isActive ? htmlspecialchars($s['url']) : '#'; ?>" target="_blank" class="btn btn-outline-secondary btn-xs btn-test-link <?php echo $isActive ? '' : 'disabled'; ?>" id="test_<?php echo $key; ?>" title="Testar link em nova aba">
												<i class="bi bi-box-arrow-up-right me-1"></i>Testar Link
											</a>
											<button type="button" class="btn btn-light btn-xs border text-muted btn-clear-input" data-target="input_<?php echo $key; ?>" title="Limpar URL">
												<i class="bi bi-x-lg me-1"></i>Limpar
											</button>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- ABA 2: Demais Redes Sociais -->
					<div class="tab-pane fade" id="tab_secondary" role="tabpanel" aria-labelledby="tabSecondary-btn">
						<div class="row g-4">
							<?php
							foreach (['pinterest', 'reddit', 'snapchat', 'tumblr', 'flickr', 'quora'] as $key):
								if(!isset($socials[$key])) continue;
								$s = $socials[$key];
								$meta = $meta_social[$key];
								$isActive = !empty($s['url']);
							?>
							<div class="col-md-6 col-xl-4">
								<div class="card h-100 border shadow-xs social-card" data-key="<?php echo $key; ?>" style="transition: all 0.2s ease;">
									<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
										<div class="d-flex align-items-center gap-2">
											<div class="rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: <?php echo $meta['bg_subtle']; ?>; color: <?php echo $meta['color']; ?>;">
												<i class="<?php echo $meta['icon_class']; ?> fs-5"></i>
											</div>
											<span class="fw-bold text-body"><?php echo $s['name']; ?></span>
										</div>
										<span class="badge social-badge <?php echo $isActive ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-body-tertiary border'; ?> rounded-pill small">
											<?php echo $isActive ? '● Ativa' : '○ Oculta'; ?>
										</span>
									</div>
									<div class="card-body p-3">
										<label class="form-label small text-muted mb-1"><?php echo $meta['hint']; ?></label>
										<div class="input-group input-group-sm mb-2">
											<span class="input-group-text"><i class="<?php echo $meta['icon_class']; ?>" style="color: <?php echo $meta['color']; ?>;"></i></span>
											<input type="text" class="form-control social-input" name="<?php echo $key; ?>" id="input_<?php echo $key; ?>" value="<?php echo htmlspecialchars($s['url']); ?>" placeholder="<?php echo $meta['placeholder']; ?>" autocomplete="off">
										</div>
										<div class="d-flex align-items-center justify-content-between gap-1">
											<a href="<?php echo $isActive ? htmlspecialchars($s['url']) : '#'; ?>" target="_blank" class="btn btn-outline-secondary btn-xs btn-test-link <?php echo $isActive ? '' : 'disabled'; ?>" id="test_<?php echo $key; ?>" title="Testar link em nova aba">
												<i class="bi bi-box-arrow-up-right me-1"></i>Testar Link
											</a>
											<button type="button" class="btn btn-light btn-xs border text-muted btn-clear-input" data-target="input_<?php echo $key; ?>" title="Limpar URL">
												<i class="bi bi-x-lg me-1"></i>Limpar
											</button>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- ABA 3: Plataformas Legadas -->
					<div class="tab-pane fade" id="tab_legacy" role="tabpanel" aria-labelledby="tabLegacy-btn">
						<div class="alert alert-secondary small mb-3 border-0">
							<i class="bi bi-info-circle me-1"></i> Estas plataformas são mantidas para compatibilidade legada com instalações anteriores do sistema. Caso sua clínica não utilize, mantenha os campos em branco.
						</div>
						<div class="row g-4">
							<?php
							foreach (['googleplus', 'stumbleupon', 'delicious', 'digg'] as $key):
								if(!isset($socials[$key])) continue;
								$s = $socials[$key];
								$meta = $meta_social[$key];
								$isActive = !empty($s['url']);
							?>
							<div class="col-md-6 col-xl-3">
								<div class="card h-100 border shadow-xs social-card" data-key="<?php echo $key; ?>" style="transition: all 0.2s ease;">
									<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
										<div class="d-flex align-items-center gap-2">
											<div class="rounded d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: <?php echo $meta['bg_subtle']; ?>; color: <?php echo $meta['color']; ?>;">
												<i class="<?php echo $meta['icon_class']; ?>"></i>
											</div>
											<span class="fw-bold small text-body"><?php echo $s['name']; ?></span>
										</div>
										<span class="badge social-badge <?php echo $isActive ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-body-tertiary border'; ?> rounded-pill small">
											<?php echo $isActive ? '● Ativa' : '○ Oculta'; ?>
										</span>
									</div>
									<div class="card-body p-3">
										<div class="input-group input-group-sm mb-2">
											<span class="input-group-text"><i class="<?php echo $meta['icon_class']; ?>" style="color: <?php echo $meta['color']; ?>;"></i></span>
											<input type="text" class="form-control social-input" name="<?php echo $key; ?>" id="input_<?php echo $key; ?>" value="<?php echo htmlspecialchars($s['url']); ?>" placeholder="<?php echo $meta['placeholder']; ?>" autocomplete="off">
										</div>
										<div class="d-flex align-items-center justify-content-between gap-1">
											<a href="<?php echo $isActive ? htmlspecialchars($s['url']) : '#'; ?>" target="_blank" class="btn btn-outline-secondary btn-xs btn-test-link <?php echo $isActive ? '' : 'disabled'; ?>" id="test_<?php echo $key; ?>">
												<i class="bi bi-box-arrow-up-right me-1"></i>Testar
											</a>
											<button type="button" class="btn btn-light btn-xs border text-muted btn-clear-input" data-target="input_<?php echo $key; ?>">
												<i class="bi bi-x-lg me-1"></i>Limpar
											</button>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>

				</div>
			</div>

			<!-- Rodapé de Ações Fixo / Integrado -->
			<div class="card-footer bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-top">
				<div class="d-flex align-items-center gap-2">
					<button type="button" class="btn btn-outline-danger btn-sm" id="btnResetAll">
						<i class="bi bi-trash3 me-1"></i> Limpar Todas as Redes
					</button>
				</div>
				<div class="d-flex align-items-center gap-2">
					<button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSaveSocial" name="form1">
						<i class="bi bi-check-circle-fill me-1"></i> Salvar Redes Sociais
					</button>
				</div>
			</div>
		</div>

	</form>

</div>
</div>

<style>
.btn-xs {
	padding: 0.2rem 0.45rem;
	font-size: 0.75rem;
	border-radius: 0.25rem;
}
.social-card:hover {
	border-color: var(--bs-primary) !important;
	box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('formSocialMedia');
    var btnSave = document.getElementById('btnSaveSocial');
    var btnReset = document.getElementById('btnResetAll');
    var metricActive = document.getElementById('metricActive');
    var metricInactive = document.getElementById('metricInactive');
    var totalSocials = <?php echo $total_networks; ?>;

    // Atualiza badges e métricas em tempo real enquanto o usuário digita
    function updateMetrics() {
        var activeCount = 0;
        document.querySelectorAll('.social-input').forEach(function(input) {
            var val = input.value.trim();
            var card = input.closest('.social-card');
            var badge = card.querySelector('.social-badge');
            var testBtn = card.querySelector('.btn-test-link');

            if (val !== '') {
                activeCount++;
                badge.className = 'badge social-badge bg-success-subtle text-success border border-success-subtle rounded-pill small';
                badge.textContent = '● Ativa';
                if (testBtn) {
                    testBtn.classList.remove('disabled');
                    testBtn.setAttribute('href', val);
                }
            } else {
                badge.className = 'badge social-badge bg-secondary-subtle text-body-tertiary border rounded-pill small';
                badge.textContent = '○ Oculta';
                if (testBtn) {
                    testBtn.classList.add('disabled');
                    testBtn.setAttribute('href', '#');
                }
            }
        });

        if (metricActive) metricActive.textContent = activeCount + ' Redes';
        if (metricInactive) metricInactive.textContent = (totalSocials - activeCount) + ' Redes';
    }

    // Monitora eventos de digitação
    document.querySelectorAll('.social-input').forEach(function(input) {
        input.addEventListener('input', updateMetrics);
    });

    // Botões individuais de limpar
    document.querySelectorAll('.btn-clear-input').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var targetInput = document.getElementById(targetId);
            if (targetInput) {
                targetInput.value = '';
                updateMetrics();
                if (typeof toastr !== 'undefined') {
                    toastr.info('Campo limpo.');
                }
            }
        });
    });

    // Botão Limpar Tudo com SweetAlert2
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            Swal.fire({
                title: 'Limpar todas as redes?',
                text: 'Esta ação apagará todos os links de redes sociais cadastrados. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, limpar todas',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.querySelectorAll('.social-input').forEach(function(input) {
                        input.value = '';
                    });
                    updateMetrics();
                    if (typeof toastr !== 'undefined') {
                        toastr.info('Campos limpos. Clique em "Salvar Redes Sociais" para confirmar no portal.', 'Campos Limpos');
                    }
                }
            });
        });
    }

    // Submissão assíncrona inteligente via AJAX
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var originalBtnHtml = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';

            var formData = new FormData(form);
            formData.append('ajax', '1');

            fetch('social-media.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                btnSave.disabled = false;
                btnSave.innerHTML = originalBtnHtml;

                if (data.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'Redes sociais salvas com sucesso!', 'Pronto Saúde');
                    }
                    updateMetrics();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Não foi possível salvar as redes sociais.', 'Atenção');
                    }
                }
            })
            .catch(function(err) {
                btnSave.disabled = false;
                btnSave.innerHTML = originalBtnHtml;
                if (typeof toastr !== 'undefined') {
                    toastr.error('Erro na comunicação com o servidor ao salvar redes sociais.', 'Falha de Conexão');
                }
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>