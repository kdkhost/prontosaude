<?php
require_once('header.php');

// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: ' . BASE_URL);
	exit;
}

// Getting the detailed data of a page from page slug
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? AND status=?");
$statement->execute(array($slug, 'Active'));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row)
{
	header('location: ' . BASE_URL);
	exit;
}

$page_name    = $row['page_name'] ?? '';
$page_slug    = $row['page_slug'] ?? '';
$page_content = $row['page_content'] ?? '';
$page_layout  = $row['page_layout'] ?? '';
$banner       = $row['banner'] ?? '';
$status       = $row['status'] ?? '';
?>
		
<!-- Banner Start -->
<?php
$page_banner_src = (!empty($banner) && file_exists(__DIR__ . '/assets/uploads/' . $banner)) ? BASE_URL . 'assets/uploads/' . $banner : BASE_URL . 'assets/uploads/department-1.png';
?>
<div class="page-banner" style="background-image: url(<?php echo $page_banner_src; ?>)">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($page_name); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- ==============================================================================
     1. FULL WIDTH PAGE LAYOUT (SOBRE, PRIVACIDADE, TERMOS, ETC.)
     ============================================================================== -->
<?php if($page_layout == 'Full Width Page Layout'): ?>
<section class="about-v2" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div style="background: #ffffff; border-radius: 16px; padding: 36px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; font-size: 15.5px; line-height: 1.85; color: #334155;">
					<?php echo $page_content; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     2. CONTACT US PAGE LAYOUT (PÁGINA DEDICADA DE CONTATO MULTIPAGE)
     ============================================================================== -->
<?php if($page_layout == 'Contact Us Page Layout'): ?>
<?php
	$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) 
	{
		$contact_map_iframe = $row['contact_map_iframe'];
	}

	// After form submit checking everything for email sending
	if(isset($_POST['form_contact']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'))
	{
		$error_message = '';
		$success_message = '';
		$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) 
		{
			$receive_email = $row['receive_email'];
			$receive_email_subject = $row['receive_email_subject'];
			$receive_email_thank_you_message = $row['receive_email_thank_you_message'];
			$recaptcha_status = $row['recaptcha_status'] ?? 0;
			$recaptcha_secret_key = $row['recaptcha_secret_key'] ?? '';
		}

		$valid = 1;

		// 1. Proteção Honeypot Anti-Spam
		if(!empty($_POST['website_hp'])) {
			$valid = 0;
			$error_message .= 'Envio bloqueado por suspeita de automação maliciosa.<br>';
		}

		if(empty($_POST['visitor_name']))
		{
			$valid = 0;
			$error_message .= 'Por favor, insira seu nome.<br>';
		}

		if(empty($_POST['visitor_phone']))
		{
			$valid = 0;
			$error_message .= 'Por favor, digite seu número de telefone.<br>';
		}

		if(empty($_POST['visitor_email']))
		{
			$valid = 0;
			$error_message .= 'Por favor, indique o seu endereço de e-mail.<br>';
		}
		else
		{
			if(!filter_var($_POST['visitor_email'], FILTER_VALIDATE_EMAIL))
			{
				$valid = 0;
				$error_message .= 'Por favor insira um endereço de e-mail válido.<br>';
			}
		}

		if(empty($_POST['visitor_comment']))
		{
			$valid = 0;
			$error_message .= 'Por favor, insira seu comentário.<br>';
		}

		// 2. Validação do Google reCAPTCHA se ativado no painel
		if($valid == 1 && !empty($recaptcha_status) && !empty($recaptcha_secret_key)) {
			$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
			if(empty($recaptcha_response)) {
				$valid = 0;
				$error_message .= 'Por favor, confirme que você não é um robô resolvendo o reCAPTCHA.<br>';
			} else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
					'secret'   => $recaptcha_secret_key,
					'response' => $recaptcha_response,
					'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
				]));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 6);
				$verify_response = curl_exec($ch);
				curl_close($ch);

				$captcha_obj = json_decode($verify_response);
				if(!$captcha_obj || empty($captcha_obj->success)) {
					$valid = 0;
					$error_message .= 'Falha na verificação de segurança do reCAPTCHA. Por favor, tente novamente.<br>';
				}
			}
		}

		if($valid == 1)
		{
			$to_admin = $receive_email;
			$subject = $receive_email_subject ?: 'Novo Contato Recebido via Site - Pronto Saúde';
			$message = '
	<html><body>
	<table style="border-collapse:collapse; width:100%; font-family:Arial, sans-serif;">
	<tr style="background:#0a6b74; color:#fff;"><th colspan="2" style="padding:12px; text-align:left;">Novo Contato pelo Site</th></tr>
	<tr><td style="padding:8px; border:1px solid #ddd; font-weight:bold; width:30%;">Nome:</td><td style="padding:8px; border:1px solid #ddd;">'.htmlspecialchars($_POST['visitor_name']).'</td></tr>
	<tr><td style="padding:8px; border:1px solid #ddd; font-weight:bold;">E-mail:</td><td style="padding:8px; border:1px solid #ddd;">'.htmlspecialchars($_POST['visitor_email']).'</td></tr>
	<tr><td style="padding:8px; border:1px solid #ddd; font-weight:bold;">Telefone/WhatsApp:</td><td style="padding:8px; border:1px solid #ddd;">'.htmlspecialchars($_POST['visitor_phone']).'</td></tr>
	<tr><td style="padding:8px; border:1px solid #ddd; font-weight:bold;">Mensagem:</td><td style="padding:8px; border:1px solid #ddd;">'.nl2br(htmlspecialchars($_POST['visitor_comment'])).'</td></tr>
	</table>
	</body></html>
	';
			$visitor_email = $_POST['visitor_email'];
			$headers = 'From: ' . $visitor_email . "\r\n" .
					   'Reply-To: ' . $visitor_email . "\r\n" .
					   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
					   "MIME-Version: 1.0\r\n" . 
					   "Content-Type: text/html; charset=UTF-8\r\n";

			@mail($to_admin, $subject, $message, $headers); 
			$success_message = $receive_email_thank_you_message ?: 'Obrigado por entrar em contato! Sua mensagem foi enviada com sucesso e retornaremos em breve.';
		}

		// Resposta JSON para submissões via AJAX
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			header('Content-Type: application/json; charset=utf-8');
			if(!empty($error_message)) {
				echo json_encode(['success' => false, 'message' => strip_tags(str_replace('<br>', "\n", $error_message))]);
			} else {
				echo json_encode(['success' => true, 'message' => $success_message]);
			}
			exit;
		}
	}
