<template>
    <div :class="{'btn-display': (display==='true')}">
        <button
			type="button" @click="$parent.reset"
			class="btn btn-default btn-icon btn-round"
			data-toggle="tooltip"
            title="Borrar datos del formulario"
		>
            <i class="fa fa-eraser"></i>
        </button>
        <button
			type="button"
			@click="$parent.redirect_back($parent.route_list)"
			title="Cancelar y regresar"
            class="btn btn-warning btn-icon btn-round"
			data-toggle="tooltip"
		>
            <i class="fa fa-ban"></i>
        </button>
        <button
			type="button"
			@click="$parent.createRecord"
			class="btn btn-success btn-icon btn-round"
            data-toggle="tooltip"
			title="Guardar registro"
		>
            <i class="fa fa-save"></i>
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            display: {
                type: String,
                required: false,
                default: 'true'
            }
        },
        mounted() {
            /**
             * Muestra los botones cuando se baja el scroll de la pantalla a una altura predeterminada y
             * los oculta cuando dicha altura es menor a la indicada
             */
            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) {
                    $('.btn-display').fadeIn();
                }
                else {
                    $('.btn-display').fadeOut();
                }
            });
        }
    };
</script>
