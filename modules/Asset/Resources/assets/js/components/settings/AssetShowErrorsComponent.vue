<template>
    <div :class="'alert alert-'+classAlert" role="alert" v-if="existErrors">
        <div class="container">
            <div class="alert-icon">
                <i class="now-ui-icons objects_support-17"></i>
            </div>
            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
            <ul>
                <li v-for="(error, index) in options" :key="index">{{ error }}</li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            options: [],
            classAlert: 'danger'
        }
    },
    computed: {
        existErrors: function() {
            const vm = this;
            // Si hay algun error en el componente padre actualiza
            if (vm.$parent.errors) {
                vm.options = vm.$parent.errors;
            };
            return (vm.options.length > 0);
        },
    },
    methods: {
        /**
         * [reset resetea valores de variables]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {
            this.options = [];
        },

        /**
         * [reset actualiza el valor de opciones]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        refresh() {
            const vm = this;
            vm.options = vm.$parent.errors;
        }
    }
};
</script>
