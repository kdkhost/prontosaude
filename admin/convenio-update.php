<?php
require_once('header.php');

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

if(isset($_POST['BtnConv'])) {
    //var_dump($_FILES["banner"]['name'], $_POST);
    //die();

	if(empty($_POST['titulo'])) {
		$error_message .= 'O Título não pode ser vazio<br>';
	}

	if(empty($_POST['subtitulo'])) {
		$error_message .= 'O Sub-título não pode ser vazio<br>';
	}
	
	if(empty($_POST['descricao'])) {
		$error_message .= 'A Descrição não pode ser vazia<br>';
	}
	
    $path = $_FILES["banner"]['name'];
    $path_tmp = $_FILES['banner']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload de um arquivo jpg, jpeg, gif ou png para a foto banner<br>';
        }
    }

		if($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_convenio SET titulo_banner=?, titulo=?, subtitulo=?, descricao=? WHERE id_conv=?");
    		$statement->execute(array($_POST['titulo_banner'],$_POST['titulo'],$_POST['subtitulo'],$_POST['descricao'], $_POST['id_conv']));
		}
		if($path != '') {
			unlink(BASE_URL . 'assets/uploads/'. $banner);

			$final_name = 'livre_escolha-'. $_POST['id_conv'] . '.' .$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/' . $final_name );

        	$statement = $pdo->prepare("UPDATE tbl_convenio SET titulo_banner=?, titulo=?, subtitulo=?, descricao=?, banner=? WHERE id_conv=?");
    		$statement->execute(array($_POST['titulo_banner'],$_POST['titulo'],$_POST['subtitulo'],$_POST['descricao'],$final_name, $_POST['id_conv']));
		}

		$success_message = 'Sistema Livre Escolha Atualizado com Sucesso!!';
		header("Location: convenio-edit.php");
	}
	
?>