?>
<section class="contact-v1" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row mb-4">
			<div class="col-md-12 text-center">
				<span class="badge rounded-pill px-3 py-1 text-white fw-bold mb-2" style="background: var(--ps-primary); font-size: 0.76rem;">
					<i class="fas fa-headset me-1"></i> FALE CONOSCO
				</span>
				<h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">Agende Sua Consulta ou Tire Dúvidas</h2>
				<p class="text-muted small mb-0">Nossa equipe de recepção está pronta para acolher você com agilidade e cordialidade.</p>
			</div>
		</div>

		<div class="row g-4 align-items-start">
			<div class="col-lg-7">
				<div style="background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
					<h4 class="fw-bold mb-2" style="color: var(--ps-title);">Envie Sua Mensagem</h4>
					<p class="text-muted small mb-4">Preencha o formulário e nossa recepção responderá prontamente:</p>
					
					<?php
					if(!empty($error_message))
					{
						echo '<div class="alert alert-danger mb-3">' . $error_message . '</div>';
					} 
					if(!empty($success_message))
					{
						echo '<div class="alert alert-success mb-3">' . $success_message . '</div>';
					}
					?>

					<form action="<?php echo BASE_URL; ?>page/<?php echo $_REQUEST['slug']; ?>" class="form-horizontal cform-1" id="formContatoSite" method="post">
						<!-- Campo Honeypot Invisível Anti-Spam -->
						<input type="text" name="website_hp" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">

						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label small text-muted fw-semibold mb-1">Seu Nome Completo *</label>
								<input type="text" class="form-control" placeholder="Digite seu nome completo" name="visitor_name" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
							<div class="col-md-6">
								<label class="form-label small text-muted fw-semibold mb-1">Telefone / WhatsApp *</label>
								<input type="text" class="form-control" placeholder="(21) 99999-9999" name="visitor_phone" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
							<div class="col-12">
								<label class="form-label small text-muted fw-semibold mb-1">Seu E-mail *</label>
								<input type="email" class="form-control" placeholder="seu@email.com" name="visitor_email" required style="border-radius: 8px; padding: 12px 14px;">
							</div>
							<div class="col-12">
								<label class="form-label small text-muted fw-semibold mb-1">Mensagem ou Especialidade Desejada *</label>
								<textarea name="visitor_comment" class="form-control" rows="4" placeholder="Informe a consulta, exame ou procedimento médico/odontológico desejado..." required style="border-radius: 8px; padding: 12px 14px;"></textarea>
							</div>

							<?php if(!empty($recaptcha_status) && !empty($recaptcha_site_key)): ?>
							<div class="col-12">
								<div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_site_key); ?>"></div>
								<script src="https://www.google.com/recaptcha/api.js" async defer></script>
							</div>
							<?php endif; ?>

							<div class="col-12 mt-2">
								<button type="submit" class="btn-foco-primary w-100 justify-content-center py-3" name="form_contact" style="font-size: 0.95rem;">
									<i class="fas fa-paper-plane me-2"></i> ENVIAR MENSAGEM
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<div class="col-lg-5">
				<!-- Canais de Contato Direto -->
				<div style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 24px;">
					<h5 class="fw-bold mb-3" style="color: var(--ps-title);">Canais de Atendimento</h5>
					<div class="d-flex flex-column gap-2">
						<?php if(!empty($contact_phone)): ?>
						<div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
							<div class="text-white rounded-3 p-2 fs-5 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 42px; height: 42px;">
								<i class="fas fa-phone-alt"></i>
							</div>
							<div>
								<span class="text-muted" style="font-size: 0.75rem;">Telefone da Recepção</span>
								<h6 class="fw-bold mb-0 text-dark small">
									<a href="tel:+55<?= preg_replace('/[^0-9]/', '', $contact_phone); ?>" class="text-decoration-none" style="color: inherit;">
										<?= htmlspecialchars($contact_phone); ?>
									</a>
								</h6>
							</div>
						</div>
						<?php endif; ?>

						<div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
							<div class="text-white rounded-3 p-2 fs-5 d-flex align-items-center justify-content-center" style="background: #10b981; width: 42px; height: 42px;">
								<i class="fab fa-whatsapp"></i>
							</div>
							<div>
								<span class="text-muted" style="font-size: 0.75rem;">WhatsApp Oficial</span>
								<h6 class="fw-bold mb-0 text-dark small">
									<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de falar com a recepção da Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-success fw-bold">
										Agendamento em 1 Minuto
									</a>
								</h6>
							</div>
						</div>

						<?php if(!empty($contact_email)): ?>
						<div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
							<div class="text-white rounded-3 p-2 fs-5 d-flex align-items-center justify-content-center" style="background: var(--ps-primary-dark); width: 42px; height: 42px;">
								<i class="fas fa-envelope"></i>
							</div>
							<div>
								<span class="text-muted" style="font-size: 0.75rem;">E-mail Institucional</span>
								<h6 class="fw-bold mb-0 text-dark small">
									<a href="mailto:<?= htmlspecialchars($contact_email); ?>" class="text-decoration-none" style="color: inherit;">
										<?= htmlspecialchars($contact_email); ?>
									</a>
								</h6>
							</div>
						</div>
						<?php endif; ?>

						<?php if(!empty($contact_address)): ?>
						<div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
							<div class="text-white rounded-3 p-2 fs-5 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 42px; height: 42px;">
								<i class="fas fa-map-marker-alt"></i>
							</div>
							<div>
								<span class="text-muted" style="font-size: 0.75rem;">Endereço da Unidade</span>
								<h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($contact_address); ?></h6>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Card do Mapa Google Maps -->
				<?php if(!empty($contact_map_iframe)): ?>
				<div style="background: #fff; border-radius: 16px; padding: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
					<h6 class="fw-bold mb-2" style="color: var(--ps-title);"><i class="fas fa-map-marked-alt me-1" style="color: var(--ps-primary);"></i> Como Chegar</h6>
					<div class="google-map" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; height: 200px;">
						<?php echo $contact_map_iframe; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     3. FAQ PAGE LAYOUT (PERGUNTAS FREQUENTES POR CATEGORIA)
     ============================================================================== -->
