<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_convenio WHERE id_conv = 1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $conv) {
    $titulo     = $conv["titulo"];
    $subtitulo  = $conv["subtitulo"];
    $descricao  = $conv["descricao"];
    $banner     = $conv["banner"];
    $nome       = $conv["titulo_banner"];
    $id         = $conv["id_conv"];
}

?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Convênio Livre Escolha</h1>
	</div>
	<div class="content-header-right">
	    <a href="convenio-edit.php" class="btn btn-primary btn-sm">Editar</a>
	</div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered">
                        <thead class="table-light">
                            <tr>
								<th>ID</th>
								<th>T. BANNER</th>
								<th>TÍTULO</th>
								<th>SUB-TÍTULO</th>
								<th>DESCRIÇÃO</th>
							</tr>
							<tbody style="background:#fff;">
							    <td><?= $id; ?></td>
							    <td><?= $nome; ?></td>
							    <td><?= $titulo; ?></td>
							    <td><?= $subtitulo; ?></td>
							    <td><?= $descricao; ?></td>
							</tbody>
							<tfooter>
							    <tr style="background:#fff;">
							        <td>
							            <h3>
							                BANNER
							            </h3>
							        </td>
                                  <td class="text-center" colspan="12">
                                      <center>
                                          <img style="max-height: 50px;" src="<?= BASE_URL ?>assets/uploads/<?= $banner; ?>" alt="Foto Banner" class="" />
                                      </center>
                                  </td>
                                </tr>
							</tfooter>
    					</thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>