{!! Form::button('<i class="fa fa-trash-o"></i>', [
	'class' => 'btn btn-danger btn-xs btn-icon btn-action',
	'data-toggle' => 'tooltip', 'type' => 'button', 'onclick' => "delete_record('$route')",
	'title' => ((isset($disabled) && !$disabled) || !isset($disabled)) ? __('Eliminar registro') : __('El registro no puede ser eliminado'),
	'disabled' => $disabled ?? false
]) !!}
