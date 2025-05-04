<template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="status" slot-scope="props" class="text-center">
            <div v-if="props.row.active">
                <span class="badge badge-success"><strong>Activa</strong></span>
            </div>
            <div v-else>
                <span class="badge badge-warning"
                    ><strong>Inactiva</strong></span
                >
            </div>
        </div>
        <div slot="type" slot-scope="props" class="text-center">
            <div v-if="props.row.type == 'resource'">
                <span class="badge badge-success"
                    ><strong>Ingreso</strong></span
                >
            </div>
            <div v-else-if="props.row.type == 'egress'">
                <span class="badge badge-error"><strong>Egreso</strong></span>
            </div>
            <div v-else>
                <span class="badge badge-warning"
                    ><strong>No definido</strong></span
                >
            </div>
        </div>
        <div slot="original" slot-scope="props" class="text-center">
            <div v-if="props.row.original">
                <span class="badge badge-success"><strong>SI</strong></span>
            </div>
            <div v-else>
                <span class="badge badge-warning"><strong>NO</strong></span>
            </div>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <button
                @click="initRecord(props.row)"
                class="btn btn-warning btn-xs btn-icon btn-action"
                title="Modificar registro"
                data-toggle="tooltip"
                v-has-tooltip
            >
                <i class="fa fa-edit"></i>
            </button>
            <button
                @click="deleteRecord(props.row.id, '/accounting/accounts')"
                class="btn btn-danger btn-xs btn-icon btn-action"
                title="Eliminar registro"
                data-toggle="tooltip"
                v-has-tooltip
            >
                <i class="fa fa-trash-o"></i>
            </button>
        </div>
    </v-client-table>
</template>
<script>
export default {
    props: {
        initial_records: {
            type: Array,
            default() {
                return [];
            },
        },
    },
    data() {
        return {
            records: [],
            columns: [
                "code",
                "denomination",
                "type",
                "status",
                "original",
                "id",
            ],
        };
    },
    created() {
        this.table_options.headings = {
            code: "CÓDIGO",
            denomination: "DENOMINACIÓN",
            status: "ESTATUS",
            type: "TIPO DE CUENTA",
            original: "ORIGINAL",
            id: "ACCIÓN",
        };
        this.table_options.sortable = ["code", "denomination"];
        this.table_options.filterable = ["code", "denomination"];
        this.table_options.columnsClasses = {
            code: "col-xs-1",
            denomination: "col-xs-6",
            Type: "col-xs-1",
            status: "col-xs-1",
            original: "col-xs-1",
            id: "col-xs-2",
        };
    },
    methods: {
        initRecord: function (data) {
            EventBus.$emit("load:data-account-form", data);
        },
    },
    watch: {
        initial_records: function (res) {
            this.records = res;
        },
    },
};
</script>
