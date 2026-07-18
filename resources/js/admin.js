import * as bootstrap from 'bootstrap';
import 'admin-lte';
import Swal from 'sweetalert2';
import toastr from 'toastr';
import Dropzone from 'dropzone';

window.bootstrap = bootstrap;
window.Swal = Swal;
window.toastr = toastr;
window.Dropzone = Dropzone;

// Configurações Globais Toastr
toastr.options = {
  "closeButton": true,
  "progressBar": true,
  "positionClass": "toast-top-right",
  "timeOut": "3000"
};

// Exemplo Global de Confirmação com SweetAlert2
window.confirmAction = function(title, text, confirmCallback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, confirmar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            confirmCallback();
        }
    });
};
