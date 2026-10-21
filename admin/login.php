<?php
ob_start();
session_start();
include("config.php");
$error_message='';

if(isset($_POST['form1'])) {
        
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'E-mail e/ou senha não podem estar vazios<br>';
    } else {

    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=?");
    	$statement->execute(array($_POST['email'],'Active'));
    	$total = $statement->rowCount();    
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);    
        if($total==0) {
            $error_message .= 'Endereço de e-mail não encontrado<br>';
        } else {       
            foreach($result as $row) { 
                $row_password = $row['password'];
            }
        
            if( $row_password != md5($_POST['password']) ) {
                $error_message .= 'Senha incorreta<br>';
            } else {       
            
				$_SESSION['user'] = $row;
                header("location: index.php");
            }
        }
    }

    
}
?>
<?php
    	// Getting the basic data for the website from database
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
        $favicon = $config['favicon'] ?? 'favicon.png';
        $empresa = $config['empresa'] ?? 'Pronto Saúde';
        $cor = !empty($config['color']) ? ltrim($config['color'], '#') : '0A6B74';
        $logo = $config['logo'] ?? 'logo.png';
	?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Login - <?= htmlspecialchars($empresa) ?></title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Favicon -->
	<link href="<?php echo BASE_URL; ?>assets/uploads/<?php echo htmlspecialchars($favicon); ?>" rel="shortcut icon" type="image/png">

	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
	<link rel="stylesheet" href="css/toastr.min.css">
	<link rel="stylesheet" href="style.css">
	<style>
	b {
	    color:#<?=$cor?>;
	}
	    #body {
	        background:#0a6b7440;
            background: url('../assets/images/login/teletriagem.jpg') no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            background-size: cover;
            -o-background-size: cover;
	    }
	    
	    .login-button {
            background-color: #<?=$cor?>;
            border-color: #<?=$cor?>;
        }
        .btn-primary:hover, .btn-primary:active, .btn-primary.hover {
                background-color: #85b5ba;
                border-color:#<?=$cor?>;
                color:#<?=$cor?>;
            }
            .login-box-body, .register-box-body {
                background: #0a6b7469;
                padding: 20px;
                border-top: 0;
                border-radius: 10px;
                color: #ffffff;
            }
            .form-control {
                border-radius: 10px;
                box-shadow: none;
                border-color: #0a6b74;
            }
	</style>
</head>

    <body id="body">
        <div class="container">
            <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div class="box">
                        <div class="">
                            
                            <form class="form" method="post" action="">
                                <div class="form-group col-md-6 mt-4 mb-4">
                                    <img style="max-width:280px;" src="../assets/uploads/<?= htmlspecialchars($logo) ?>" alt="Painel Administrativo" />
                                </div>
                                <div class="form-group">
                                    <label for="email" class="text-white">Usuário</label><br>
                                    <input class="form-control" placeholder="Email" name="email" type="email" autocomplete="on" autofocus required>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="text-white">Senha</label><br>
                                    <input class="form-control" placeholder="Senha" name="password" type="password" autocomplete="off" value="" required>
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary btn-block btn-flat login-button" name="form1" value="Acessar">
                                </div>
                                <?php 
                        	        if( (isset($error_message)) && ($error_message!='') ):
                        	            echo '<div class="alert alert-danger">'.$error_message.'</div>';
                        	        endif;
                        	    ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="js/jquery-2.2.4.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
        <script src="js/sweetalert2.all.min.js"></script>
        <script src="js/toastr.min.js"></script>
        <script src="js/pronto-global.js"></script>
        <?php if (!empty($error_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha no Login',
                        html: '<?= addslashes(str_replace('<br>', '<br/>', $error_message)) ?>',
                        confirmButtonText: 'Tentar novamente',
                        confirmButtonColor: '#0A6B74'
                    });
                }
            });
        </script>
        <?php endif; ?>
    </body>
</html>