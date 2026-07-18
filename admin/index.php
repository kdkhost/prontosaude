<?php require_once('header.php'); ?>

<section class="content-header">
  <h1>Painel</h1>
</section>

<?php 
$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_user");
$statement->execute();
$total_user = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_category");
$statement->execute();
$total_category = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_news");
$statement->execute();
$total_news = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_photo");
$statement->execute();
$total_photo = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_video");
$statement->execute();
$total_video = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_subscriber WHERE subs_active=1");
$statement->execute();
$total_subscriber = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_doctor WHERE status='Active'");
$statement->execute();
$total_medico = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_doctor WHERE status='Inactive'");
$statement->execute();
$total_medicos = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_department");
$statement->execute();
$total_clinica = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_dental");
$statement->execute();
$total_dental = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_trabalhe");
$statement->execute();
$total_jobs = $statement->fetchColumn();
?>

<section class="content">

  <div class="row">
      <?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-aqua"><i class="fa fa-user-plus"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de usuários</span>
          <span class="info-box-number"><?php echo $total_user; ?></span>
        </div>
      </div>
    </div>
    <?php endif;?>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-red"><i class="fa fa-tasks"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Totais de Categorias</span>
          <span class="info-box-number"><?php echo $total_category; ?></span>
        </div>
      </div>
    </div>


    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-green"><i class="fa fa-file-text"></i></span>

        <div class="info-box-content">
          <span class="info-box-text">Total de Notícias</span>
          <span class="info-box-number"><?php echo $total_news; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-aqua"><i class="fa fa-picture-o"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Fotos</span>
          <span class="info-box-number"><?php echo $total_photo; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-red"><i class="fa fa-camera"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Vídeos</span>
          <span class="info-box-number"><?php echo $total_video; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Inscritos</span>
          <span class="info-box-number"><?php echo $total_subscriber; ?></span>
        </div>
      </div>
    </div>
    
    <!-- adicionados por kdkhost -->
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-yellow"><i class="fa fa-user-md"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Médicos Ativos</span>
          <span class="info-box-number"><?php echo $total_medico; ?></span>
        </div>
      </div>
    </div>
    
    <?php if($total_medicos > 0){ ?>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-primary"><i class="fa fa-user-md"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Médicos Inativos</span>
          <span class="info-box-number"><?php echo $total_medicos; ?></span>
        </div>
      </div>
    </div>        
    <?php } ?>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-brown"><i class="fa fa-medkit"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total de Clínicas</span>
          <span class="info-box-number"><?php echo $total_clinica; ?></span>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-primary"><i class="fa fa-info-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-wrap">Total de Serv. Dentários</span>
          <span class="info-box-number"><?php echo $total_dental; ?></span>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon" style="background:#0a6b74; color:#fff;">
            <i class="fa fa-list"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text text-wrap">
              <a href="trabalhe-conosco.php" style="text-decoration:none;">
                  Total de Vagas
              </a>
          </span>
          <span class="info-box-number"><?php echo $total_jobs; ?></span>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
      <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="panel panel-default text-center">
                <div class="panel panel-header">
                  <img class="card-img-top img-responsive" style="width:100%; height:195px;" src="https://www.softdownload.com.br/wp-content/uploads/2019/06/10_programas_acesso_remoto.png" alt="Suporte Remoto">
                </div>
                <h4>Suporte Online</h4>
                <div class="panel-body">
                    os dados de acesso ao site devem ser enviados por email para que seja feito o acesso remoto na maquina ou no sistema
                </div>
                <a href="mailto:marcelobradrj@gmail.com"  class="btn-block btn-warning btn" style="border-radius:0px;">
                    Entrar em contato
                </a>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 col-xs-12">
           <div class="panel panel-default text-center">
              <div class="panel panel-header">
                <img class="card-img-top img-responsive" style="width:100%; height:195px;" src="https://flowti.com.br/storage/blog/1342172021070260df17b9e0917.jpeg" alt="Suporte Telefone">
              </div>
              <h4>Suporte via Telefone</h4>
              <div class="panel-body">
                ao solicitar suporte via telefone indique o problema ao tecnico para que o mesmo possa ajuda-lo da melhor maneira
              </div>
              <a href="tel:5521981325441"  class="btn-block btn-info btn" style="border-radius:0px;">
                  Ligar
              </a>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="panel panel-default text-center">
                <div class="panel panel-header">
                    <img class="card-img-top img-responsive" style="width:100%; height:195px;" src="https://www.metroworldnews.com.br/resizer/hnHOTrh2kbkMmMJopaQZM9XV8ho=/300x0/arc-photo-metroworldnews/arc2-prod/public/TGYQFT5IQFFEZNH272AZZIHUIY.jpg" alt="Suporte WhatsApp">
                </div>
                <h4>Suporte via WhatsApp</h4>
              <div class="panel-body">
                  envie prints das telas onde deseja suporte e o acesso do site completo
              </div>
              <a href="https://wa.me/5521981325441"  class="btn-block btn-success btn" style="border-radius:0px;">
                Chamar
              </a>
            </div>
        </div>
  </div>

<script>
    $(document).ready(function ($) {
      $("div").click(function () {
        var url = $(this).data("href");
        window.open(url);
      });
    });
</script>

</section>

<?php require_once('footer.php'); ?>