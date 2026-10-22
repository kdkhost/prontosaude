<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['category_name'])) {
        $valid = 0;
        $error_message .= "Category Name can not be empty<br>";
    } else {
		// Duplicate Category checking
    	// current category name that is in the database
    	$statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_category_name = $row['category_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_name=? and category_name!=?");
    	$statement->execute(array($_POST['category_name'],$current_category_name));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'Category name already exists<br>';
    	}
    }

    if($valid == 1) {

    	if($_POST['category_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['category_name']);
    		$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);;
    	} else {
    		$temp_string = strtolower($_POST['category_slug']);
    		$category_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_slug=? AND category_name!=?");
		$statement->execute(array($category_slug,$current_category_name));
		$total = $statement->rowCount();
		if($total) {
			$category_slug = $category_slug.'-1';
		}
    	
		// updating into the database
		$statement = $pdo->prepare("UPDATE tbl_category SET category_name=?, category_slug=?, meta_title=?, meta_keyword=?, meta_description=? WHERE category_id=?");
		$statement->execute(array($_POST['category_name'],$category_slug,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));

    	$success_message = 'Category is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_id=?");
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
				<h3 class="mb-1 fw-bold">Editar Categoria</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Categoria</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="category.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>


<?php							
foreach ($result as $row) {
	$category_name = $row['category_name'];
	$category_slug = $row['category_slug'];
	$meta_title = $row['meta_title'];
	$meta_keyword = $row['meta_keyword'];
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

        <form action="" method="post">

        <div class="card card-outline card-primary shadow-sm mb-4">

            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome da Categoria <span class="text-danger">*</span></label>
                    <div class="col-12 col-sm-9 col-lg-6">
                        <input type="text" class="form-control" name="category_name" value="<?php echo $category_name; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug da Categoria</label>
                    <div class="col-12 col-sm-9 col-lg-6">
                        <input type="text" class="form-control ps-slug-locked" name="category_slug" value="<?php echo $category_slug; ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
                        <div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
                            <i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
                        </div>
                    </div>
                </div>
                <div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>SEO Information</h5></div></div>
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
						<textarea class="form-control" name="meta_description" style="height:100px;"><?php echo $meta_description; ?></textarea>
					</div>
				</div>
                <div class="row mb-3">
                	<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
                    <div class="col-12 col-sm-9 col-lg-6">
                      <button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
<a href="category.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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