<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Páginas</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Páginas</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="page-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
			</div>
		</div>
	</div>
</div>


<div class="app-content">
<div class="container-fluid">

  <div class="row">
    <div class="col-md-12">

      <div class="card card-outline card-primary shadow-sm mb-4">
        
        <div class="card-body table-responsive">
          <table id="example1" class="table table-hover table-striped align-middle w-100">
			<thead>
			    <tr>
			        <th>ID</th>
			        <th>Nome da página</th>
			        <th>Slug de página</th>
			        <th>Layout da página</th>
			        <th>Status</th>
			        <th>Açao</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_page ORDER BY page_id ASC");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr>
	                    <td><?php echo $i; ?></td>
	                    <td><?php echo $row['page_name']; ?></td>
	                    <td><?php echo $row['page_slug']; ?></td>
	                    <td><?php echo $row['page_layout']; ?></td>
	                    <td><?php echo $row['status']; ?></td>
	                    <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="page-edit.php?id=<?php echo $row['page_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
	                        <a href="#" class="btn btn-danger btn-sm" data-href="page-delete.php?id=<?php echo $row['page_id']; ?>" data-target="#confirm-delete"><i class="bi bi-trash3"></i> Excluir</a></div></td>
	                </tr>
            		<?php
            	}
            	?>
            </tbody>
          </table>
        </div>
      </div>
  
    </div>

</div>
</div>
</div>
<?php require_once('footer.php'); ?>