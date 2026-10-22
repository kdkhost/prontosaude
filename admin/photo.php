<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Fotos</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Fotos</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="photo-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo</a>
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
			        <th>Título</th>
			        <th>Foto</th>
			        <th>Categoria</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>

            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT
                                                t1.photo_id,
                                                t1.photo_caption,
                                                t1.photo_name,
                                                t1.p_category_id,
                                                t2.p_category_id,
                                                t2.p_category_name
                                            FROM
                                                tbl_photo t1
                                            JOIN tbl_category_photo t2 ON
                                                t1.p_category_id = t2.p_category_id
                                            ORDER BY
                                                t2.p_category_name");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
	            	?>
	                <tr>
	                    <td><?= $i ; ?></td>
	                    <td><?php echo $row['photo_caption']; ?></td>
	                    <td>
	                    	<img class="rounded border object-fit-cover" src="../assets/uploads/<?php echo $row['photo_name']; ?>" height="80" width="80">
	                    </td>
	                    <td><?php echo $row['p_category_name']; ?></td>
	                    <td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="photo-edit.php?id=<?php echo $row['photo_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
	                        <a href="#" class="btn btn-danger btn-sm" 
	                        data-target="#confirm-delete-<?php echo $row['photo_id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
	                </tr>
	                
	                <div class="modal fade" id="confirm-delete-<?php echo $row['photo_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                </div>
                                <div class="modal-body">
                                    você tem certeza de que quer deletar essa imagem?<br>
                                    <img src="../assets/uploads/<?php echo $row['photo_name']; ?>" width="140">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <a href="photo-delete.php?id=<?php echo $row['photo_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                </div>
                            </div>
                        </div>
                    </div>
	                <?php } ?>
            </tbody>
          </table>
        </div>
      </div> 


</div>
</div>
</div>
</div>

<?php require_once('footer.php'); ?>