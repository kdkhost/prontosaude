<?php 
require_once('header.php'); 

// Busca os dados dos Termos de Uso no banco de dados
$statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE id=1 LIMIT 1");
$statement->execute();
$row_termos = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row_termos) {
    // Fallback caso não haja registro com id=1
    $statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE status=1 ORDER BY id DESC LIMIT 1");
    $statement->execute();
    $row_termos = $statement->fetch(PDO::FETCH_ASSOC);
}

$titulo     = !empty($row_termos['titulo']) ? $row_termos['titulo'] : 'Termos de Uso & Condições Gerais';
$detalhes   = !empty($row_termos['detalhes']) ? $row_termos['detalhes'] : '<p>Converse com o seu médico ou responsável pela execução do procedimento, eles saberão orientá-lo. Nosso compromisso é com você e com a saúde de sua família.</p>';
$banner_raw = $row_termos['banner'] ?? '';
$data_mod   = !empty($row_termos['modificado']) && $row_termos['modificado'] !== '0000-00-00 00:00:00' ? $row_termos['modificado'] : ($row_termos['criado'] ?? date('Y-m-d'));

// Resolução de imagem de banner
$banner_url = BASE_URL . 'assets/uploads/department-1.png';
if (!empty($banner_raw)) {
    if (file_exists(__DIR__ . '/assets/uploads/termos/' . $banner_raw)) {
        $banner_url = BASE_URL . 'assets/uploads/termos/' . $banner_raw;
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
						<i class="fas fa-file-medical me-1"></i> Transparência &amp; Atendimento
					</span>
					<h1 class="mt-2 mb-2"><?= htmlspecialchars($titulo); ?></h1>
					<p class="text-white text-opacity-75 small mb-0">Conheça os termos de uso, condições gerais e diretrizes assistenciais do Centro Médico Pronto Saúde.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Conteúdo dos Termos de Uso -->
<section class="about-v2 section-sobre" style="padding: 60px 0;">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10 col-md-12">
				<div class="ps-page-content-box">
					
					<div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-slate-200">
						<div class="d-flex align-items-center justify-content-center text-white rounded-3 p-3" style="background: var(--ps-primary); width: 48px; height: 48px; font-size: 1.25rem;">
							<i class="fas fa-file-signature"></i>
						</div>
						<div>
							<h4 class="fw-bold mb-0" style="color: var(--ps-title); font-size: 1.25rem;">Condições Gerais de Atendimento &amp; Serviços</h4>
							<span class="text-muted small">Pronto Saúde • Segurança do Paciente e Ética Médica</span>
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
							Estes Termos de Uso foram atualizados em <?= function_exists('formata_mes_ano') ? formata_mes_ano($data_mod) : date('d/m/Y', strtotime($data_mod)); ?>.
						</div>
						<div class="d-flex align-items-center gap-2">
							<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
								<i class="fas fa-check-circle me-1"></i> Vigente
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>

