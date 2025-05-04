<template>
	<div class="card">
		<div class="card-header">
			<h6 class="card-title text-uppercase">Calculo del Salario Base</h6>
			<div class="card-btns">
				<a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
				   title="Ir atrás" data-toggle="tooltip">
					<i class="fa fa-reply"></i>
				</a>
				<a href="#" class="card-minimize btn btn-card-action btn-round" title="Minimizar"
				   data-toggle="tooltip">
					<i class="now-ui-icons arrows-1_minimal-up"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class="alert alert-danger" v-if="errors.length > 0">
				<div class="container">
					<div class="alert-icon">
						<i class="now-ui-icons objects_support-17"></i>
					</div>
					<strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"
							@click.prevent="errors = []">
						<span aria-hidden="true">
							<i class="now-ui-icons ui-1_simple-remove"></i>
						</span>
					</button>
					<ul>
						<li v-for="error in errors" :key="error">{{ error }}</li>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class=" form-group">
						<label>Quincena</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_periodicity" name="periodicity" id="sel_quincena">
								<label class="custom-control-label" for="sel_quincena"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class=" form-group">
						<label>Mes</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_periodicity" name="periodicity" id="sel_mes">
								<label class="custom-control-label" for="sel_mes"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class=" form-group">
						<label>Año</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_periodicity" name="periodicity" id="sel_year">
								<label class="custom-control-label" for="sel_year"></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class=" form-group is-required">
						<label>Tipo de Cargo</label>
						<select2 :options="position_types"
							v-model="record.position_type_id"></select2>
					</div>
				</div>
				<div class="col-md-6">
					<div class=" form-group is-required">
						<label>Tipo de Tabulador</label>
						<select2 :options="tabulator_types" @input="resetTabulator()"
							v-model="record.tabulator_type_id"></select2>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class=" form-group">
						<label>Valor Absoluto</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_incidence_value" name="incidence_value" 
									   id="sel_neto_value" value="neto_value">
								<label class="custom-control-label" for="sel_neto_value"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class=" form-group">
						<label>Unidad Tributaria</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_incidence_value" name="incidence_value" 
									   id="sel_tax_unit" value="tax_unit">
								<label class="custom-control-label" for="sel_tax_unit"></label>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class=" form-group">
						<label>Porcentaje</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_incidence_value" name="incidence_value" 
									   id="sel_percent" value="percent">
								<label class="custom-control-label" for="sel_percent"></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class=" form-group is-required">
						<label>Valor</label>
						<input type="text" placeholder="" data-toggle="tooltip"
										   title=""
										   class="form-control input-sm" v-model="record.incidence_value">
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer text-right">
        	<button type="button" @click="reset()"
					class="btn btn-default btn-icon btn-round"
					title ="Borrar datos del formulario">
					<i class="fa fa-eraser"></i>
			</button>

        	<button type="button"
        			class="btn btn-warning btn-icon btn-round btn-modal-close"
        			data-dismiss="modal"
        			title="Cancelar y regresar">
        			<i class="fa fa-ban"></i>
        	</button>

        	<button type="button"  @click="createRecord('')"
        			class="btn btn-success btn-icon btn-round btn-modal-save"
        			title="Guardar registro">
        		<i class="fa fa-save"></i>
            </button>
        </div>
    </div>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					position_type_id: '',
					tabulator_type_id: '',
					incidence_value: '',
				},

				records: [],
				errors: [],

				position_types: [],
				tabulator_types: [
					{"id":"","text":"Seleccione..."},
					{"id":1,"text":"Tabulador Mixto"},
					{"id":2,"text":"Tabulador Horizontal"},
					{"id":3,"text":"Tabulador Vertical"}],
			}
		},
		methods: {
			reset() {
				this.record = {
					id: '',
					position_type_id: '',
					tabulator_type_id: '',
				}

			},
			readRecords(url) {

			},
		},
		created() {
		},
		mounted() {
			const vm = this;
			vm.getPositionTypes();
		},
	};
</script>
