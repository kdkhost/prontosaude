<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar FAQs</h1>
	</div>
	<div class="content-header-right">
		<a href="faq-add.php" class="btn btn-primary btn-sm">Add Novo</a>
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
			        <th width="50">ID</th>
			        <th width="140">Título FAQ</th>
			        <th>Conteúdo FAQ</th>
			        <th width="120">Categoria FAQ</th>
			        <th width="80">Ação</th>
			    </tr>
			</thead>
            <tbody>
	            <?php
	            	$i=0;
	            	$statement = $pdo->prepare("SELECT 
	            	                           
												t1.faq_id,
												t1.faq_title,
												t1.faq_content,
												t1.faq_category_id,

												t2.faq_category_id,
												t2.faq_category_name

	            	                           	FROM tbl_faq t1
	            	                           	JOIN tbl_faq_category t2
	            	                           	ON t1.faq_category_id = t2.faq_category_id");
	            	$statement->execute();
	            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	            	foreach ($result as $row) {
	            		$i++;
		            	?>
			            <tr>
			                <td><?= $i; ?></td>
			                <td><?= $row['faq_title']; ?></td>
			                <td><?= $row['faq_content']; ?></td>
			                <td><?= $row['faq_category_name']; ?></td>
			                <td>
			                    <a href="faq-edit.php?id=<?= $row['faq_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
			                    <a href="#" class="btn btn-danger btn-xs" data-href="faq-delete.php?id=<?= $row['faq_id']; ?>" data-toggle="modal" data-target="#confirm-delete-<?=$row['faq_id']?>">Deletar</a>  
			                </td>
			            </tr>
			            <div class="modal fade" id="confirm-delete-<?=$row['faq_id']?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="myModalLabel">Confirmar Exclusão</h4>
                                    </div>
                                    <div class="modal-body">
                                        Você tem certeza de que vai deletar essa Pergunta?<br>
                                        <b>Pergunta:</b> <strong class="text-danger"><?= $row['faq_title']; ?></strong><br>
                                        <b>Resposta:</b>  <strong class="text-danger"><?= $row['faq_content']; ?></strong><br>
                                        <b>Reservado para Categoria:</b>  <strong class="text-danger"><?= $row['faq_category_name']; ?></strong>
                                        
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <a href="faq-delete.php?id=<?= $row['faq_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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