<?php if($page_layout == 'FAQ Page Layout'): ?>
<section class="faq" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php
				$i=0;
				$j=0;
				$statement = $pdo->prepare("SELECT * FROM tbl_faq_category ORDER BY faq_category_id ASC");
				$statement->execute();
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
				foreach ($result as $row) {
					$i++;
					$statement1 = $pdo->prepare("SELECT * FROM tbl_faq WHERE faq_category_id=?");
					$statement1->execute(array($row['faq_category_id']));
					$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
					if (empty($result1)) {
						continue;
					}
				?>
				<div style="margin-bottom: 36px;">
					<h3 style="font-size: 22px; font-weight: 800; color: #0a6b74; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-question-circle"></i> <?php echo htmlspecialchars($row['faq_category_name'] ?? ''); ?>
					</h3>
					<div class="accordion accordion-foco" id="accordion<?php echo $i; ?>">
						<?php
						foreach ($result1 as $idx1 => $row1) {
							$j++;
							?>
							<div class="accordion-item mb-2" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
								<h2 class="accordion-header" id="heading<?= $j; ?>">
									<button class="accordion-button <?= $idx1 !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $j; ?>" aria-expanded="<?= $idx1 === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?= $j; ?>">
										<i class="fas fa-question-circle me-2" style="color: var(--ps-primary);"></i> <?php echo htmlspecialchars($row1['faq_title']); ?>
									</button>
								</h2>
								<div id="collapse<?= $j; ?>" class="accordion-collapse collapse <?= $idx1 === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?= $j; ?>" data-bs-parent="#accordion<?php echo $i; ?>">
									<div class="accordion-body" style="padding: 20px; color: #475569; font-size: 15px; line-height: 1.7; background: #f8fafc;">
										<?php echo $row1['faq_content']; ?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				}
				?>				
			</div>			
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     4. PHOTO GALLERY PAGE LAYOUT (GALERIA DE FOTOS MODERNA & RESPONSIVA)
     ============================================================================== -->
<?php if($page_layout == 'Photo Gallery Page Layout'): ?>
<?php
// Carrega todas as fotos ativas com suas categorias
$photos_stmt = $pdo->prepare("
    SELECT t1.photo_id, t1.photo_caption, t1.photo_name, t1.p_category_id,
           t2.p_category_name
    FROM tbl_photo t1
    JOIN tbl_category_photo t2 ON t1.p_category_id = t2.p_category_id
    WHERE (t1.status = 'Active' OR t1.status IS NULL) AND t2.status = 'Active'
    ORDER BY t1.photo_id DESC
");
$photos_stmt->execute();
$gallery_photos = $photos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Carrega categorias ativas
$cat_stmt = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE status='Active' ORDER BY p_category_id ASC");
$cat_stmt->execute();
$gallery_categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Contabiliza fotos por categoria
$total_photos = count($gallery_photos);
$category_counts = [];
foreach ($gallery_photos as $p) {
    $cid = $p['p_category_id'];
    $category_counts[$cid] = ($category_counts[$cid] ?? 0) + 1;
}
?>
<section class="ps-gallery-section">
	<div class="container">
		<!-- Header descritivo da Galeria -->
		<div class="ps-gallery-header">
			<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
				<i class="fas fa-hospital me-1"></i> Infraestrutura Médica
			</span>
			<h2 class="ps-gallery-title"><?= !empty($page_name) ? htmlspecialchars($page_name) : 'Galeria de Fotos'; ?></h2>
			<p class="ps-gallery-subtitle">
				Conheça nossas instalações, consultórios climatizados, recepção acolhedora e equipamentos de ponta preparados para o seu conforto e segurança.
			</p>
			<?php if(!empty(trim(strip_tags($page_content)))): ?>
				<div class="mt-3 text-start bg-white p-3 rounded-3 border border-slate-200 small text-secondary">
					<?= $page_content; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Barra de Filtros Pílula Responsiva -->
		<?php if(!empty($gallery_categories)): ?>
		<div class="ps-gallery-filter-wrap">
			<button type="button" class="ps-gallery-filter-btn active" data-filter="all">
				<i class="fas fa-th-large"></i>
				<span>Todos os Ambientes</span>
				<span class="ps-gallery-count"><?= $total_photos; ?></span>
			</button>
			<?php foreach ($gallery_categories as $cat): ?>
				<?php
				$cat_id = $cat['p_category_id'];
				$cat_count = $category_counts[$cat_id] ?? 0;
				$cat_name = $cat['p_category_name'];
				$cat_lower = mb_strtolower($cat_name, 'UTF-8');
				$icon = 'fas fa-door-open';
				if (strpos($cat_lower, 'recep') !== false) {
					$icon = 'fas fa-concierge-bell';
				} elseif (strpos($cat_lower, 'consult') !== false) {
					$icon = 'fas fa-stethoscope';
				} elseif (strpos($cat_lower, 'triag') !== false) {
					$icon = 'fas fa-user-md';
				} elseif (strpos($cat_lower, 'exam') !== false || strpos($cat_lower, 'laborat') !== false) {
					$icon = 'fas fa-microscope';
				}
				?>
				<button type="button" class="ps-gallery-filter-btn" data-filter="cat-<?= $cat_id; ?>">
					<i class="<?= $icon; ?>"></i>
					<span><?= htmlspecialchars($cat_name); ?></span>
					<span class="ps-gallery-count"><?= $cat_count; ?></span>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Grid de Fotos com Fancybox 5 -->
		<div class="row g-4" id="psGalleryGrid">
			<?php if(!empty($gallery_photos)): ?>
				<?php foreach ($gallery_photos as $row): ?>
					<?php
					$photo_filename = $row['photo_name'];
					$photo_path = BASE_URL . 'assets/uploads/' . $photo_filename;
					$caption = !empty($row['photo_caption']) ? $row['photo_caption'] : $row['p_category_name'] . ' • Pronto Saúde';
					$cat_id = $row['p_category_id'];
					?>
					<div class="col-12 col-sm-6 col-lg-4 ps-gallery-item cat-<?= $cat_id; ?>" data-category="cat-<?= $cat_id; ?>">
						<div class="ps-gallery-card">
							<a href="<?= $photo_path; ?>" 
							   class="ps-gallery-img-link" 
							   data-fancybox="pronto-galeria-cat-<?= $cat_id; ?>" 
							   data-caption="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?>"
							   title="Clique para ampliar em tela cheia">
								
								<span class="ps-gallery-badge-cat">
									<i class="fas fa-tag"></i>
									<?= htmlspecialchars($row['p_category_name']); ?>
								</span>

								<img src="<?= $photo_path; ?>" 
								     alt="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?>" 
								     loading="lazy">

								<div class="ps-gallery-overlay">
									<div class="ps-gallery-zoom-icon">
										<i class="fas fa-search-plus"></i>
									</div>
								</div>
							</a>

							<div class="ps-gallery-footer">
								<h6 class="ps-gallery-caption" title="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?>">
									<?= htmlspecialchars($caption); ?>
								</h6>
								<span class="ps-gallery-sub">
									<i class="fas fa-expand me-1"></i> Ampliar
								</span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="col-12 text-center py-5">
					<div class="p-4 bg-white rounded-4 border border-slate-200 shadow-sm d-inline-block">
						<i class="fas fa-images text-muted fa-3x mb-3"></i>
						<h5 class="fw-bold text-dark">Nenhuma foto encontrada</h5>
						<p class="text-muted small mb-0">Em breve adicionaremos novas imagens da nossa infraestrutura.</p>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Estado Vazio para Filtros sem Fotos -->
		<div id="psGalleryEmpty" class="text-center py-5" style="display: none;">
			<div class="p-4 bg-white rounded-4 border border-slate-200 shadow-sm d-inline-block" style="max-width: 420px;">
				<i class="fas fa-filter text-muted fa-2x mb-2" style="color: var(--ps-accent) !important;"></i>
				<h6 class="fw-bold text-dark mb-1">Nenhuma imagem nesta categoria</h6>
				<p class="text-muted small mb-0">Selecione outro ambiente ou clique em "Todos os Ambientes" para ver todas as fotos.</p>
			</div>
		</div>
	</div>
</section>

<!-- Script de Filtragem Dinâmica Fluida sem mixitup antigo -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.ps-gallery-filter-btn');
    const galleryItems = document.querySelectorAll('.ps-gallery-item');
    const emptyNotice = document.getElementById('psGalleryEmpty');

    filterButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetFilter = this.getAttribute('data-filter');

            filterButtons.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');

            let visibleCount = 0;

            galleryItems.forEach(function (item) {
                const categoryClass = item.getAttribute('data-category');
                if (targetFilter === 'all' || categoryClass === targetFilter) {
                    item.classList.remove('ps-hidden');
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.97)';
                    setTimeout(function () {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 30);
                    visibleCount++;
                } else {
                    item.classList.add('ps-hidden');
                }
            });

            if (emptyNotice) {
                emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });
});
</script>
<?php endif; ?>

