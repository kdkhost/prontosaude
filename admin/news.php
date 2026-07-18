<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Notícias</h1>
	</div>
	<div class="content-header-right">
		<a href="news-add.php" class="btn btn-primary btn-sm">Adcionar Notícia</a>
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
								<th>Id</th>
								<th>Miniatura</th>
								<th width="180">Título</th>
								<th width="280">Detalhes</th>
								<th>Categoria</th>
								<th>Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT

														t1.news_id,
														t1.news_title,
														t1.news_content,
														t1.photo,
														t1.category_id,

														t2.category_id,
														t2.category_name

							                           	FROM tbl_news t1
							                           	JOIN tbl_category t2
							                           	ON t1.category_id = t2.category_id
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td>
										<?php
										if($row['photo'] == '')
										{
											echo '<img src="../assets/uploads/no-photo1.jpg" alt="" style="width:100px;">';
										}
										else
										{
											echo '<img src="../assets/uploads/'.$row['photo'].'" alt="'.$row['news_title'].'" style="width:100px;">';
										}
										?>
									</td>
									<td><?php if(strlen($row['news_title']) > 40){echo substr($row['news_title'],0,40).' ...';} else {echo $row['news_title'];} ?></td>
									<td><?php if(strlen($row['news_content']) > 180){echo substr($row['news_content'],0,180).' ...';} else {echo $row['news_content'];} ?></td>
									<td>
										<?php echo $row['category_name']; ?>
									</td>
									<td>										
										<a href="news-edit.php?id=<?php echo $row['news_id']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="news-delete.php?id=<?php echo $row['news_id']; ?>" data-toggle="modal" data-target="#confirm-delete-<?$row['news_id']?>">Deletar</a>  
									</td>
								</tr>
								
								<div class="modal fade" id="confirm-delete-<?$row['news_id']?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Tem certeza de que deseja excluir este item?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                            <a href="news-delete.php?id=<?php echo $row['news_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
								<?php }?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>


<?php require_once('footer.php'); ?>