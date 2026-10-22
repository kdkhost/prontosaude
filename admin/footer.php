		</main>
		<!-- Fim do Conteúdo Principal -->

		<!-- Rodapé do Painel (Footer) -->
		<footer class="app-footer text-body-secondary py-3 px-4 border-top bg-body">
			<div class="float-end d-none d-sm-inline">
				<span class="badge text-bg-success-subtle text-success border border-success-subtle me-1">
					<i class="bi bi-check-circle-fill me-1"></i>PHP 8.4 Ativo
				</span>
				<span class="badge text-bg-info-subtle text-info border border-info-subtle">
					<i class="bi bi-layers-fill me-1"></i>AdminLTE 4
				</span>
			</div>
			<strong>&copy; <?= date('Y'); ?> <a href="<?= BASE_URL ?>" target="_blank" class="text-decoration-none text-primary fw-semibold"><?= htmlspecialchars($empresa); ?></a>.</strong> Sistema Hospitalar e Clínico Integrado.
		</footer>

		<!-- Botão Flutuante Voltar ao Topo no Painel Admin -->
		<button type="button" id="adminBackToTop" class="btn btn-primary shadow-lg rounded-circle" title="Voltar ao Topo" aria-label="Voltar ao Topo" style="position: fixed; bottom: 25px; right: 25px; width: 44px; height: 44px; display: none; align-items: center; justify-content: center; z-index: 1045; transition: opacity 0.3s ease, transform 0.2s ease; border: 2px solid rgba(255,255,255,0.25);">
			<i class="bi bi-arrow-up fs-5"></i>
		</button>

	</div><!-- ./app-wrapper -->

	<!-- Bibliotecas Core do AdminLTE 4 -->
	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="js/overlayscrollbars.browser.es6.min.js"></script>
	<script src="js/bootstrap5.bundle.min.js"></script>
	<script src="js/adminlte4.min.js"></script>
	
	<!-- Bibliotecas de KPIs, Gráficos e Notificações -->
	<script src="js/apexcharts.min.js"></script>
	<script src="js/sweetalert2.all.min.js"></script>
	<script src="js/toastr.min.js"></script>

	<!-- Plugins de Formulários e Tabelas -->
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/dataTables.responsive.min.js"></script>
	<script src="js/responsive.bootstrap4.min.js"></script>
	<script src="js/select2.full.min.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
	<script src="ckeditor/ckeditor.js"></script>
	<script>
	if (window.jQuery && !window.jQuery.fn.tooltip) {
		window.jQuery.fn.tooltip = function () { return this; };
	}
	</script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js"></script>

	<!-- Utilitário Global Pronto Saúde (SweetAlert2, Toastr, DarkMode, Confirmações) -->
	<script src="js/pronto-global.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-uploader.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-slug.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-masks.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-security.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-switches.js?v=<?= time(); ?>"></script>
	<script src="js/pronto-spa.js?v=<?= time(); ?>"></script>

	<script>
	// Controle do Botão Voltar ao Topo no Painel Admin
	document.addEventListener('DOMContentLoaded', function () {
		const btnAdminTop = document.getElementById('adminBackToTop');
		if (btnAdminTop) {
			window.addEventListener('scroll', function () {
				if (window.scrollY > 200) {
					btnAdminTop.style.display = 'flex';
					btnAdminTop.style.opacity = '1';
				} else {
					btnAdminTop.style.opacity = '0';
					setTimeout(function () {
						if (window.scrollY <= 200) btnAdminTop.style.display = 'none';
					}, 250);
				}
			});
			btnAdminTop.addEventListener('click', function (e) {
				e.preventDefault();
				window.scrollTo({ top: 0, behavior: 'smooth' });
			});
		}
	});
	</script>
</body>
</html>