<!-- ==============================================================================
     5. VIDEO GALLERY PAGE LAYOUT (MODERNO, RESPONSIVO, 1 VÍDEO POR VEZ)
     ============================================================================== -->
<?php if($page_layout == 'Video Gallery Page Layout'): ?>
<?php
// Carrega vídeos ativos com suas categorias
$videos_stmt = $pdo->prepare("
    SELECT t1.video_id, t1.video_title, t1.video_iframe, t1.v_category_id,
           t2.v_category_name
    FROM tbl_video t1
    JOIN tbl_category_video t2 ON t1.v_category_id = t2.v_category_id
    WHERE (t1.status = 'Active' OR t1.status IS NULL) AND t2.status = 'Active'
    ORDER BY t1.video_id DESC
");
$videos_stmt->execute();
$gallery_videos = $videos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Carrega categorias de vídeo ativas
$vcat_stmt = $pdo->prepare("SELECT * FROM tbl_category_video WHERE status='Active' ORDER BY v_category_id ASC");
$vcat_stmt->execute();
$video_categories = $vcat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagem de vídeos por categoria
$total_videos = count($gallery_videos);
$vcategory_counts = [];
foreach ($gallery_videos as $v) {
    $cid = $v['v_category_id'];
    $vcategory_counts[$cid] = ($vcategory_counts[$cid] ?? 0) + 1;
}
?>
<section class="ps-gallery-section">
	<div class="container">
		<!-- Header descritivo da Galeria de Vídeos -->
		<div class="ps-gallery-header">
			<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
				<i class="fas fa-play-circle me-1"></i> Vídeos &amp; Depoimentos
			</span>
			<h2 class="ps-gallery-title"><?= !empty($page_name) ? htmlspecialchars($page_name) : 'Galeria de Vídeos'; ?></h2>
			<p class="ps-gallery-subtitle">
				Acompanhe histórias reais de superação, depoimentos de nossos pacientes e explicações médicas sobre cuidados e saúde preventiva.
			</p>
			<?php if(!empty(trim(strip_tags($page_content)))): ?>
				<div class="mt-3 text-start bg-white p-3 rounded-3 border border-slate-200 small text-secondary">
					<?= $page_content; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Barra de Filtros Pílula Responsiva -->
		<?php if(!empty($video_categories)): ?>
		<div class="ps-gallery-filter-wrap">
			<button type="button" class="ps-gallery-filter-btn active" data-vfilter="all">
				<i class="fas fa-film"></i>
				<span>Todos os Vídeos</span>
				<span class="ps-gallery-count"><?= $total_videos; ?></span>
			</button>
			<?php foreach ($video_categories as $vcat): ?>
				<?php
				$cat_id = $vcat['v_category_id'];
				$cat_count = $vcategory_counts[$cat_id] ?? 0;
				$cat_name = $vcat['v_category_name'];
				$cat_lower = mb_strtolower($cat_name, 'UTF-8');
				$icon = 'fas fa-video';
				if (strpos($cat_lower, 'depoim') !== false) {
					$icon = 'fas fa-comment-dots';
				} elseif (strpos($cat_lower, 'proced') !== false) {
					$icon = 'fas fa-heartbeat';
				} elseif (strpos($cat_lower, 'instruc') !== false || strpos($cat_lower, 'dica') !== false) {
					$icon = 'fas fa-lightbulb';
				}
				?>
				<button type="button" class="ps-gallery-filter-btn" data-vfilter="vcat-<?= $cat_id; ?>">
					<i class="<?= $icon; ?>"></i>
					<span><?= htmlspecialchars($cat_name); ?></span>
					<span class="ps-gallery-count"><?= $cat_count; ?></span>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Grid de Vídeos com Capa na Frente e Esqueleto Animado -->
		<div class="row g-4" id="psVideoGrid">
			<?php if(!empty($gallery_videos)): ?>
				<?php foreach ($gallery_videos as $idx => $vrow): ?>
					<?php
					$video_raw = $vrow['video_iframe'];
					$is_mp4 = (stripos($video_raw, '.mp4') !== false || stripos($video_raw, 'assets/uploads') !== false);
					$video_src = '';
					$youtube_id = '';
					$cover_img = BASE_URL . 'assets/uploads/department-1.png';

					if ($is_mp4) {
						$clean_name = basename(str_replace('\\', '/', $video_raw));
						$video_src = BASE_URL . 'assets/uploads/videos/' . $clean_name;
					} elseif (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $video_raw, $yt_match)) {
						$youtube_id = $yt_match[1];
						$cover_img = 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
					}

					$v_title = !empty($vrow['video_title']) ? $vrow['video_title'] : $vrow['v_category_name'] . ' • Pronto Saúde';
					$cat_id = $vrow['v_category_id'];
					$card_id = 'ps_video_' . ($vrow['video_id'] ?? $idx);
					?>
					<div class="col-12 col-sm-6 col-lg-4 ps-video-item vcat-<?= $cat_id; ?>" data-vcategory="vcat-<?= $cat_id; ?>">
						<div class="ps-video-card" id="<?= $card_id; ?>">
							<!-- 1. Esqueleto Animado Shimmer (exibido ao atualizar/carregar a página) -->
							<div class="ps-video-skeleton" aria-hidden="true">
								<div class="ps-skeleton-thumb ps-skeleton-shimmer"></div>
								<div class="ps-skeleton-body">
									<div class="ps-skeleton-pill ps-skeleton-shimmer"></div>
									<div class="ps-skeleton-title ps-skeleton-shimmer"></div>
									<div class="ps-skeleton-meta ps-skeleton-shimmer"></div>
								</div>
							</div>

							<!-- 2. Área do Player e Capa na Frente do Box -->
							<div class="ps-video-player-wrap">
								<span class="ps-video-badge-cat">
									<i class="fas fa-tag"></i>
									<?= htmlspecialchars($vrow['v_category_name']); ?>
								</span>

								<!-- Capa na frente do box com Botão de Play Pulsante -->
								<div class="ps-video-cover-facade" 
								     style="background-image: url('<?= $cover_img; ?>');"
								     data-video-src="<?= htmlspecialchars($video_src); ?>"
								     data-yt-id="<?= htmlspecialchars($youtube_id); ?>"
								     data-is-mp4="<?= $is_mp4 ? '1' : '0'; ?>"
								     title="Clique para reproduzir: <?= htmlspecialchars($v_title, ENT_QUOTES, 'UTF-8'); ?>">
									<div class="ps-video-cover-overlay"></div>
									
									<div class="ps-video-play-btn">
										<span class="ps-play-pulse-ring"></span>
										<i class="fas fa-play"></i>
									</div>

									<span class="ps-video-tag-hd">
										<i class="fas fa-play-circle me-1 text-success"></i> Assistir Vídeo
									</span>
								</div>

								<!-- Contêiner do Vídeo HTML5 ou Iframe (Carregado instantaneamente ao clicar) -->
								<div class="ps-video-embed-target w-100 h-100 d-flex align-items-center justify-content-center">
									<?php if($is_mp4 && !empty($video_src)): ?>
										<video controls preload="metadata" playsinline class="w-100 h-100" style="display:none;" poster="<?= $cover_img; ?>">
											<source src="<?= $video_src; ?>" type="video/mp4">
											Seu navegador não suporta reprodução de vídeo HTML5.
										</video>
									<?php elseif(!empty($youtube_id)): ?>
										<div class="ps-yt-placeholder w-100 h-100"></div>
									<?php else: ?>
										<div class="ps-raw-video-wrap w-100 h-100">
											<?= $video_raw; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<!-- 3. Corpo do Card com Identificação e Status -->
							<div class="ps-video-body">
								<h5 class="ps-video-title" title="<?= htmlspecialchars($v_title, ENT_QUOTES, 'UTF-8'); ?>">
									<?= htmlspecialchars($v_title); ?>
								</h5>
								<div class="ps-video-footer">
									<span class="ps-video-status">
										<i class="fas fa-circle-play text-success"></i> Pronto Saúde
									</span>
									<span class="small text-muted">
										<i class="fas fa-volume-up me-1 text-primary"></i> Áudio Ativo
									</span>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="col-12 text-center py-5">
					<div class="p-4 bg-white rounded-4 border border-slate-200 shadow-sm d-inline-block">
						<i class="fas fa-film text-muted fa-3x mb-3"></i>
						<h5 class="fw-bold text-dark">Nenhum vídeo disponível</h5>
						<p class="text-muted small mb-0">Em breve disponibilizaremos novos vídeos e depoimentos.</p>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Estado Vazio para Filtros sem Vídeos -->
		<div id="psVideoEmpty" class="text-center py-5" style="display: none;">
			<div class="p-4 bg-white rounded-4 border border-slate-200 shadow-sm d-inline-block" style="max-width: 420px;">
				<i class="fas fa-video-slash text-muted fa-2x mb-2" style="color: var(--ps-accent) !important;"></i>
				<h6 class="fw-bold text-dark mb-1">Nenhum vídeo nesta categoria</h6>
				<p class="text-muted small mb-0">Selecione outra categoria ou clique em "Todos os Vídeos".</p>
			</div>
		</div>
	</div>
