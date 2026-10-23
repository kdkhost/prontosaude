<?php 
require_once('header.php'); 

// Busca os dados da Política de Privacidade no banco de dados
$statement = $pdo->prepare("SELECT * FROM tbl_privacidade WHERE id=1 LIMIT 1");
$statement->execute();
$row_priv = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row_priv) {
    // Fallback caso não haja registro com id=1
    $statement = $pdo->prepare("SELECT * FROM tbl_privacidade WHERE status=1 ORDER BY id DESC LIMIT 1");
    $statement->execute();
    $row_priv = $statement->fetch(PDO::FETCH_ASSOC);
}

$titulo     = !empty($row_priv['titulo']) ? $row_priv['titulo'] : 'Política de Privacidade & Termos';
$detalhes   = !empty($row_priv['detalhes']) ? $row_priv['detalhes'] : '<p>A Pronto Saúde valoriza e respeita a privacidade de todos os seus pacientes, colaboradores e visitantes. Nossas diretrizes estão em total conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>';
$banner_raw = $row_priv['banner'] ?? '';
$data_mod   = !empty($row_priv['modificado']) && $row_priv['modificado'] !== '0000-00-00 00:00:00' ? $row_priv['modificado'] : ($row_priv['criado'] ?? date('Y-m-d'));

// Resolução de imagem de banner
$banner_url = BASE_URL . 'assets/uploads/department-1.png';
if (!empty($banner_raw)) {
    if (file_exists(__DIR__ . '/assets/uploads/privacidade/' . $banner_raw)) {
        $banner_url = BASE_URL . 'assets/uploads/privacidade/' . $banner_raw;
    } elseif (file_exists(__DIR__ . '/assets/uploads/' . $banner_raw)) {
        $banner_url = BASE_URL . 'assets/uploads/' . $banner_raw;
    }
}
?>

<!-- Banner Superior Padronizado -->
<div class="page-banner" style="background-image: url(<?= $banner_url; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center text-white">
				<div class="banner-text">
					<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
						<i class="fas fa-shield-alt me-1"></i> Transparência &amp; Segurança
					</span>
					<h1 class="mt-2 mb-2"><?= htmlspecialchars($titulo); ?></h1>
					<p class="text-white text-opacity-75 small mb-0">Conheça nosso compromisso ético e legal com a proteção dos seus dados pessoais e médicos.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Conteúdo da Política de Privacidade -->
<section class="about-v2" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10 col-md-12">
				<div style="background: #ffffff; border-radius: 20px; padding: 42px 36px; box-shadow: 0 10px 30px rgba(10, 107, 116, 0.06); border: 1px solid #e2e8f0; font-size: 15.5px; line-height: 1.85; color: #334155;">
					
					<div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-slate-200">
						<div class="d-flex align-items-center justify-content-center text-white rounded-3 p-3" style="background: var(--ps-primary); width: 48px; height: 48px; font-size: 1.25rem;">
							<i class="fas fa-lock"></i>
						</div>
						<div>
							<h4 class="fw-bold mb-0" style="color: var(--ps-title); font-size: 1.25rem;">Proteção de Dados &amp; Sigilo Médico</h4>
							<span class="text-muted small">Pronto Saúde • Diretrizes de Governança e LGPD</span>
						</div>
					</div>

					<!-- Texto oficial do banco de dados -->
					<div class="ps-privacy-content">
						<?= $detalhes; ?>
					</div>

					<!-- Rodapé com carimbo de atualização -->
					<div class="mt-4 pt-3 border-top border-slate-200 d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted small">
						<div>
							<i class="fa-regular fa-clock me-1" style="color: var(--ps-primary);"></i>
							Esta Política de Privacidade foi atualizada em <?= function_exists('formata_mes_ano') ? formata_mes_ano($data_mod) : date('d/m/Y', strtotime($data_mod)); ?>.
						</div>
						<div class="d-flex align-items-center gap-2">
							<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
								<i class="fas fa-check-circle me-1"></i> LGPD Válida
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>