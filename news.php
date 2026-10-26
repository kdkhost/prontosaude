<?php require_once('header.php'); ?>
		
<?php
// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: '.BASE_URL);
	exit;
}

// Getting the news detailed data from the news slug
$statement = $pdo->prepare("SELECT
							t1.news_id,
							t1.news_title,
							t1.news_slug,
							t1.news_content,
							t1.news_date,
							t1.publisher,
							t1.photo,
							t1.category_id,
							t2.category_name,
							t2.category_slug
                           	FROM tbl_news t1
                           	LEFT JOIN tbl_category t2
                           	ON t1.category_id = t2.category_id
                           	WHERE t1.news_slug=?");
$statement->execute(array($slug));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	header('location: '.BASE_URL);
	exit;
}

$news_id       = (int)($row['news_id'] ?? 0);
$news_title    = $row['news_title'] ?? '';
$news_content  = $row['news_content'] ?? '';
$news_date     = $row['news_date'] ?? '';
$publisher     = $row['publisher'] ?? '';
$photo         = $row['photo'] ?? '';
$category_id   = $row['category_id'] ?? '';
$category_slug = $row['category_slug'] ?? '';
$category_name = $row['category_name'] ?? '';

// Atualização atômica e otimizada da contagem de visualizações
$update_stmt = $pdo->prepare("UPDATE tbl_news SET total_view = total_view + 1 WHERE news_slug=?");
$update_stmt->execute(array($slug));

