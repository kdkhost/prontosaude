<?php require_once('header.php'); ?>
		
<?php
// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: '.BASE_URL);
	exit;
}

// Getting the news detailed data from the news slug
$statement = $pdo->prepare("SELECT
							t1.news_title,
							t1.news_slug,
							t1.news_content,
							t1.news_date,
							t1.publisher,
							t1.photo,
							t1.category_id,
							t2.category_name,
							t2.category_slug
                           	FROM tbl_news t1
                           	LEFT JOIN tbl_category t2
                           	ON t1.category_id = t2.category_id
                           	WHERE t1.news_slug=?");
$statement->execute(array($slug));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	header('location: '.BASE_URL);
	exit;
}

$news_title    = $row['news_title'] ?? '';
$news_content  = $row['news_content'] ?? '';
$news_date     = $row['news_date'] ?? '';
$publisher     = $row['publisher'] ?? '';
$photo         = $row['photo'] ?? '';
$category_id   = $row['category_id'] ?? '';
$category_slug = $row['category_slug'] ?? '';
$category_name = $row['category_name'] ?? '';

// Atualização atômica e otimizada da contagem de visualizações
$update_stmt = $pdo->prepare("UPDATE tbl_news SET total_view = total_view + 1 WHERE news_slug=?");
$update_stmt->execute(array($slug));
?>


<?php
$news_banner_src = (!empty($photo) && file_exists('assets/uploads/' . $photo)) ? BASE_URL . 'assets/uploads/' . $photo : BASE_URL . 'assets/uploads/department-1.png';
?>
<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= $news_banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($news_title); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->


<!-- Blog Start -->
<section class="blog">
	<div class="container">
		<div class="row">
			<div class="col-md-9">
				
				<!-- Blog Classic Start -->
				<div class="blog-grid">
					<div class="row">
						<div class="col-md-12">
							

							<!-- Post Item Start -->
							<div class="post-item">
								<div class="image-holder">
									<img class="img-responsive" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo ?>" alt="<?php echo $news_title; ?>">
								</div>
								<div class="text">
									<h3><?php echo $news_title; ?></h3>
									<ul class="status">
										<li><i class="fa fa-tag"></i>Categoria: <a href="<?php echo BASE_URL; ?>category/<?php echo $category_slug; ?>"><?php echo $category_name; ?></a></li>
										<li><i class="fa fa-calendar"></i>Data: <?php echo $news_date; ?></li>
									</ul>
									<p>
										<?php echo $news_content; ?>
									</p>
									<h3>Compartilhe isso</h3>
									<div class="sharethis-inline-share-buttons"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<h3>Comentários</h3>
									<?php
									// Getting the full url of the current page
									$final_url = BASE_URL . 'news/' . $_REQUEST['slug'];
									?>
									<!-- Facebook Comment Main Code (got from facebook website) -->
									<div class="fb-comments" data-href="<?php echo $final_url; ?>" data-numposts="5"></div>
								</div>
							</div>
							<!-- Post Item End -->

						</div>

					</div>
				</div>
				<!-- Blog Classic End -->
				



			</div>
			<div class="col-md-3">
				
				<?php require_once('sidebar.php'); ?>
			
			</div>

			


		</div>
	</div>
</section>
<!-- Blog End -->


<?php require_once('footer.php'); ?>