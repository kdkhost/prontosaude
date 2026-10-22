<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['page_name'])) {
        $valid = 0;
        $error_message .= "O nome da página não pode ficar vazio<br>";
    } else {
		// Duplicate Page checking
    	// current page name that is in the database
    	$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_page_name = $row['page_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_name=? and page_name!=?");
    	$statement->execute(array($_POST['page_name'],$current_page_name));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'O nome da página já existe<br>';
    	}
    }

    $path = $_FILES['banner']['name'];
    $path_tmp = $_FILES['banner']['tmp_name'];

    if($path!='') {
        $ext = strtolower(pathinfo( $path, PATHINFO_EXTENSION ));
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' && $ext!='webp' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, png, gif ou webp<br>';
        }
    }

    if($valid == 1) {

    	if($_POST['page_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['page_name']);
    		$page_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);;
    	} else {
    		$temp_string = strtolower($_POST['page_slug']);
    		$page_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? AND page_name!=?");
		$statement->execute(array($page_slug,$current_page_name));
		$total = $statement->rowCount();
		if($total) {
			$page_slug = $page_slug.'-1';
		}
   

   		if($path == '') {
			// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_page SET page_name=?, page_slug=?, page_content=?,page_layout=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE page_id=?");
			$statement->execute(array($_POST['page_name'],$page_slug,$_POST['page_content'],$_POST['page_layout'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
   		} else {

   			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name = 'page-banner-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

   			// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_page SET page_name=?, page_slug=?, page_content=?,page_layout=?, banner=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE page_id=?");
			$statement->execute(array($_POST['page_name'],$page_slug,$_POST['page_content'],$_POST['page_layout'],$final_name,$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
   		}

    	$success_message = 'A página foi atualizada com sucesso.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Página</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Página</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="page.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>


<?php							
foreach ($result as $row) {
	$page_name        = $row['page_name'];
	$page_slug        = $row['page_slug'];
	$page_content     = $row['page_content'];
	$page_layout      = $row['page_layout'];
	$banner           = $row['banner'];
	$status           = $row['status'];
	$meta_title       = $row['meta_title'];
	$meta_keyword     = $row['meta_keyword'];
	$meta_description = $row['meta_description'];
}
?>

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
		<input type="hidden" name="current_banner" value="<?php echo $banner; ?>">
        <div class="card card-outline card-primary shadow-sm mb-4">

            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome da Página <span class="text-danger">*</span></label>
                    <div class="col-12 col-sm-9 col-lg-6">
                        <input type="text" class="form-control" name="page_name" value="<?php echo $page_name; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug da Página</label>
                    <div class="col-12 col-sm-9 col-lg-6">
                        <input type="text" class="form-control ps-slug-locked" name="page_slug" value="<?php echo $page_slug; ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
                        <div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
                            <i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Layout da Página</label>
					<div class="col-12 col-sm-9 col-lg-6">
						<select class="form-control select2" name="page_layout" style="width:300px;" onchange="showContentInputArea(this)">
							<option value="Full Width Page Layout" <?php if($page_layout=='Full Width Page Layout') {echo 'selected';} ?>>Layout de página de largura total</option>
							<option value="FAQ Page Layout" <?php if($page_layout=='FAQ Page Layout') {echo 'selected';} ?>>Layout da página FAQ</option>
							<option value="Doctor Page Layout" <?php if($page_layout=='Doctor Page Layout') {echo 'selected';} ?>>Layout da página do médico</option>
							<option value="Photo Gallery Page Layout" <?php if($page_layout=='Photo Gallery Page Layout') {echo 'selected';} ?>>Layout da Página Galeria</option>
							<option value="Video Gallery Page Layout" <?php if($page_layout=='Video Gallery Page Layout') {echo 'selected';} ?>>Layout da Página Vídeo</option>
							<option value="Blog Page Layout" <?php if($page_layout=='Blog Page Layout') {echo 'selected';} ?>>Layout da página do blog</option>
							<option value="Contact Us Page Layout" <?php if($page_layout=='Contact Us Page Layout') {echo 'selected';} ?>>Layout da página Fale conosco</option>
						</select>
					</div>
				</div>
                <div class="row mb-3" id="showPageContent" style="<?php if($page_layout=='Full Width Page Layout') {echo 'display:block';}else{echo 'display:none;';} ?>">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Conteúdo da página</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<textarea class="form-control" name="page_content" id="1"><?php echo $page_content; ?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<?php if(!empty($banner) && file_exists('../assets/uploads/'.$banner)): ?>
							<div class="p-2 border rounded-3 bg-body-tertiary d-inline-block shadow-xs" style="max-width: 320px;">
								<div class="ratio ratio-16x9 rounded overflow-hidden border bg-dark">
									<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $banner; ?>" id="pageBannerCurrentImg" alt="Banner de página" class="w-100 h-100 object-fit-cover">
								</div>
								<div class="small text-body-secondary mt-1"><i class="bi bi-file-earmark-image me-1"></i> <?php echo htmlspecialchars($banner); ?></div>
							</div>
						<?php else: ?>
							<span class="text-body-secondary fst-italic">Nenhum banner cadastrado</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Novo Banner / Imagem Social</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<input type="file" name="banner" class="form-control mb-2" id="pageBannerInput" accept="image/png, image/jpeg, image/webp, image/gif">
						<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
							<span class="badge bg-primary text-white"><i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1</span>
							<span class="badge bg-dark text-white"><i class="bi bi-arrows-fullscreen me-1"></i> Dimensão Recomendada: 1200 x 630 px</span>
							<span class="badge bg-secondary-subtle text-body border"><i class="bi bi-file-earmark-check me-1"></i> Formatos: JPG, PNG, WEBP</span>
						</div>
						<div class="form-text small text-body-secondary">
							Esta imagem é exibida no cabeçalho da página e gerada como <strong>capa no WhatsApp, Facebook, LinkedIn e Twitter/X</strong> ao compartilhar. (Deixe em branco para manter a imagem atual)
						</div>
						<div id="pageBannerPreviewContainer" class="mt-3 p-2 border rounded-3 bg-body-tertiary d-none" style="max-width: 380px;">
							<div class="small fw-semibold text-success mb-1"><i class="bi bi-eye me-1"></i> Prévia da Nova Imagem (Proporção 1.91:1):</div>
							<div class="ratio ratio-16x9 rounded overflow-hidden border bg-dark">
								<img id="pageBannerPreviewImg" src="" alt="Nova Prévia" class="w-100 h-100 object-fit-cover">
							</div>
						</div>
					</div>
				</div>			
                <div class="row mb-3">
		            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
		            <div class="col-12 col-sm-9 col-lg-6">
		                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" <?php if($status == 'Active') { echo 'checked'; } ?>> <label class="form-check-label">Sim</label></div>
		                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive" <?php if($status == 'Inactive') { echo 'checked'; } ?>> <label class="form-check-label">Não</label></div>
		            </div>
		        </div>
                <div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>Informações de SEO</h5></div></div>
                <div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<input type="text" class="form-control" name="meta_title" value="<?php echo $meta_title; ?>">
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<textarea class="form-control" name="meta_keyword" style="height:100px;"><?php echo $meta_keyword; ?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
					<div class="col-12 col-sm-9 col-lg-9">
						<textarea class="form-control" id="2" name="meta_description" style="height:100px;"><?php echo $meta_description; ?></textarea>
					</div>
				</div>
                <div class="row mb-3">
                	<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
                    <div class="col-12 col-sm-9 col-lg-6">
                      <button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
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