// Carrega comentários nativos aprovados deste artigo (com autocriação resiliente da tabela)
$comentarios = [];
try {
	$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	if ($driver === 'sqlite') {
		$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_news_comment (
			comment_id INTEGER PRIMARY KEY AUTOINCREMENT,
			news_id INTEGER NOT NULL,
			c_name VARCHAR(150) NOT NULL,
			c_email VARCHAR(150) NOT NULL,
			c_comment TEXT NOT NULL,
			c_date DATETIME NOT NULL,
			c_status INTEGER DEFAULT 1
		)");
	} else {
		$pdo->exec("CREATE TABLE IF NOT EXISTS tbl_news_comment (
			comment_id INT(11) NOT NULL AUTO_INCREMENT,
			news_id INT(11) NOT NULL,
			c_name VARCHAR(150) NOT NULL,
			c_email VARCHAR(150) NOT NULL,
			c_comment TEXT NOT NULL,
			c_date DATETIME NOT NULL,
			c_status TINYINT(1) DEFAULT 1,
			PRIMARY KEY (comment_id),
			KEY idx_news (news_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
	$stC = $pdo->prepare("SELECT * FROM tbl_news_comment WHERE news_id = ? AND c_status = 1 ORDER BY comment_id DESC");
	$stC->execute([$news_id]);
	$comentarios = $stC->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
$total_comentarios = count($comentarios);
?>


<?php
$news_banner_src = (!empty($photo) && file_exists('assets/uploads/' . $photo)) ? BASE_URL . 'assets/uploads/' . $photo : BASE_URL . 'assets/uploads/department-1.png';
?>
<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= $news_banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($news_title); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->


<!-- Blog Start -->
<section class="ps-blog-section">
	<div class="container">
		<div class="row g-4">
			<div class="col-lg-8">
				
				<div class="ps-blog-main-area">
					<!-- Post Item Start -->
					<article class="post-item" style="background: #ffffff; border-radius: 16px; padding: 28px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
						<?php if(!empty($photo)): ?>
							<div class="image-holder" style="border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.08);">
								<img class="img-responsive" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo ?>" alt="<?php echo htmlspecialchars($news_title); ?>" style="width: 100%; max-height: 480px; object-fit: cover;">
							</div>
						<?php endif; ?>
						
						<div class="text">
							<h2 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 14px; line-height: 1.35;">
								<?php echo htmlspecialchars($news_title); ?>
							</h2>
							
							<ul class="status" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 10px; padding: 0; list-style: none;">
								<?php if(!empty($category_name)): ?>
									<li>
										<a href="<?php echo BASE_URL; ?>category/<?php echo $category_slug; ?>" style="background: #e6f3f4; color: #0a6b74; font-weight: 700; font-size: 13px; padding: 5px 14px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
											<i class="fas fa-tag"></i> <?php echo htmlspecialchars($category_name); ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if(!empty($news_date)): ?>
									<li style="background: #f1f5f9; color: #64748b; font-weight: 600; font-size: 13px; padding: 5px 14px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
										<i class="far fa-calendar-alt ps-text-teal"></i> <?php echo pronto_format_date($news_date); ?>
									</li>
								<?php endif; ?>
							</ul>

							<div class="article-content" style="color: #334155; font-size: 16px; line-height: 1.85; margin-bottom: 30px;">
								<?php echo $news_content; ?>
							</div>
							
							<!-- Barra de Compartilhamento em Redes Sociais -->
							<?php
							$art_url = BASE_URL . 'news/' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
							$art_title = htmlspecialchars($news_title, ENT_QUOTES, 'UTF-8');
							$share_msg_wa = urlencode($news_title . "\n\nLeia o artigo completo na Pronto Saúde:\n" . $art_url);
							$share_url_encoded = urlencode($art_url);
							$share_title_encoded = urlencode($news_title);
							?>
							<div class="ps-social-share-wrapper p-3 p-md-4 my-4">
								<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
									<div>
										<span class="d-block text-uppercase fw-bold text-muted small" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gostou deste artigo?</span>
										<h5 class="fw-bold mb-0" style="color: var(--ps-title); font-size: 1.05rem;">
											<i class="fas fa-share-nodes ps-text-teal me-2"></i> Compartilhe com amigos e familiares:
										</h5>
									</div>
									<div class="d-flex flex-wrap align-items-center gap-2">
										<!-- WhatsApp -->
										<a href="https://api.whatsapp.com/send?text=<?= $share_msg_wa; ?>" target="_blank" rel="noopener noreferrer" class="ps-share-btn ps-share-whatsapp" title="Compartilhar no WhatsApp">
											<i class="fab fa-whatsapp fs-6"></i> <span>WhatsApp</span>
										</a>
										<!-- Facebook -->
										<a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url_encoded; ?>" target="_blank" rel="noopener noreferrer" class="ps-share-btn ps-share-facebook" title="Compartilhar no Facebook">
											<i class="fab fa-facebook-f fs-6"></i> <span>Facebook</span>
										</a>
										<!-- X / Twitter -->
										<a href="https://twitter.com/intent/tweet?url=<?= $share_url_encoded; ?>&text=<?= $share_title_encoded; ?>" target="_blank" rel="noopener noreferrer" class="ps-share-btn ps-share-twitter" title="Compartilhar no X (Twitter)">
											<i class="fab fa-x-twitter fs-6"></i> <span>X</span>
										</a>
										<!-- LinkedIn -->
										<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $share_url_encoded; ?>" target="_blank" rel="noopener noreferrer" class="ps-share-btn ps-share-linkedin" title="Compartilhar no LinkedIn">
											<i class="fab fa-linkedin-in fs-6"></i> <span>LinkedIn</span>
										</a>
										<!-- Telegram -->
										<a href="https://t.me/share/url?url=<?= $share_url_encoded; ?>&text=<?= $share_title_encoded; ?>" target="_blank" rel="noopener noreferrer" class="ps-share-btn ps-share-telegram" title="Compartilhar no Telegram">
											<i class="fab fa-telegram-plane fs-6"></i> <span>Telegram</span>
										</a>
										<!-- Copiar Link -->
										<button type="button" class="ps-share-btn ps-share-copy" id="btnCopyNewsUrl" data-url="<?= $art_url; ?>" title="Copiar Link do Artigo">
											<i class="fas fa-link fs-6"></i> <span>Copiar Link</span>
										</button>
									</div>
								</div>
							</div>

							<div class="mt-3">
								<a href="<?= BASE_URL; ?>page/blog" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill text-decoration-none d-inline-flex align-items-center small">
									<i class="fas fa-arrow-left me-2"></i> Voltar para Todas as Notícias
								</a>
							</div>
						</div>
					</article>

					<!-- Card Unificado de Comentários (Sistema Próprio da Clínica + Facebook Comments) -->
					<?php 
						$final_url = BASE_URL . 'news/' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
						$limit_posts = !empty($fb_comment_numposts) ? intval($fb_comment_numposts) : 5;
					?>
					<div class="ps-comments-card">
						<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
							<div>
								<span class="d-block text-uppercase fw-bold text-muted small" style="font-size: 0.72rem; letter-spacing: 0.5px;">Interação e Diálogo</span>
								<h3 class="fw-bold mb-0" style="color: var(--ps-primary); font-size: 1.25rem;">
									<i class="far fa-comments me-2"></i> Comentários &amp; Opiniões
								</h3>
							</div>
							
							<!-- Abas de Alternância: Sistema Nativo vs Facebook -->
							<ul class="nav nav-pills ps-comment-pills" id="commentTabs" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active rounded-pill fw-bold small px-3 py-1" id="tab-local-btn" data-bs-toggle="pill" data-bs-target="#tab-local" type="button" role="tab" aria-selected="true">
										<i class="fas fa-comment-dots me-1"></i> Comentários no Site (<span id="psCommentCountBadge"><?= $total_comentarios; ?></span>)
									</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link rounded-pill fw-bold small px-3 py-1" id="tab-fb-btn" data-bs-toggle="pill" data-bs-target="#tab-fb" type="button" role="tab" aria-selected="false">
										<i class="fab fa-facebook me-1"></i> Facebook
									</button>
								</li>
							</ul>
						</div>

						<div class="tab-content" id="commentTabsContent">
							<!-- Aba 1: Comentários do Próprio Sistema (Ativa por Padrão) -->
							<div class="tab-pane fade show active" id="tab-local" role="tabpanel">
								
								<!-- Formulário de Envio de Comentário -->
								<div class="p-3 p-md-4 rounded-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
									<h5 class="fw-bold mb-3" style="color: var(--ps-title); font-size: 1rem;">
										<i class="fas fa-pen-to-square me-2" style="color: var(--ps-primary);"></i> Deixe sua dúvida ou comentário
									</h5>
									<form id="formPostComment" autocomplete="off">
										<!-- Honeypot anti-spam invisível -->
										<input type="text" name="website_hp" style="display:none !important;" tabindex="-1" autocomplete="off">
										<input type="hidden" name="news_id" value="<?= $news_id; ?>">
										
										<div class="row g-3 mb-3">
											<div class="col-md-6">
												<label class="form-label small fw-bold text-muted mb-1">Seu Nome Completo *</label>
												<div class="input-group input-group-sm">
													<span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
													<input type="text" name="c_name" class="form-control border-start-0" placeholder="Ex: Maria Ferreira" required minlength="2" maxlength="120">
												</div>
											</div>
											<div class="col-md-6">
												<label class="form-label small fw-bold text-muted mb-1">Seu E-mail * <small class="text-muted fw-normal">(não será exibido)</small></label>
												<div class="input-group input-group-sm">
													<span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
													<input type="email" name="c_email" class="form-control border-start-0" placeholder="seu.email@exemplo.com" required>
												</div>
											</div>
										</div>

										<div class="mb-3">
											<label class="form-label small fw-bold text-muted mb-1">Seu Comentário *</label>
											<textarea name="c_comment" class="form-control" rows="3" placeholder="Compartilhe suas dúvidas, observações ou experiência sobre o assunto..." required minlength="4" maxlength="2500" style="font-size: 0.9rem;"></textarea>
										</div>

										<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
											<small class="text-muted" style="font-size: 0.76rem;">
												<i class="fas fa-shield-alt text-success me-1"></i> Ambiente moderado e livre de spam.
											</small>
											<button type="submit" id="btnSubmitComment" class="btn btn-sm btn-submit-comment fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
												<i class="fas fa-paper-plane"></i> <span>Publicar Comentário</span>
											</button>
										</div>
									</form>
								</div>

								<!-- Lista de Comentários -->
								<div id="commentsFeedWrapper">
									<?php if(!empty($comentarios)): ?>
										<div id="commentsList">
											<?php foreach($comentarios as $c): 
												$c_parts = explode(' ', trim($c['c_name']));
												$c_init = mb_strtoupper(mb_substr($c_parts[0] ?? '', 0, 1));
												if (count($c_parts) > 1) {
													$c_init .= mb_strtoupper(mb_substr(end($c_parts) ?? '', 0, 1));
												}
												$c_time = date('d/m/Y \à\s H:i', strtotime($c['c_date']));
											?>
											<div class="ps-comment-item d-flex gap-3 align-items-start">
												<div class="ps-comment-avatar">
													<?= htmlspecialchars($c_init); ?>
												</div>
												<div class="flex-grow-1">
													<div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
														<h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
															<?= htmlspecialchars($c['c_name']); ?>
															<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-2" style="font-size: 0.68rem; font-weight: 600;">
																<i class="fas fa-check-circle me-1"></i> Leitor
															</span>
														</h6>
														<small class="text-muted" style="font-size: 0.75rem;">
															<i class="far fa-clock me-1"></i> <?= $c_time; ?>
														</small>
													</div>
													<p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.6;">
														<?= nl2br(htmlspecialchars($c['c_comment'])); ?>
													</p>
												</div>
											</div>
											<?php endforeach; ?>
										</div>
									<?php else: ?>
										<div id="noCommentsPlaceholder" class="text-center py-4 text-muted">
											<i class="far fa-comments fa-2x mb-2 d-block text-secondary opacity-50"></i>
											<p class="mb-1 fw-bold">Ainda não há comentários neste artigo.</p>
											<small>Seja o primeiro a compartilhar sua dúvida ou relato usando o formulário acima!</small>
										</div>
										<div id="commentsList" style="display: none;"></div>
									<?php endif; ?>
								</div>
							</div>

							<!-- Aba 2: Comentários do Facebook -->
							<div class="tab-pane fade" id="tab-fb" role="tabpanel">
								<div class="p-3 bg-light rounded-3 mb-3 border">
									<small class="text-muted d-block">
										<i class="fab fa-facebook ps-text-teal me-1"></i> <strong>Aviso de Integração:</strong> Caso a caixa do Facebook não carregue por bloqueadores de rastreamento no navegador ou ausência de App ID configurado, utilize a aba <strong>Comentários no Site</strong> acima.
									</small>
								</div>
								<div class="fb-comments" data-href="<?= $final_url; ?>" data-numposts="<?= $limit_posts; ?>" data-width="100%"></div>
							</div>
						</div>
					</div>

					<script>
					$(function() {
						// 1. Copiar Link do Artigo com Notificação Toastr
						$('#btnCopyNewsUrl').on('click', function(e) {
							e.preventDefault();
							var url = $(this).data('url') || window.location.href;
							if (navigator.clipboard && window.isSecureContext) {
								navigator.clipboard.writeText(url).then(function() {
									if (typeof toastr !== 'undefined') {
										toastr.success('Link do artigo copiado para a área de transferência!');
									}
								}).catch(function() {
									copyFallback(url);
								});
							} else {
								copyFallback(url);
							}
						});

						function copyFallback(text) {
							var tempInput = document.createElement('input');
							tempInput.value = text;
							document.body.appendChild(tempInput);
							tempInput.select();
							document.execCommand('copy');
							document.body.removeChild(tempInput);
							if (typeof toastr !== 'undefined') {
								toastr.success('Link do artigo copiado para a área de transferência!');
							}
						}

						// 2. Envio Assíncrono (AJAX) do Comentário
						$('#formPostComment').on('submit', function(e) {
							e.preventDefault();
							var $form = $(this);
							var $btn = $('#btnSubmitComment');
							var originalBtnHtml = $btn.html();

							$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Publicando...');

							$.ajax({
								url: '<?= BASE_URL; ?>ajax_comment.php',
								type: 'POST',
								data: $form.serialize(),
								dataType: 'json',
								success: function(res) {
									$btn.prop('disabled', false).html(originalBtnHtml);
									if (res.status === 'success') {
										if (typeof toastr !== 'undefined') {
											toastr.success(res.message);
										}
										$form[0].reset();

										$('#noCommentsPlaceholder').hide();
										$('#commentsList').show();

										var c = res.comment;
										var itemHtml = `
										<div class="ps-comment-item d-flex gap-3 align-items-start" style="display:none; background: rgba(16, 185, 129, 0.05); padding: 14px; border-radius: 12px; margin-bottom: 12px;">
											<div class="ps-comment-avatar">
												${c.initials}
											</div>
											<div class="flex-grow-1">
												<div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
													<h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
														${c.name}
														<span class="badge bg-success text-white rounded-pill ms-2" style="font-size: 0.68rem; font-weight: 600;">
															<i class="fas fa-check-circle me-1"></i> Publicado Agora
														</span>
													</h6>
													<small class="text-muted" style="font-size: 0.75rem;">
														<i class="far fa-clock me-1"></i> ${c.date}
													</small>
												</div>
												<p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.6;">
													${c.comment}
												</p>
											</div>
										</div>`;

										var $newItem = $(itemHtml);
										$('#commentsList').prepend($newItem);
										$newItem.slideDown(400);

										var $badge = $('#psCommentCountBadge');
										var currentCount = parseInt($badge.text()) || 0;
										$badge.text(currentCount + 1);

									} else {
										if (typeof toastr !== 'undefined') {
											toastr.error(res.message || 'Erro ao publicar comentário.');
										}
									}
								},
								error: function() {
									$btn.prop('disabled', false).html(originalBtnHtml);
									if (typeof toastr !== 'undefined') {
										toastr.error('Erro de conexão ao enviar o comentário. Tente novamente.');
									}
								}
							});
						});

						// 3. Re-renderiza XFBML do Facebook caso a aba seja clicada
						$('#tab-fb-btn').on('shown.bs.tab', function() {
							if (typeof FB !== 'undefined' && FB.XFBML) {
								FB.XFBML.parse();
							}
						});
					});
					</script>
					<!-- Post Item End -->

				</div>

			</div>
			<div class="col-lg-4">
				<?php require_once('sidebar.php'); ?>
			</div>
		</div>
	</div>
</section>
<!-- Blog End -->


<?php require_once('footer.php'); ?>