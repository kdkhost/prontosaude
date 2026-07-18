<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Vizualizar Todas</h1>
	</div>
	<div class="content-header-right">
		<a href="extra-add.php" class="btn btn-primary btn-sm">Add Especialidade</a>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="minhaTabela" class="table table-bordered table-striped">
			<thead>
			    <tr>
			        <th width="50">ID</th>
			        <th width="">Médico</th>
			        <th>Especialidade</th>
			        <th width="">Dia</th>
			        <th width="">Hora</th>
			        <th width="80">Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT * FROM tbl_doctor AS m 
	            	                                INNER JOIN tbl_designation AS f 
	            	                                INNER JOIN tbl_especialidade AS e 
	            	                                INNER JOIN tbl_department_openning_hour AS d 
	            	                          WHERE m.id = e.medico AND d.oh_id = e.semana AND f.designation_id = e.funcao AND m.status = 'Active'");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            		$i++;
		            	?>
			            <tr>
			                <td><?= $i; ?></td>
			                <td><?= $row['name']; ?></td>
			                <td><?= $row['designation_name']; ?></td>
			                <td><?= $row['oh_day']; ?></td>
			                <td><?= date("H:i",strtotime($row['inicio'])); ?> às <?= date("H:i",strtotime($row['fim'])); ?></td>
			                <td>
			                    <a href="extra-edit.php?ide=<?php echo $row['ide']; ?>" class="btn btn-primary btn-xs">Editar</a>
			                    <a href="#" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['ide']; ?>">Deletar</a>  
			                </td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?php echo $row['ide']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja excluir este item?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                    <a href="extra-delete.php?ide=<?php echo $row['ide']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                </div>
                            </div>
                        </div>
                    </div>
			            <?php }?>
            </tbody>
          </table>
        </div>
      </div>
</section>

<?php require_once('footer.php'); ?>