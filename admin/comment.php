<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
	$fb_comment_status   = isset($_POST['fb_comment_status']) ? 1 : 0;
	$fb_comment_numposts = intval($_POST['fb_comment_numposts'] ?? 5);
	$fb_app_id           = trim($_POST['fb_app_id'] ?? '');
	$code_body           = trim($_POST['code_body'] ?? '');

	if($fb_comment_numposts < 1) {
		$fb_comment_numposts = 5;
	}

	// Atualiza tbl_comment
	$statement = $pdo->prepare("UPDATE tbl_comment SET code_body=?, fb_comment_status=?, fb_comment_numposts=?, fb_app_id=? WHERE id=1");
	$statement->execute(array($code_body, $fb_comment_status, $fb_comment_numposts, $fb_app_id));

	$success_message = 'As configurações de comentários do Facebook foram salvas com sucesso!';
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Comentários do Facebook (Blog)</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Comentários do Facebook</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="<?php echo BASE_URL; ?>page/blog" target="_blank" class="btn btn-outline-primary btn-sm shadow-sm">
					<i class="bi bi-box-arrow-up-right me-1"></i> Ver Blog no Site
				</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_comment WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC);							
$code_body           = $row['code_body'] ?? '';
$fb_comment_status   = intval($row['fb_comment_status'] ?? 1);
$fb_comment_numposts = intval($row['fb_comment_numposts'] ?? 5);
$fb_app_id           = $row['fb_app_id'] ?? '';
?>

