<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Preços dos itens</h1>
	</div>
	<div class="content-header-right">
		<a href="pricing-item-add.php" class="btn btn-primary btn-sm">Adicionar Novo</a>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        <?php if($_SESSION['msg']){ ?>
		            <div id="alert" class="callout callout-success">
		                <?php echo $_SESSION['msg']; unset($_SESSION['msg']);?>
		            </div>
		        <?php }elseif($_SESSION['erro']){?>
		            <div id="alert" class="callout callout-danger">
		                <?php echo $_SESSION['erro']; unset($_SESSION['erro']);?>
		            </div>
		        <?php } ?>
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-striped">
			<thead>
			    <tr>
			        <th>ID</th>
			        <th>Nome do item</th>
			        <th>Nome do Plano</th>
			        <th>Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT
                                                t1.pricing_item_id,
                                                t1.pricing_item_name,
                                                t1.pricing_plan_id,
                                                t2.pricing_plan_id,
                                                t2.pricing_plan_name
                                            FROM
                                                tbl_pricing_item t1
                                            JOIN tbl_pricing_plan t2 ON
                                                t1.pricing_plan_id = t2.pricing_plan_id
                                            ORDER BY
                                                t2.pricing_plan_id");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            	    extract($row);
	            		$i++;
		            	?>
			            <tr>
			                <td><?php echo $i; ?></td>
			                <td><?php echo $row['pricing_item_name']; ?></td>
			                <td><?php echo $row['pricing_plan_name']; ?></td>
			                <td>
			                    <a href="pricing-item-edit.php?id=<?php echo $row['pricing_item_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
			                    <a href="#" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-delete-<?= $row['pricing_item_id']; ?>">Deletar</a>  
			                </td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?= $row['pricing_item_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmação de Remoção</h4>
                                    </div>
                                    <div class="modal-body">
                                        Tem certeza de que deseja excluir este item, (<?php echo $row['pricing_item_name']; ?>)?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <form action="pricing-item-delete.php" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $row['pricing_item_id']; ?>">
                                            <button type="submit" name="del" class="btn btn-danger">Deletar</button>
                                        </form>
                                        <!--<a href="pricing-item-delete.php?id=<?php echo $row['pricing_item_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>-->
                                    </div>
                                </div>
                            </div>
                        </div>
			            <?php
	            	}
	            ?>
            </tbody>
          </table>
        </div>
      </div>
  

</section>





<?php require_once('footer.php'); ?>