{!! Form::button('<i class="fa fa-check"></i>', [
	'class' => 'btn btn-success btn-xs btn-icon btn-action',
	'data-toggle' => 'tooltip', 'type' => 'button',
    'onclick' => "undelete_record('$route')",
	'title' => 'Restaurar registro eliminado',
	'disabled' => $disabled ?? false
]) !!}
