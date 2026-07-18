<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Usuários</h1>
	</div>
	<div class="content-header-right">
		<a href="user-add.php" class="btn btn-primary btn-sm">Adcionar Usuário</a>
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
			        <th>Nome</th>
			        <th>Foto</th>
			        <th>Email</th>
			        <th>Permissão</th>
			        <th>Status</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>

				<?php
                    $i=0;
                    $statement = $pdo->prepare("SELECT * FROM tbl_user");
                    $statement->execute();
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                    foreach ($result as $row) {
                    	$i++;
                    	?>
						<tr>
		                    <td><?php echo $i; ?></td>
		                    <td><?php echo $row['full_name']; ?></td>
		                    <td>
		                    	<?php
		                    	if($row['photo']=='') {
		                    		echo '<img src="../assets/uploads/no-photo.jpg" width="100">';
		                    	} else {
									echo '<img src="../assets/uploads/'.$row['photo'].'" width="100">';
		                    	}
		                    	?>
		                    	
		                    </td>
		                    <td><?php echo $row['email']; ?></td>
		                    <td><?php echo $row['role']; ?></td>
		                    <td><?php echo $row['status']; ?></td>
		                    <td>
		                        <?php if($i != 1) { ?>
			                        <a href="user-edit.php?id=<?=$row['id'];?>" class="btn btn-primary btn-xs">Editar</a>
			                        <a href="#" class="btn btn-danger btn-xs" data-href="user-delete.php?id=<?=$row['id'];?>" data-toggle="modal" data-target="#confirm-delete">Deletar</a>
		                        <?php } ?>
		                    </td>
		                </tr>
                    	<?php
                    }
                ?>

            </tbody>
          </table>
        </div>
      </div>
  

</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmar Exclusão</h4>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir este item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a href="user-delete.php?id=<?=$row['id'];?>" class="btn btn-danger btn-ok">Deletar</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>