<template>
    <div class="alert alert-danger" role="alert" v-if="listErrors.length > 0">
        <div class="container">
            <div class="alert-icon">
                <i class="now-ui-icons objects_support-17"></i>
            </div>
            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="clearErrors();">
                <span aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </span>
            </button>

            <ul>
                <li v-for="(error, index) in listErrors" :key="index">{{ error }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                //
            }
        },
        methods: {
            clearErrors() {
                this.$parent.errors = [];
            }
        },
        props: ['listErrors']
    };
</script>