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

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Convênio Livre Escolha</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Convênio Livre Escolha</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="convenio-edit.php" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
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

</div>
</div>

<?php require_once('footer.php'); ?>