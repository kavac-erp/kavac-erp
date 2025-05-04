{{-- Plantilla para textos de ayuda (generales) en formularios --}}
@if (isset($codeSetting))
	<div class="row">
		<div class="col-12" id="formatCode">
			<span class="form-text">
				<strong>{{ __('Formato') }}:</strong> {{ __('prefijo-dígitos-año') }}
				<ul>
					<li id="formatCodePrefix">{{ __('Prefijo (requerido): 1 a 3 caracteres') }}</li>
					<li id="formatCodeDigits">{{ __('Digitos (requerido): 4 caracteres (mínimo), 8 caracteres (máximo)') }}</li>
					<li id="formatCodeYear">{{ __('Año (requerido): 2 o 4 caracteres (YY o YYYY)') }}</li>
				</ul>
				<strong>{{ __('Longitud total máxima') }}:</strong> {{ __('15 caracteres') }}<br>
				<strong>{{ __('Nota') }}:</strong> {{ __('Todas las letras deben ser mayúsculas') }}<br>
				<strong>{{ __('Ej.') }}</strong> FOR-00000000-YYYY
			</span>
		</div>
	</div>
	<hr>
@endif
