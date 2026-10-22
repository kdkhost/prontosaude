<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['facebook'],'Facebook'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['twitter'],'Twitter'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['linkedin'],'LinkedIn'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['googleplus'],'Google Plus'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['pinterest'],'Pinterest'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['youtube'],'YouTube'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['instagram'],'Instagram'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['tumblr'],'Tumblr'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['flickr'],'Flickr'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['reddit'],'Reddit'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['snapchat'],'Snapchat'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['whatsapp'],'WhatsApp'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['quora'],'Quora'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['stumbleupon'],'StumbleUpon'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['delicious'],'Delicious'));

	$statement = $pdo->prepare("UPDATE tbl_social SET social_url=? WHERE social_name=?");
	$statement->execute(array($_POST['digg'],'Digg'));

	$success_message = 'Social Media URLs are updated successfully.';

}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Redes Sociais</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Redes Sociais</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_social");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	if($row['social_name'] == 'Facebook') {
		$facebook = $row['social_url'];
	}
	if($row['social_name'] == 'Twitter') {
		$twitter = $row['social_url'];
	}
	if($row['social_name'] == 'LinkedIn') {
		$linkedin = $row['social_url'];
	}
	if($row['social_name'] == 'Google Plus') {
		$googleplus = $row['social_url'];
	}
	if($row['social_name'] == 'Pinterest') {
		$pinterest = $row['social_url'];
	}
	if($row['social_name'] == 'YouTube') {
		$youtube = $row['social_url'];
	}
	if($row['social_name'] == 'Instagram') {
		$instagram = $row['social_url'];
	}
	if($row['social_name'] == 'Tumblr') {
		$tumblr = $row['social_url'];
	}
	if($row['social_name'] == 'Flickr') {
		$flickr = $row['social_url'];
	}
	if($row['social_name'] == 'Reddit') {
		$reddit = $row['social_url'];
	}
	if($row['social_name'] == 'Snapchat') {
		$snapchat = $row['social_url'];
	}
	if($row['social_name'] == 'WhatsApp') {
		$whatsapp = $row['social_url'];
	}
	if($row['social_name'] == 'Quora') {
		$quora = $row['social_url'];
	}
	if($row['social_name'] == 'StumbleUpon') {
		$stumbleupon = $row['social_url'];
	}
	if($row['social_name'] == 'Delicious') {
		$delicious = $row['social_url'];
	}
	if($row['social_name'] == 'Digg') {
		$digg = $row['social_url'];
	}
}
?>

<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<form action="" method="post">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">						
						<p style="padding-bottom: 20px;">If you do not want to show a social media in your front end page, just leave the input field blank.</p>

						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Facebook</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="facebook" value="<?php echo $facebook; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Twitter</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="twitter" value="<?php echo $twitter; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">LinkedIn</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="linkedin" value="<?php echo $linkedin; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Google Plus</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="googleplus" value="<?php echo $googleplus; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Pinterest</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pinterest" value="<?php echo $pinterest; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">YouTube</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="youtube" value="<?php echo $youtube; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Instagram</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="instagram" value="<?php echo $instagram; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Tumblr</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="tumblr" value="<?php echo $tumblr; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Flickr</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="flickr" value="<?php echo $flickr; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Reddit</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="reddit" value="<?php echo $reddit; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Snapchat</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="snapchat" value="<?php echo $snapchat; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">WhatsApp</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="whatsapp" value="<?php echo $whatsapp; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Quora</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="quora" value="<?php echo $quora; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">StumbleUpon</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="stumbleupon" value="<?php echo $stumbleupon; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Delicious</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="delicious" value="<?php echo $delicious; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Digg</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="digg" value="<?php echo $digg; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>


</div>
</div>

<?php require_once('footer.php'); ?>