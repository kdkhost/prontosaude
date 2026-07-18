@php
    $dropzoneId = 'dropzone_' . Str::random(8);
@endphp

<div class="mb-3">
    <label class="form-label d-block">{{ $slot }}</label>
    <div class="dropzone" id="{{ $dropzoneId }}">
        <div class="dz-message" data-dz-message>
            <span>Arraste os arquivos aqui ou clique para fazer upload</span>
            <br>
            <small class="text-muted">{{ $hintDimensions }} (Máx: {{ $maxFilesize }}MB)</small>
        </div>
    </div>
    <!-- Campo hidden que armazenará o caminho do arquivo retornado pelo servidor -->
    <input type="hidden" name="{{ $name }}" id="{{ $dropzoneId }}_input">
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
            
            let myDropzone = new Dropzone("#{{ $dropzoneId }}", {
                url: "{{ $url }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                maxFilesize: {{ $maxFilesize }}, // MB
                acceptedFiles: "{{ $acceptedFiles }}",
                maxFiles: {{ $maxFiles }},
                addRemoveLinks: true,
                dictRemoveFile: "Remover Arquivo",
                dictCancelUpload: "Cancelar Upload",
                dictFileTooBig: "Arquivo muito grande. Max: {{ $maxFilesize }}MB.",
                
                success: function(file, response) {
                    if (response.success) {
                        document.getElementById('{{ $dropzoneId }}_input').value = response.path;
                        toastr.success('Upload concluído!');
                    }
                },
                error: function(file, response) {
                    let msg = response.error ? response.error : 'Erro ao realizar upload.';
                    toastr.error(msg);
                    this.removeFile(file);
                },
                removedfile: function(file) {
                    document.getElementById('{{ $dropzoneId }}_input').value = "";
                    file.previewElement.remove();
                }
            });
        }
    });
</script>
@endpush