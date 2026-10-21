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
	<script src="js/select2.full.min.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
	<script src="ckeditor/ckeditor.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

	<!-- Utilitário Global Pronto Saúde (SweetAlert2, Toastr, DarkMode, Confirmações) -->
	<script src="js/pronto-global.js"></script>

	<script>
	$(function () {
		// Inicialização de Tabelas DataTables em Português
		if ($.fn.dataTable) {
			$('#example1, #example2').DataTable({
				"language": {
					"sEmptyTable": "Nenhum registro encontrado",
					"sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
					"sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
					"sInfoFiltered": "(Filtrados de _MAX_ registros)",
					"sInfoPostFix": "",
					"sInfoThousands": ".",
					"sLengthMenu": "_MENU_ resultados por página",
					"sLoadingRecords": "Carregando...",
					"sProcessing": "Processando...",
					"sZeroRecords": "Nenhum registro encontrado",
					"sSearch": "Pesquisar:",
					"oPaginate": {
						"sNext": "Próximo",
						"sPrevious": "Anterior",
						"sFirst": "Primeiro",
						"sLast": "Último"
					},
					"oAria": {
						"sSortAscending": ": Ordenar colunas de forma ascendente",
						"sSortDescending": ": Ordenar colunas de forma descendente"
					}
				},
				"paging": true,
				"lengthChange": true,
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"responsive": true
			});
		}

		// Inicialização segura do Select2
		if ($.fn.select2) {
			$('.select2').select2({
				width: '100%',
				language: 'pt-BR'
			});
		}

		// Inicialização segura do Datepicker
		if ($.fn.datepicker) {
			$('#datepicker, #datepicker1').datepicker({
				autoclose: true,
				format: 'dd-mm-yyyy',
				todayBtn: 'linked'
			});
		}

		// Inicialização do Summernote
		if ($.fn.summernote) {
			$('#1, #2, #3, .summernote').summernote({
				tabsize: 2,
				height: 200,
				toolbar: [
					['style', ['style']],
					['font', ['bold', 'italic', 'underline', 'clear']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
					['table', ['table']],
					['insert', ['link', 'picture', 'video']],
					['view', ['fullscreen', 'codeview']]
				]
			});
		}
	});
	</script>
</body>
</html>