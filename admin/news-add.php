<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['news_title'])) {
		$valid = 0;
		$error_message .= 'O título da notícia não pode ficar vazio<br>';
	} else {
		// Duplicate Checking
    	$statement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_title=?");
    	$statement->execute(array($_POST['news_title']));
    	$total = $statement->rowCount();
    	if($total) {
    		$valid = 0;
        	$error_message .= "O título da notícia já existe<br>";
    	}
	}

	if(empty($_POST['news_content'])) {
		$valid = 0;
		$error_message .= 'O conteúdo de notícias não pode ficar vazio<br>';
	}

	if(empty($_POST['news_date'])) {
		$valid = 0;
		$error_message .= 'A data de publicação da notícia não pode ficar vazia<br>';
	}

	if(empty($_POST['category_id'])) {
		$valid = 0;
		$error_message .= 'Você deve ter que selecionar uma categoria<br>';
	}


	if($_POST['publisher'] == '') {
		$publisher = $_SESSION['user']['full_name'];
	} else {
		$publisher = $_POST['publisher'];	
	}


	$path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];


    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, gif ou png<br>';
        }
    }
	

	if($valid == 1) {

		// getting auto increment id for photo renaming
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_news'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		if($_POST['news_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['news_title']);
    		$news_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['news_slug']);
    		$news_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_slug=?");
		$statement->execute(array($news_slug));
		$total = $statement->rowCount();
		if($total) {
			$news_slug = $news_slug.'-1';
		}

		$clean_title = trim(strip_tags($_POST['meta_title'] ?? ''));
		$clean_kw = trim(strip_tags($_POST['meta_keyword'] ?? ''));
		$clean_desc = trim(strip_tags($_POST['meta_description'] ?? ''));
		$clean_desc = preg_replace('/[\r\n\t]+/', ' ', $clean_desc);
		$clean_desc = preg_replace('/\s+/', ' ', $clean_desc);

		if($path=='') {
			// When no photo will be selected
			$statement = $pdo->prepare("INSERT INTO tbl_news (news_title,news_slug,news_content,news_date,photo,category_id,publisher,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
			$statement->execute(array($_POST['news_title'],$news_slug,$_POST['news_content'],$_POST['news_date'],'',$_POST['category_id'],$publisher,0,$clean_title,$clean_kw,$clean_desc));
		} else {
    		// uploading the photo into the main location and giving it a final name
    		$final_name = 'news-'.$ai_id.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            $statement = $pdo->prepare("INSERT INTO tbl_news (news_title,news_slug,news_content,news_date,photo,category_id,publisher,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
			$statement->execute(array($_POST['news_title'],$news_slug,$_POST['news_content'],$_POST['news_date'],$final_name,$_POST['category_id'],$publisher,0,$clean_title,$clean_kw,$clean_desc));
		}


		// Sending an email notification to all the active subscribers
		$statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active=1");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) {
			$url = BASE_URL . 'news/' . $news_slug;
			$message = '
			Prezado(a) senhor(a),<br><br>
			Uma notícia foi publicada: 
			'.$_POST['news_title'].'<br><br>
			Para ver os detalhes sobre esta notícia, por favor clique na seguinte url:<br>
			<a href="'.$url.'">'.$url.'</a>';

			$to = $row['subs_email'];
			$subject = "Uma notícia foi publicada";
			$message = $message;
			$headers = 'From: '. $receive_email . "\r\n" .
					   'Reply-To: '. $receive_email . "\r\n" .
					   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
					   "MIME-Version: 1.0\r\n" . 
					   "Content-Type: text/html; charset=ISO-8859-1\r\n";
			mail($to, $subject, $message, $headers);
		}		
		$success_message = 'As notícias foram adicionadas com sucesso!';
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Notícia</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Notícia</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="news.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título da notícia <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="news_title" placeholder="Exemplo: manchete de notícias">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug de Notícias</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control ps-slug-locked" name="news_slug" placeholder="Gerado automaticamente..." readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Notícias <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="news_content" id="1"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Data de publicação <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="news_date" id="datepicker" value="<?php echo date('d-m-Y'); ?>">(Format: dd-mm-yy)
							</div>
						</div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto em destaque</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <input type="file" name="photo" class="form-control mb-2" accept="image/*">
				                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
				                	<span class="badge bg-primary text-white"><i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1</span>
				                	<span class="badge bg-dark text-white"><i class="bi bi-arrows-fullscreen me-1"></i> Tamanho Recomendado: 1200 x 630 px</span>
				                	<span class="badge bg-secondary-subtle text-body border">WhatsApp / Redes</span>
				                </div>
				                <div class="form-text small text-body-secondary">
				                	Esta foto é exibida como capa da notícia e na <strong>prévia de compartilhamento em redes sociais (WhatsApp, Facebook, Twitter/X)</strong>.
				                </div>
				            </div>
				        </div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<select class="form-control select2" name="category_id">
				            		<option value="">Selecione uma categoria</option>
				            		<?php
						            	$i=0;
						            	$statement = $pdo->prepare("SELECT * FROM tbl_category ORDER BY category_name ASC");
						            	$statement->execute();
						            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
						            	foreach ($result as $row) {
						            		?>
											<option value="<?php echo $row['category_id']; ?>"><?php echo $row['category_name']; ?></option>
						            		<?php
						            	}
					            	?>
				            	</select>
				            </div>
				        </div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Editor</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="publisher"> (Se você deixar em branco, o usuário logado será tratado como o editor)
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
								<input type="text" class="form-control" name="meta_keyword">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_description" style="height:120px;"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="news.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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