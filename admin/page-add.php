<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['page_name'])) {
        $valid = 0;
        $error_message .= "O nome da página não pode ficar vazio<br>";
    } else {
    	// Duplicate Page checking
    	$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_name=?");
    	$statement->execute(array($_POST['page_name']));
    	$total = $statement->rowCount();
    	if($total) {
    		$valid = 0;
        	$error_message .= "O nome da página já existe<br>";
    	}
    }

    $path = $_FILES['banner']['name'];
    $path_tmp = $_FILES['banner']['tmp_name'];

    if($path!='') {
        $ext = strtolower(pathinfo( $path, PATHINFO_EXTENSION ));
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' && $ext!='webp' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de arquivo jpg, jpeg, png, gif ou webp para o banner<br>';
        }
    } else {
    	$valid = 0;
        $error_message .= 'Você deve ter que selecionar uma foto para banner<br>';
    }

    if($valid == 1) {

    	// getting auto increment id
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_page'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

    	if($_POST['page_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['page_name']);
    		$page_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['page_slug']);
    		$page_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=?");
		$statement->execute(array($page_slug));
		$total = $statement->rowCount();
		if($total) {
			$page_slug = $page_slug.'-1';
		}

		$final_name = 'page-banner-'.$ai_id.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );
    	
		// saving into the database
		$clean_title = trim(strip_tags($_POST['meta_title'] ?? ''));
		$clean_kw = trim(strip_tags($_POST['meta_keyword'] ?? ''));
		$clean_desc = trim(strip_tags($_POST['meta_description'] ?? ''));
		$clean_desc = preg_replace('/[\r\n\t]+/', ' ', $clean_desc);
		$clean_desc = preg_replace('/\s+/', ' ', $clean_desc);

		$statement = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?)");
		$statement->execute(array($_POST['page_name'],$page_slug,$_POST['page_content'],$_POST['page_layout'],$final_name,$_POST['status'],$clean_title,$clean_kw,$clean_desc));

    	$success_message = 'A página foi adicionada com sucesso.';
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Página</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Página</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="page.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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
			
			<p>
			<?php echo $error_message; ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
			
			<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form action="" method="post" enctype="multipart/form-data">

				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome da página <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="page_name" placeholder="Exemplo: Sobre nós">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug de página</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control ps-slug-locked" name="page_slug" placeholder="Gerado automaticamente..." readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Layout da página</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<select class="form-control select2" name="page_layout" style="width:300px;" onchange="showContentInputArea(this)">
									<option value="Full Width Page Layout">Layout de página de largura total</option>
									<option value="FAQ Page Layout">Layout da página FAQ</option>
									<option value="Doctor Page Layout">Layout da página do Médico</option>
									<option value="Photo Gallery Page Layout">Layout da página Galeria</option>
									<option value="Video Gallery Page Layout">Layout da página da galeria de vídeos</option>
									<option value="Blog Page Layout">Layout da página do blog</option>
									<option value="Contact Us Page Layout">Layout da página Fale conosco</option>
								</select>
							</div>
						</div>
						
						<div class="row mb-3" id="showPageContent">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Conteúdo da página</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="page_content" id="1"></textarea>
							</div>
						</div>	
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner / Imagem Social <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="file" name="banner" class="form-control mb-2" id="pageBannerInput" accept="image/png, image/jpeg, image/webp, image/gif">
								<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
									<span class="badge bg-primary text-white"><i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1</span>
									<span class="badge bg-dark text-white"><i class="bi bi-arrows-fullscreen me-1"></i> Dimensão Recomendada: 1200 x 630 px</span>
									<span class="badge bg-secondary-subtle text-body border"><i class="bi bi-file-earmark-check me-1"></i> Formatos: JPG, PNG, WEBP</span>
								</div>
								<div class="form-text small text-body-secondary">
									Esta imagem é utilizada como topo da página e gerada automaticamente como <strong>capa no WhatsApp, Facebook, LinkedIn e Twitter/X</strong> ao compartilhar.
								</div>
								<div id="pageBannerPreviewContainer" class="mt-3 p-2 border rounded-3 bg-body-tertiary d-none" style="max-width: 380px;">
									<div class="small fw-semibold text-body-secondary mb-1"><i class="bi bi-eye me-1"></i> Prévia do Banner (Proporção 1.91:1):</div>
									<div class="ratio ratio-16x9 rounded overflow-hidden border bg-dark">
										<img id="pageBannerPreviewImg" src="" alt="Prévia" class="w-100 h-100 object-fit-cover">
									</div>
								</div>
							</div>
						</div>					
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" checked> <label class="form-check-label">Sim</label></div>
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive"> <label class="form-check-label">Não</label></div>
				            </div>
				        </div>
						<div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>Informações de SEO</h5></div></div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" class="form-control" name="meta_title">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_keyword" style="height:80px;"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control no-summernote" data-no-summernote="true" id="meta_description" name="meta_description" style="height:100px;"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="page.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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
(function() {
	const fileInput = document.getElementById('pageBannerInput');
	const container = document.getElementById('pageBannerPreviewContainer');
	const img = document.getElementById('pageBannerPreviewImg');
	if(fileInput && container && img) {
		fileInput.addEventListener('change', function() {
			const file = this.files && this.files[0];
			if(file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					img.src = e.target.result;
					container.classList.remove('d-none');
				};
				reader.readAsDataURL(file);
			} else {
				container.classList.add('d-none');
			}
		});
	}
})();
</script>

<?php require_once('footer.php'); ?>