<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<?php if(!empty($error_message)): ?>
			<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
					<div><?php echo $error_message; ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<?php if(!empty($success_message)): ?>
			<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-check-circle-fill fs-5 me-2"></i>
					<div><?php echo $success_message; ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<form action="" method="post">

				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-header bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
						<span class="fw-semibold text-primary fs-6">
							<i class="bi bi-facebook me-2"></i> Integração dos Comentários do Facebook nos Artigos do Blog
						</span>
						<span class="badge <?php echo ($fb_comment_status == 1) ? 'bg-success' : 'bg-secondary'; ?> px-3 py-1">
							<?php echo ($fb_comment_status == 1) ? '<i class="bi bi-check-circle-fill me-1"></i> Módulo Ativo' : '<i class="bi bi-slash-circle me-1"></i> Módulo Desativado'; ?>
						</span>
					</div>

					<div class="card-body p-4">

						<!-- 1. Switch de Ativação Geral -->
						<div class="row mb-4 pb-3 border-bottom align-items-center">
							<label class="col-sm-3 col-lg-3 col-form-label fw-bold">Exibir Caixa de Comentários</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<div class="form-check form-switch fs-5">
									<input class="form-check-input" type="checkbox" name="fb_comment_status" id="fb_comment_status" value="1" <?php echo ($fb_comment_status == 1) ? 'checked' : ''; ?>>
									<label class="form-check-label fs-6 fw-semibold text-body ms-2" for="fb_comment_status">
										Ativar widget de comentários em cada post de notícia/artigo do blog
									</label>
								</div>
								<div class="form-text text-muted">
									Quando desativado, o bloco de comentários é automaticamente ocultado de todas as páginas de notícias.
								</div>
							</div>
						</div>

						<!-- 2. Controle de Quantidade e Paginação de Comentários -->
						<div class="row mb-4 pb-3 border-bottom align-items-center">
							<label class="col-sm-3 col-lg-3 col-form-label fw-bold">
								Limite de Comentários por Página <span class="badge bg-primary ms-1">Paginação</span>
							</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<div class="row g-2 align-items-center">
									<div class="col-sm-6 col-md-5">
										<div class="input-group">
											<span class="input-group-text"><i class="bi bi-chat-left-dots text-primary"></i></span>
											<input type="number" class="form-control" name="fb_comment_numposts" id="fb_comment_numposts" value="<?php echo $fb_comment_numposts; ?>" min="1" max="100" required>
											<span class="input-group-text">comentários</span>
										</div>
									</div>
									<div class="col-sm-6 col-md-7">
										<div class="btn-group btn-group-sm" role="group" aria-label="Atalhos de quantidade">
											<button type="button" class="btn btn-outline-secondary btn-quick-num" data-num="3">3</button>
											<button type="button" class="btn btn-outline-secondary btn-quick-num" data-num="5">5 (Padrão)</button>
											<button type="button" class="btn btn-outline-secondary btn-quick-num" data-num="10">10</button>
											<button type="button" class="btn btn-outline-secondary btn-quick-num" data-num="15">15</button>
											<button type="button" class="btn btn-outline-secondary btn-quick-num" data-num="20">20</button>
										</div>
									</div>
								</div>
								<div class="form-text text-muted mt-2">
									Configura o atributo oficial <code>data-numposts</code> do Facebook. Define quantos comentários aparecem inicialmente antes do botão "Carregar mais comentários", mantendo a leitura leve e rápida.
								</div>
							</div>
						</div>

						<!-- 3. Facebook App ID (Opcional) -->
						<div class="row mb-4 pb-3 border-bottom align-items-center">
							<label class="col-sm-3 col-lg-3 col-form-label fw-bold">
								Facebook App ID <span class="badge bg-secondary-subtle text-secondary ms-1">Opcional</span>
							</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="text" class="form-control" name="fb_app_id" id="fb_app_id" value="<?php echo htmlspecialchars($fb_app_id); ?>" placeholder="Ex: 123456789012345">
								<div class="form-text text-muted">
									Se você possui um aplicativo cadastrado no <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener">Facebook for Developers</a>, informe o App ID aqui para habilitar moderação avançada de comentários.
								</div>
							</div>
						</div>

						<!-- 4. Código do SDK do Facebook (Personalizado ou Automático) -->
						<div class="row mb-4">
							<label class="col-sm-3 col-lg-3 col-form-label fw-bold">
								Código do SDK do Facebook (JS)
							</label>
							<div class="col-12 col-sm-9 col-lg-8">
								<div class="d-flex flex-wrap gap-2 mb-2">
									<button type="button" class="btn btn-sm btn-outline-primary" id="btnGenDefaultSdk">
										<i class="bi bi-magic me-1"></i> Gerar Código SDK Padrão (pt_BR)
									</button>
									<button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearSdk">
										<i class="bi bi-eraser me-1"></i> Limpar Campo
									</button>
									<a href="https://developers.facebook.com/docs/plugins/comments/" target="_blank" class="btn btn-sm btn-link text-decoration-none">
										<i class="bi bi-box-arrow-up-right me-1"></i> Documentação Oficial do Facebook
									</a>
								</div>
								<textarea class="form-control font-monospace small" name="code_body" id="code_body" rows="6" placeholder="Cole o código do SDK fornecido pelo Facebook ou clique em 'Gerar Código SDK Padrão'..."><?php echo htmlspecialchars($code_body); ?></textarea>
								<div class="form-text text-muted mt-2">
									<i class="bi bi-info-circle text-primary me-1"></i> <strong>Nota Inteligente:</strong> Se este campo ficar vazio, o sistema automaticamente injeta o SDK oficial oficial do Facebook em português brasileiro (<code>pt_BR</code>) com suporte total a XFBML e HTTPS.
								</div>
							</div>
						</div>

						<!-- Informações Úteis de Exibição -->
						<div class="alert alert-light border rounded-3 p-3 mb-4">
							<h6 class="fw-bold mb-2 text-primary"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Como funciona a exibição no site?</h6>
							<p class="small text-muted mb-1">
								Ao abrir qualquer artigo do blog, o sistema gera a tag oficial:
							</p>
							<code class="small d-block p-2 bg-body-tertiary rounded border mb-2">
								&lt;div class="fb-comments" data-href="https://seusite.com.br/news/slug-do-post" data-numposts="<span id="previewNum">5</span>" data-width="100%"&gt;&lt;/div&gt;
							</code>
							<p class="small text-muted mb-0">
								Cada post terá sua própria discussão independente baseada na URL canônica única do artigo.
							</p>
						</div>

						<div class="row">
							<div class="col-sm-3 col-lg-3 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-7">
								<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form1">
									<i class="bi bi-check-lg me-1"></i> Salvar Configurações
								</button>
							</div>
						</div>

					</div>
				</div>

			</form>

		</div>
	</div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var numInput = document.getElementById('fb_comment_numposts');
    var previewNum = document.getElementById('previewNum');
    var codeArea = document.getElementById('code_body');
    var appIdInput = document.getElementById('fb_app_id');

    // Botões de quantidade rápida
    document.querySelectorAll('.btn-quick-num').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var val = this.getAttribute('data-num');
            numInput.value = val;
            if (previewNum) previewNum.textContent = val;
        });
    });

    if (numInput && previewNum) {
        numInput.addEventListener('input', function() {
            previewNum.textContent = this.value || '5';
        });
    }

    // Botão Gerar SDK padrão
    var btnGen = document.getElementById('btnGenDefaultSdk');
    if (btnGen) {
        btnGen.addEventListener('click', function() {
            var appId = appIdInput ? appIdInput.value.trim() : '';
            var appIdParam = appId ? '&appId=' + appId : '';
            var sdkCode = '<div id="fb-root"></div>\n<script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v19.0' + appIdParam + '"><\/script>';
            codeArea.value = sdkCode;
            
            if (window.notificar) {
                window.notificar('success', 'O código oficial do Facebook SDK em Português do Brasil foi inserido no campo.', 'Código SDK Gerado!');
            } else if (typeof toastr !== 'undefined') {
                toastr.success('O código oficial do Facebook SDK em Português do Brasil foi inserido no campo.', 'Código SDK Gerado!');
            }
        });
    }

    // Botão Limpar
    var btnClear = document.getElementById('btnClearSdk');
    if (btnClear) {
        btnClear.addEventListener('click', function() {
            codeArea.value = '';
        });
    }
});
</script>

<?php require_once('footer.php'); ?>