</section>

<!-- Script de Controle de Vídeo Único, Capas Dinâmicas, Esqueleto e Filtros -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. ANIMAÇÃO DE ESQUELETO AO ATUALIZAR / CARREGAR A PÁGINA
    const videoCards = document.querySelectorAll('.ps-video-card');
    setTimeout(function () {
        videoCards.forEach(function (card, index) {
            setTimeout(function () {
                card.classList.add('ps-skeleton-loaded');
            }, index * 90);
        });
    }, 380);

    // 2. EXTRAÇÃO DE PRIMEIRO FRAME VIA CANVAS PARA CAPAS DE VÍDEOS MP4 LOCAIS
    const facades = document.querySelectorAll('.ps-video-cover-facade');
    facades.forEach(function (facade) {
        const isMp4 = facade.getAttribute('data-is-mp4') === '1';
        const vSrc = facade.getAttribute('data-video-src');
        if (isMp4 && vSrc) {
            try {
                const tempVideo = document.createElement('video');
                tempVideo.src = vSrc;
                tempVideo.crossOrigin = 'anonymous';
                tempVideo.muted = true;
                tempVideo.preload = 'metadata';
                tempVideo.currentTime = 0.6; // meio segundo para garantir quadro nítido do depoimento

                tempVideo.addEventListener('seeked', function () {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = tempVideo.videoWidth || 640;
                        canvas.height = tempVideo.videoHeight || 360;
                        const ctx = canvas.getContext('2d');
                        if (ctx && canvas.width > 0 && canvas.height > 0) {
                            ctx.drawImage(tempVideo, 0, 0, canvas.width, canvas.height);
                            const posterUrl = canvas.toDataURL('image/jpeg', 0.85);
                            if (posterUrl && posterUrl.length > 100) {
                                facade.style.backgroundImage = 'url(' + posterUrl + ')';
                                const cardVideo = facade.closest('.ps-video-player-wrap').querySelector('video');
                                if (cardVideo) cardVideo.poster = posterUrl;
                            }
                        }
                    } catch (err) {
                        // Mantém a capa estilizada com gradiente e logo caso CORS de terceiros restrinja o canvas
                    }
                }, { once: true });
            } catch (e) {}
        }
    });

    // 3. REPRODUÇÃO INSTANTÂNEA AO CLICAR NA CAPA DO BOX COM 1 VÍDEO POR VEZ
    facades.forEach(function (facade) {
        facade.addEventListener('click', function () {
            const wrap = this.closest('.ps-video-player-wrap');
            if (!wrap) return;

            // Pausa qualquer outro vídeo HTML5 em execução
            const allVideos = document.querySelectorAll('video');
            allVideos.forEach(function (v) {
                if (!v.paused) v.pause();
            });

            // Reseta outras capas do YouTube se houver
            document.querySelectorAll('.ps-video-cover-facade.is-hidden').forEach(function (f) {
                // Mantém escondida apenas a que está tocando agora
            });

            const isMp4 = this.getAttribute('data-is-mp4') === '1';
            const ytId = this.getAttribute('data-yt-id');

            // Esconde a capa da frente suavemente
            this.classList.add('is-hidden');

            if (isMp4) {
                const videoEl = wrap.querySelector('video');
                if (videoEl) {
                    videoEl.style.display = 'block';
                    videoEl.play().catch(function () {});
                }
            } else if (ytId) {
                const target = wrap.querySelector('.ps-video-embed-target');
                if (target) {
                    target.innerHTML = '<iframe class="w-100 h-100" src="https://www.youtube.com/embed/' + encodeURIComponent(ytId) + '?autoplay=1&rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                }
            }
        });
    });

    // 4. REGRA CRÍTICA: Se algum vídeo iniciar reprodução via controles nativos, pausa os demais
    document.addEventListener('play', function (e) {
        if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'video') {
            const allVideos = document.querySelectorAll('video');
            allVideos.forEach(function (v) {
                if (v !== e.target && !v.paused) {
                    v.pause();
                }
            });
        }
    }, true);

    // 5. FILTROS DINÂMICOS DE VÍDEO COM ANIMAÇÃO
    const vfilterButtons = document.querySelectorAll('.ps-gallery-filter-btn[data-vfilter]');
    const videoItems = document.querySelectorAll('.ps-video-item');
    const emptyNotice = document.getElementById('psVideoEmpty');

    vfilterButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetFilter = this.getAttribute('data-vfilter');

            // Pausa qualquer vídeo ativo ao trocar de categoria
            const allVideos = document.querySelectorAll('video');
            allVideos.forEach(function (v) {
                if (!v.paused) v.pause();
            });

            vfilterButtons.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');

            let visibleCount = 0;

            videoItems.forEach(function (item) {
                const categoryClass = item.getAttribute('data-vcategory');
                if (targetFilter === 'all' || categoryClass === targetFilter) {
                    item.classList.remove('ps-hidden');
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.97)';
                    setTimeout(function () {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 40);
                    visibleCount++;
                } else {
                    item.classList.add('ps-hidden');
                }
            });

            if (emptyNotice) {
                emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });
});
</script>
<?php endif; ?>

