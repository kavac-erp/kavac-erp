<template>
	<div>
		<multiselectComponent v-model="selected_values" :options="options" :multiple="multiple" :taggable="taggable" :id="id"
							  :preselect-first="preselect_first" :track-by="track_by" :label="track_by"
							  placeholder="Seleccione..." :preserve-search="preserve_search" :hide-selected="hide_selected"
							  :clear-on-select="clear_on_select" :close-on-select="close_on_select" @select="onSelect"
							  :deselect-group-label="'Deseleccionar grupo'" :deselect-label="'Eliminar'"
							  :select-group-label="'Seleccionar grupo'" :select-label="'Seleccionar'"
							  :selected-label="'Seleccionado'" :tag-placeholder="'Crear una etiqueta'"
							  open-direction="bottom" @search-change="search_change" :internal-search="internal_search"
                              :searchable="searchable" @tag="add_tag" :options-limit="options_limit" :group-select="group_select"
							  :group-values="group_values" :group-label="group_label" :limit="limit" :limitText="limit_text"
							  :disabled="disabled"
		>
			<template slot="noOptions">Lista vacía</template>
			<div slot="singleLabel" slot-scope="props" v-if="track_by != ''">
                <span v-html="props.option[track_by]"></span>
            </div>
		</multiselectComponent>
	</div>
</template>

<script>
	import Multiselect from 'vue-multiselect';

	/** Elimina el prop loading por conflicto con el mixin de la aplicación */
	delete Multiselect.props.loading;

	Vue.component('multiselectComponent', Multiselect);

	export default {
		data () {
			return {
				selected_values: []
			}
		},
		props: {
			options: {
				type: Array,
				required: true,
			},
			track_by: {
				type: String,
				required: true,
			},
			taggable: {
				type: Boolean,
				required: false,
				default: true
			},
			id: {
				type: String,
				required: false,
				default: 'multiselect'
			},
			preselect_first: {
				type: Boolean,
				required: false,
				default: false
			},
			preserve_search: {
				type: Boolean,
				required: false,
				default: true
			},
			hide_selected: {
				type: Boolean,
				required: false,
				default: true
			},
			clear_on_select: {
				type: Boolean,
				required: false,
				default: true
			},
			close_on_select: {
				type: Boolean,
				required: false,
				default: true
			},
			value: {
				type: [String, Array, Object],
				required: false,
                default: function() {
                    return [];
                }
			},
			multiple: {
                type: Boolean,
                required: false,
                default: true
            },
			internal_search: {
                type: Boolean,
                required: false,
                default: true
            },
			searchable: {
                type: Boolean,
                required: false,
                default: true
            },
			search_change: {
                type: Function,
                required: false,
				default: function() {
                    return [];
                }
            },
			add_tag: {
                type: Function,
                required: false,
				default: function() {
                    return [];
                }
            },
			options_limit: {
                type: Number,
                required: false,
				default: Infinity
            },
			group_select: {
				type: Boolean,
                required: false,
                default: false
			},
			group_values: {
				type: String,
				required: false,
				default: ''
			},
			group_label: {
				type: String,
				required: false,
				default: ''
			},
			limit: {
				type: Number,
				required: false,
				default: Infinity
			},
			limit_text: {
				type: Function,
				default: count => count > 1 ? `+ ${count} seleccionados` : `+ ${count} seleccionado`
			},
			disabled:{
				type: Boolean,
				default: false
			}
		},
		methods: {
            /**
             * Evento que permite ejecutar las instrucciones necesarias al momento de seleccionar opciones del select
             *
             * @method    onSelect
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param     {string}    option    Texto de la opción
             * @param     {integer}   id        Identificador de la opción
             */
			onSelect (option, id) {
		    	//
		    },
		},
		watch: {
			selected_values: function() {
				this.$emit('input', this.selected_values)
			},
			value: function(selected) {
				this.selected_values = selected;
			}
		},
	};
</script>
