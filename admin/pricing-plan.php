<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Preços dos Planos</h1>
	</div>
	<div class="content-header-right">
		<a href="pricing-plan-add.php" class="btn btn-primary btn-sm">Add Novo</a>
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
			        <th>Plano</th>
			        <th>Preço</th>
			        <th>Texto do Botão</th>
			        <th>URL do Botão</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan ORDER BY pricing_plan_id ASC");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
	            	?>
	                <tr>
	                    <td><?php echo $i; ?></td>
	                    <td><?php echo $row['pricing_plan_name']; ?></td>
	                    <td><?php echo $row['pricing_plan_price']; ?></td>
	                    <td><?php echo $row['pricing_plan_button_text']; ?></td>
	                    <td><?php echo $row['pricing_plan_button_url']; ?></td>
	                    <td>
	                        <a href="pricing-plan-edit.php?id=<?php echo $row['pricing_plan_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
	                        <a href="#" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-delete-<?= $row['pricing_plan_id']; ?>">Deletar</a>
	                    </td>
	                </tr>
	                <div class="modal fade" id="confirm-delete-<?= $row['pricing_plan_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Confirmar Exclusão</h4>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja excluir o plano <b><?= $row['pricing_plan_name']; ?></b>?<br>
                                    Tome cuidado! Todos os dados de itens de preços sob este plano também serão excluídos.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                    <a href="pricing-plan-delete.php?id=<?= $row['pricing_plan_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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