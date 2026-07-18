<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Fotos</h1>
	</div>
	<div class="content-header-right">
		<a href="photo-add.php" class="btn btn-primary btn-sm">Add Nova</a>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-striped">
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
	                    	<img src="../assets/uploads/<?php echo $row['photo_name']; ?>" height="80" width="80">
	                    </td>
	                    <td><?php echo $row['p_category_name']; ?></td>
	                    <td>
	                        <a href="photo-edit.php?id=<?php echo $row['photo_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
	                        <a href="#" class="btn btn-danger btn-xs" 
	                        data-toggle="modal" data-target="#confirm-delete-<?php echo $row['photo_id']; ?>">
	                            Deletar
	                        </a>
	                    </td>
	                </tr>
	                
	                <div class="modal fade" id="confirm-delete-<?php echo $row['photo_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                </div>
                                <div class="modal-body">
                                    você tem certeza de que quer deletar essa imagem?<br>
                                    <img src="../assets/uploads/<?php echo $row['photo_name']; ?>" width="140">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
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

</section>

<?php require_once('footer.php'); ?>