<!-- ==============================================================================
     6. BLOG / NOTÍCIAS PAGE LAYOUT
     ============================================================================== -->
<?php if($page_layout == 'Blog Page Layout'): ?>
<section class="blog" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-9">
				<div class="blog-grid">
					<div class="row g-4">
						<div class="col-md-12">
							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC");
							$statement->execute();
							$total = $statement->rowCount();
							?>

							<?php if(!$total): ?>
							<p class="text-muted">Nenhuma notícia encontrada no momento.</p>
							<?php else: ?>

							<?php
							$adjacents = 10;	
							$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC");
							$statement->execute();
							$total_pages = $statement->rowCount();
							
							$targetpage = $_SERVER['PHP_SELF'];
							$limit = 5;                                 
							$page = @$_GET['page'];
							if($page) 
								$start = ($page - 1) * $limit;          
							else
								$start = 0;	

							$statement = $pdo->prepare("SELECT
													   t1.news_title,
							                           t1.news_slug,
							                           t1.news_content,
							                           t1.news_date,
							                           t1.photo,
							                           t1.category_id,
							                           t2.category_name,
							                           t2.category_slug
							                           FROM tbl_news t1
							                           JOIN tbl_category t2
							                           ON t1.category_id = t2.category_id 		                           
							                           ORDER BY t1.news_id DESC
							                           LIMIT $start, $limit");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							
							$s1 = $_REQUEST['slug'];
							if ($page == 0) $page = 1;                  
							$prev = $page - 1;                          
							$next = $page + 1;                          
							$lastpage = ceil($total_pages/$limit);      
							$lpm1 = $lastpage - 1;   
							$pagination = "";
							if($lastpage > 1)
							{   
								$pagination .= "<div class=\"pagination\">";
								if ($page > 1) 
									$pagination.= "<a href=\"$targetpage?slug=$s1&page=$prev\">&#171; anterior</a>";
								else
									$pagination.= "<span class=\"disabled\">&#171; anterior</span>";    
								if ($lastpage < 7 + ($adjacents * 2))
								{   
									for ($counter = 1; $counter <= $lastpage; $counter++)
									{
										if ($counter == $page)
											$pagination.= "<span class=\"current\">$counter</span>";
										else
											$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
									}
								}
								elseif($lastpage > 5 + ($adjacents * 2))
								{
									if($page < 1 + ($adjacents * 2))        
									{
										for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
										{
											if ($counter == $page)
												$pagination.= "<span class=\"current\">$counter</span>";
											else
												$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
										}
										$pagination.= "...";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lpm1\">$lpm1</a>";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lastpage\">$lastpage</a>";       
									}
									elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
									{
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=1\">1</a>";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=2\">2</a>";
										$pagination.= "...";
										for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
										{
											if ($counter == $page)
												$pagination.= "<span class=\"current\">$counter</span>";
											else
												$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
										}
										$pagination.= "...";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lpm1\">$lpm1</a>";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=$lastpage\">$lastpage</a>";       
									}
									else
									{
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=1\">1</a>";
										$pagination.= "<a href=\"$targetpage?slug=$s1&page=2\">2</a>";
										$pagination.= "...";
										for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
										{
											if ($counter == $page)
												$pagination.= "<span class=\"current\">$counter</span>";
											else
												$pagination.= "<a href=\"$targetpage?slug=$s1&page=$counter\">$counter</a>";                 
										}
									}
								}
								if ($page < $counter - 1) 
									$pagination.= "<a href=\"$targetpage?slug=$s1&page=$next\">próximo &#187;</a>";
								else
									$pagination.= "<span class=\"disabled\">próximo &#187;</span>";
								$pagination.= "</div>\n";       
							}
							?>

							<?php
							foreach ($result as $row) {
								$n_photo = (!empty($row['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $row['photo']))
									? BASE_URL . 'assets/uploads/' . $row['photo']
									: BASE_URL . 'assets/uploads/department-1.png';
								?>
								<div class="post-item mb-4" style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
									<div class="row align-items-center">
										<div class="col-md-5 mb-3 mb-md-0">
											<div style="border-radius: 12px; overflow: hidden; height: 200px;">
												<img src="<?php echo $n_photo; ?>" alt="<?php echo htmlspecialchars($row['news_title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
											</div>
										</div>
										<div class="col-md-7">
											<span class="badge rounded-pill mb-2 px-3 py-1" style="background: #e6f3f4; color: #0a6b74; font-size: 0.75rem;">
												<i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($row['category_name']); ?>
											</span>
											<h4 class="fw-bold mb-2">
												<a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>" style="color: var(--ps-title); text-decoration: none;">
													<?php echo htmlspecialchars($row['news_title']); ?>
												</a>
											</h4>
											<p class="text-muted small mb-3">
												<?php echo mb_strimwidth(strip_tags($row['news_content']), 0, 140, '...'); ?>
											</p>
											<div class="d-flex align-items-center justify-content-between">
												<span class="text-muted small" style="font-size: 0.75rem;">
													<i class="far fa-calendar-alt me-1"></i> <?php echo htmlspecialchars($row['news_date']); ?>
												</span>
												<a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>" class="btn btn-sm btn-foco-primary py-2 px-3 fw-bold">
													Ler Artigo <i class="fas fa-arrow-right ms-1"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>							
							<?php endif; ?>

						</div>

						<div class="col-md-12">
							<?php if($total): ?>
							<?php echo $pagination; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<?php require_once('sidebar.php'); ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     7. DOCTOR PAGE LAYOUT (CORPO CLÍNICO / MÉDICOS)
     ============================================================================== -->
<?php if($page_layout == 'Doctor Page Layout'): ?>
<section class="doctor-v3" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row g-3">
			<?php
			$statement = $pdo->prepare("SELECT
										t1.id,
										t1.name,
										t1.slug,
										t1.designation_id,
										t1.photo,
										t1.degree,
										t1.detail,
										t1.phone, 
										t1.email,
										t1.status,
										t2.designation_name
			                            FROM tbl_doctor t1
			                            JOIN tbl_designation t2
			                            ON t1.designation_id = t2.designation_id
			                            WHERE t1.status=?
			                            ORDER BY t1.id ASC");
			$statement->execute(array('Active'));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$doc_photo = (!empty($row['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $row['photo']))
					? BASE_URL . 'assets/uploads/' . $row['photo']
					: BASE_URL . 'assets/uploads/department-1.png';
				$doc_link = BASE_URL . 'doctor/' . $row['slug'];
				?>
				<div class="col-xl-3 col-lg-4 col-md-6">
					<div class="card-medico">
						<img src="<?= $doc_photo; ?>" alt="<?= htmlspecialchars($row['name']); ?>" class="card-medico-thumb">
						<div class="card-medico-body">
							<h6 class="card-medico-name">
								<a href="<?= $doc_link; ?>" class="text-decoration-none" style="color: inherit;">
									<?= htmlspecialchars($row['name']); ?>
								</a>
							</h6>
							<span class="card-medico-spec"><?= htmlspecialchars($row['designation_name'] ?? 'Médico Especialista'); ?></span>
							<div class="card-medico-footer">
								<a href="<?= $doc_link; ?>" class="btn btn-sm btn-outline-secondary w-100 fw-bold mb-1">
									<i class="fas fa-user-md me-1"></i> Ver Perfil Completo
								</a>
								<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar consulta com ' . $row['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-foco-primary w-100 fw-bold">
									<i class="fas fa-calendar-check me-1"></i> Agendar Consulta
								</a>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php require_once('footer.php'); ?>
