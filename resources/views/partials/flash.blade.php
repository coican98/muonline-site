@if(session('success'))
    <div class="flash-message flash-success" role="status">
        <i class="fas fa-check-circle" aria-hidden="true" style="margin-top: 2px;"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Fechar" title="Fechar">&times;</button>
    </div>
@endif

@if(session('error'))
    <div class="flash-message flash-error" role="alert">
        <i class="fas fa-exclamation-triangle" aria-hidden="true" style="margin-top: 2px;"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Fechar" title="Fechar">&times;</button>
    </div>
@endif

@if(session('info'))
    <div class="flash-message flash-info" role="status">
        <i class="fas fa-info-circle" aria-hidden="true" style="margin-top: 2px;"></i>
        <div>{{ session('info') }}</div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Fechar" title="Fechar">&times;</button>
    </div>
@endif

@if(session('warning'))
    <div class="flash-message flash-warning" role="alert">
        <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top: 2px;"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Fechar" title="Fechar">&times;</button>
    </div>
@endif

@if($errors->any())
    <div class="flash-message flash-error" role="alert">
        <i class="fas fa-exclamation-circle" aria-hidden="true" style="margin-top: 2px;"></i>
        <div>
            <strong>Ocorreram erros de validação:</strong>
            <ul class="field-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Fechar" title="Fechar">&times;</button>
    </div>
@endif
