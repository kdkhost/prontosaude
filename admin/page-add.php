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
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload do arquivo jpg, jpeg, gif ou png para banner<br>';
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
		$statement = $pdo->prepare("INSERT INTO tbl_page (page_name,page_slug,page_content,page_layout,banner,status,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?)");
		$statement->execute(array($_POST['page_name'],$page_slug,$_POST['page_content'],$_POST['page_layout'],$final_name,$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description']));

    	$success_message = 'A página foi adicionada com sucesso.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar Página</h1>
	</div>
	<div class="content-header-right">
		<a href="page.php" class="btn btn-primary btn-sm">Visializar todas</a>
	</div>
</section>


<section class="content">

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

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">

				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nome da página <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="page_name" placeholder="Exemplo: Sobre nós">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Slug de página </label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="page_slug" placeholder="Exemplo: sobre-nos">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Layout da página </label>
							<div class="col-sm-2">
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
						
						<div class="form-group" id="showPageContent">
							<label for="" class="col-sm-2 control-label">Conteúdo da página </label>
							<div class="col-sm-9">
								<textarea class="form-control" name="page_content" id="1"></textarea>
							</div>
						</div>	
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Banner <span>*</span></label>
							<div class="col-sm-9" style="padding-top:5px">
								<input type="file" name="banner">(Apenas jpg, jpeg, gif e png são permitidos)
							</div>
						</div>					
						<div class="form-group">
				            <label for="" class="col-sm-2 control-label">Ativo? </label>
				            <div class="col-sm-6">
				                <label class="radio-inline">
				                    <input type="radio" name="status" value="Active" checked>Sim
				                </label>
				                <label class="radio-inline">
				                    <input type="radio" name="status" value="Inactive">Não
				                </label>
				            </div>
				        </div>
						<h3 class="seo-info">Informações de SEO</h3>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Metatítulo </label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="meta_title">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Meta-palavras-chave </label>
							<div class="col-sm-9">
								<textarea class="form-control" name="meta_keyword" style="height:100px;"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Meta Descrição </label>
							<div class="col-sm-9">
								<textarea class="form-control" id="2" name="meta_description" style="height:100px;"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Salvar</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>