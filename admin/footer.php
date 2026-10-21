		</div>

	</div>

	<script src="js/jquery-2.2.4.min.js"></script>
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
	<script src="js/jquery.fancybox.pack.js"></script>
	<script src="js/app.min.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
    <script src="js/on-off-switch-onload.js"></script>
	<script src="ckeditor/ckeditor.js"></script>
	<script src="js/demo.js"></script>
	<script src="js/sweetalert2.all.min.js"></script>
	<script src="js/toastr.min.js"></script>
	<script src="js/pronto-global.js"></script>

	<script>
	  $(function () {
	    // Inicialização segura do Summernote
	    if ($.fn.summernote) {
	      $('#1').summernote({
	        placeholder: 'Descrição sobre o serviço',
	        tabsize: 2,
	        height: 120,
	        lang: 'pt-BR',
	        toolbar: [
	          ['font', ['bold', 'underline', 'clear']],
	          ['color', ['color']],
	          ['para', ['ul', 'ol', 'paragraph']],
	          ['table', ['table']],
	          ['insert', ['link', 'picture', 'video']],
	          ['view', ['fullscreen', 'codeview']]
	        ]
	      });

	      $('#2').summernote({
	        placeholder: 'Pequena Descrição sobre o serviço',
	        tabsize: 2,
	        lang: 'pt-BR',
	        toolbar: [
	          ['font', ['bold', 'underline', 'clear']],
	          ['color', ['color']],
	          ['para', ['paragraph']],
	          ['table', ['table']],
	          ['insert', ['link', 'picture', 'video']],
	          ['view', ['fullscreen', 'codeview']]
	        ]
	      });

	      $('#3').summernote({
	        height: 450,
	        focus: false,
	        toolbar: [
	          ['style', ['style']],
	          ['font', ['bold', 'italic', 'underline', 'clear']],
	          ['fontname', ['fontname']],
	          ['para', ['ul', 'ol', 'paragraph']],
	          ['height', ['height']],
	          ['table', ['table']],
	          ['insert', ['link', 'picture', 'hr']],
	          ['color', ['color']],
	          ['view', ['fullscreen', 'codeview']]
	        ],
	        airMode: false
	      });
	    }

	    // Initialize Select2 Elements
	    if ($.fn.select2) {
	      $(".select2").select2();
	    }

	    // Datemask dd/mm/yyyy
	    if ($.fn.inputmask) {
	      $("#datemask").inputmask("dd-mm-yyyy", {"placeholder": "dd-mm-yyyy"});
	      $("#datemask2").inputmask("mm-dd-yyyy", {"placeholder": "mm-dd-yyyy"});
	      $("[data-mask]").inputmask();
	    }

	    // Date picker
	    if ($.fn.datepicker) {
	      $('#datepicker, #datepicker1').datepicker({
	        autoclose: true,
	        format: 'dd-mm-yyyy',
	        todayBtn: 'linked'
	      });
	    }
	    
	    if ($.fn.datetimepicker) {
	      $('#prontosaude').datetimepicker({
	        format: 'yyyy-mm-dd hh:ii',
	        language: "pt-BR"
	      });
	    }

	    // iCheck
	    if ($.fn.iCheck) {
	      $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
	        checkboxClass: 'icheckbox_minimal-blue',
	        radioClass: 'iradio_minimal-blue'
	      });
	      $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
	        checkboxClass: 'icheckbox_minimal-red',
	        radioClass: 'iradio_minimal-red'
	      });
	      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
	        checkboxClass: 'icheckbox_flat-green',
	        radioClass: 'iradio_flat-green'
	      });
	    }

        if ($.fn.DataTable) {
          if ($('#minhaTabela').length) {
            $('#minhaTabela').DataTable({
              "language": {
                "lengthMenu": "Mostrando _MENU_ registros por página",
                "zeroRecords": "Nada encontrado",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhum registro disponível",
                "infoFiltered": "(filtrado de _MAX_ registros no total)"
              }
            });
          }

          if ($('#example1').length) {
            $("#example1").DataTable();
          }

          if ($('#example2').length) {
            $('#example2').DataTable({
              "paging": true,
              "lengthChange": false,
              "searching": false,
              "ordering": true,
              "info": true,
              "autoWidth": false
            });
          }
        }

	    $('#confirm-delete').on('show.bs.modal', function(e) {
	      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
	    });

        // Conversão automática de .callout alerts para notificações Toastr
        $('.callout-success').each(function() {
            var text = $(this).text().trim();
            if (text && typeof toastr !== 'undefined') {
                toastr.success(text, 'Sucesso');
            }
        });
        $('.callout-danger').each(function() {
            var text = $(this).text().trim();
            if (text && typeof toastr !== 'undefined') {
                toastr.error(text, 'Atenção');
            }
        });

        // Efeito fade para callouts existentes
        setTimeout(function () {
          $(".callout").fadeOut();
        }, 4000);
	  });

	  $(document).ready(function () {
	    if (typeof CKEDITOR !== 'undefined' && $('#editor1').length) {
	      CKEDITOR.replace('editor1');
	    }
	  }); 
	</script>

	<script type="text/javascript">
		function showDiv(elem){
			if(elem.value == 0) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 1) {
		      	document.getElementById('photo_div').style.display = "block";
		      	document.getElementById('photo_div_existing').style.display = "block";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 2) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('photo_div_existing').style.display = "none";
		      	document.getElementById('icon_div').style.display = "block";
		   	}
		}
		function showContentInputArea(elem){
		   if(elem.value == 'Full Width Page Layout') {
		      	document.getElementById('showPageContent').style.display = "block";
		   } else {
		   		document.getElementById('showPageContent').style.display = "none";
		   }
		}
	</script>

	<script type="text/javascript">
        $(document).ready(function () {
            $("#btnAddFaq").click(function () {
                var trNew1 = '<tr>' +
                    '<td><input autocomplete="off" type="text" class="form-control" name="fq_title[]" style="width:100%"></td>' +
                    '<td><textarea class="form-control" cols="30" rows="10" name="fq_content[]" style="width:100%;height:50px;"></textarea></td>' +
                    '<td><a href="javascript:void(0)" class="Delete1 btn btn-danger btn-xs">X</a></td>' +
                    '</tr>';
                $("#fqSection tbody").append(trNew1);
            });

            $('#fqSection').delegate('a.Delete1', 'click', function () {
                $(this).closest('tr').fadeOut('slow', function() { $(this).remove(); });
                return false;
            });

            $("#btnAddOpenningHour").click(function () {
                var trNew1 = '<tr>' +
                    '<td><input autocomplete="off" type="text" class="form-control" name="oh_day[]" style="width:100%"></td>' +
                    '<td><input autocomplete="off" type="text" class="form-control" name="oh_time[]" style="width:100%"></td>' +
                    '<td><a href="javascript:void(0)" class="Delete1 btn btn-danger btn-xs">X</a></td>' +
                    '</tr>';
                $("#ohSection tbody").append(trNew1);
            });

            $('#ohSection').delegate('a.Delete1', 'click', function () {
                $(this).closest('tr').fadeOut('slow', function() { $(this).remove(); });
                return false;
            });
        });
    </script>
</body>
</html>