<?php
ob_start();
session_start();
include("config.php");
include("functions.php");
include("../assets/PHPmailer/email.php");
$error_message='';
$GET  = filter_input_array(INPUT_GET,FILTER_DEFAULT);
$POST  = filter_input_array(INPUT_POST,FILTER_DEFAULT);
// Getting the basic data for the website from database
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
	$logo = $row['logo'];
	$favicon = $row['favicon'];
	$footer_copyright = $row['footer_copyright'];
	
}
$ValidaToken = false;
if(!empty($GET['tk']) && !empty($GET['e'])){
	$email  = base64_decode($GET['e']);
	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND token=?");
	$statement->execute(array($email,$GET['tk']));
	if($statement->rowCount()):
		$ValidaToken = true;
	endif;
}
 
if(isset($POST['send'])) {

	if(empty($POST['email'])) {
		$error_message = 'E-mail não podem estar vazios<br>';
	} else {
		
		$email = strip_tags(trim($POST['email'])); 

		$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=?");
		$statement->execute(array($email,'Active'));
		$total = $statement->rowCount();    
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);  
//	var_dump($result);
		if($total==0) {
			$error_message .= 'Endereço de email não corresponde a nenhum usuário Ativo ou válido.<br>';
		} else {   
		
			$user = $result[0];
			$token =  hash('sha512', $email.time());
			$email = base64_encode($email);
			$statement = $pdo->prepare("UPDATE  tbl_user SET token=? WHERE id=?");
			$statement->execute(array($token,$user['id']));

			$linkrecover = BASE_URL."/admin/recover.php?tk={$token}&e={$email}";
			$html = "<h3>Foi solicitado a troca de sua senha</h3>";
			$html .="<p>Se você realmente solicitou a troca da senha, faça os procedimentos abaixo, <br>
			Clique no link abaixo ou copie e cole na barra de Endereço de seu navegador<br>
			<a href='{$linkrecover}'>{$linkrecover}</a> 
			<br>
			<br>
			</p>";
			 
			$email_send = SendMail($user['email'], $user['full_name'], "Recuperar senha.", $html);
			 if($email_send):
			 $error_message = "Email enviado com Sucesso!";
			 else:
			 $error_message = "Falha ao enviar email";
			 endif;
		}

				
	}
}
if(isset($POST['recover'])) {

	if(empty($POST['password']) || empty($POST['re_password'])) {
		$error_message = 'Preencha todos os campos<br>';
	} else {
		
		$password = strip_tags(trim($POST['password'])); 
		$repassword = strip_tags(trim($POST['re_password'])); 
		$email = strip_tags(trim($POST['email'])); 
		if($repassword!=$repassword){
			$error_message = 'As Senhas não coencidem. Por favor tente novamente';
		}else{

			$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? ");
			$statement->execute(array($email));
			$total = $statement->rowCount();    
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);    
			if($total==0) {
				$error_message .= 'Endereço de email não corresponde a nenhum usuário Ativo ou válido.<br>';
			} else {   
				$user = $result[0]; 
				$statement = $pdo->prepare("UPDATE  tbl_user SET password=? WHERE id=?");
				$statement->execute(array(md5($password),$user['id']));
 
				$html = "<h3>Senha redefinida</h3>";
				$html .="<p>Sua senha foi redefinida com sucesso! 
				<br>
				<br>
				</p>";
				$email_send = SendMail($user['email'], $user['full_name'], "Recuperar senha.", $html);
				 header("location:login");
				 $error_message = "Senha alterada com sucesso!";
				 
			}
		}
				 
	}
}

 
?>
<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Recuperar senha - Painel de Controle</title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">
	<link href="<?php echo BASE_URL; ?>assets/uploads/<?php echo $favicon; ?>" rel="shortcut icon" type="image/png">

	<link rel="stylesheet" href="style.css">
</head>

<body class="hold-transition login-page sidebar-mini" style="background-image: url(https://hilab.com.br/wp-content/uploads/2021/05/Medicina-precisao-Hilab-1149x768.jpg);">

	<?php if($ValidaToken): ?>
		<div class="login-box">

			<div class="login-box-body">
				<div class="login-logo">
					<span class="logo-lg"><img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $logo; ?>" alt="logo do site" width="100%" height="100" class="responsive-image"></span>
				</div>
				<hr>
				<p class="login-box-msg">Faça o login para iniciar sua sessão</p>

				<?php 
				if( (isset($error_message)) && ($error_message!='') ):
					echo '<div class="error">'.$error_message.'</div>';
			endif;
			?>

			<form action="" method="post">
				<input  name="email" type="hidden" value="<?=$email?>">
				<div class="form-group has-feedback">
					<input class="form-control" placeholder="Nova senha" name="password" type="password" required="" autocomplete="off" autofocus>
				</div>
				<div class="form-group has-feedback">
					<input class="form-control" placeholder="Digite novamente" name="re_password" type="password" autocomplete="off" required="" >
				</div>
				<div class="row">
					<div class="col-xs-8"></div>
					<div class="col-xs-4">
						<input type="submit" class="btn btn-primary btn-block btn-flat login-button" name="recover" value="Alterar senha">
					</div>
				</div>
			</form>
			<br>Seu IP:<b><? echo $_SERVER["REMOTE_ADDR"]; ?></b> será gravado.
		</div>

		<a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-block btn-flat login-button"><i class="fa fa-sign-out"></i> Ir para o Site</a>
	</div>
	<?php else: ?>
		<div class="login-box">

			<div class="login-box-body">
				<div class="login-logo">
					<span class="logo-lg"><img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $logo; ?>" alt="logo do site" width="100%" height="100" class="responsive-image"></span>
				</div>
				<hr>
				<p class="login-box-msg">Informe o email cadastrado para recuperação de senha.</p>

				<?php 
				if( (isset($error_message)) && ($error_message!='') ):
					echo '<div class="error">'.$error_message.'</div>';
			endif;
			?>

			<form action="" method="post">
				<div class="form-group has-feedback">
					<input class="form-control" placeholder="Digite seu email" name="email" type="email" autocomplete="off" autofocus required="">
				</div>

				<div class="row">
					<div class="col-xs-7">
					   <a class="btn btn-link" href="login.php">Lembrou a senha?</a>
					</div>
					<div class="col-xs-5">
					   <input type="submit" class="btn btn-primary btn-block login-button" name="send" value="Enviar email">
					</div>
				</div>
			</form>
			<br>Seu IP:<b><? echo $_SERVER["REMOTE_ADDR"]; ?></b> será gravado.
		</div>

		<a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-block btn-flat login-button"><i class="fa fa-sign-out"></i> Ir para o Site</a>
	</div>
<?php endif; ?>

<section class="footer container">

	<div class="row">

		<div class="col-md-12 copyright" style="text-align: center;">
			<p style="color:white;"><?php echo $footer_copyright; ?></p>
		</div>
	</div>

</section>


<script src="js/jquery-2.2.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="js/jquery.inputmask.date.extensions.js"></script>
<script src="js/jquery.inputmask.extensions.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/icheck.min.js"></script>
<script src="js/fastclick.js"></script>
<script src="js/jquery.sparkline.min.js"></script>
<script src="js/jquery.slimscroll.min.js"></script>
<script src="js/app.min.js"></script>
<script src="js/demo.js"></script>

</body>
</html>