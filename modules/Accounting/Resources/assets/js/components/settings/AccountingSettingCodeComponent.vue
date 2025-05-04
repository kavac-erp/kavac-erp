<template>
    <div>
        <h6>Asientos contables</h6>
        <form @submit.prevent="">
            <div class="card-body">
                <accounting-show-errors ref="settingCode" />
                <div class="row">
                    <div class="col-3" id="helpCodeReference">
                        <label for="entries_reference" class="control-label"
                            >Código de referencia</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            data-toggle="tooltip"
                            v-has-tooltip
                            title="Formato para el código de los reportes"
                            name="entries_reference"
                            v-model="code"
                            placeholder="Ej. XXX-00000000-YYYY"
                            :readonly="ref_code ? true : false"
                        />
                    </div>
                </div>
            </div>
            <div class="card-footer text-right" v-if="!ref_code">
                <buttonsDisplay
                    :route_list="app_url + '/accounting/settings'"
                    display="false"
                />
            </div>
        </form>
    </div>
</template>
<script>
export default {
    props: {
        ref_code: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            code: "",
        };
    },
    mounted() {
        if (this.ref_code) {
            this.code =
                this.ref_code.format_prefix +
                "-" +
                this.ref_code.format_digits +
                "-" +
                this.ref_code.format_year;
        }

        $('*[placeholder$="XXX-00000000-YYYY"]').each(function () {
            Inputmask({
                regex: "^[A-Z]{1,3}$-[0]{4,8}-^([Y]{2}|[Y]{4})$",
                clearMaskOnLostFocus: true,
            }).mask(this);
        });
    },
    methods: {
        reset() {
            if (!this.ref_code) {
                this.code = "";
            }
        },
        validatedFormatCode() {
            var res = false;
            var errors = [];
            if (this.code != "") {
                var prefix = this.code.split("-")[0];
                var digits = this.code.split("-")[1];
                var year = this.code.split("-")[2];
                if (
                    !prefix ||
                    prefix.length < 1 ||
                    prefix.length > 3 ||
                    !digits ||
                    digits.length < 6 ||
                    digits.length > 8 ||
                    !year ||
                    (year.toUpperCase() != "YY" && year.toUpperCase() != "YYYY")
                ) {
                    errors.push(
                        "El campo código de referencia no cumple con el formato valido."
                    );
                    res = true;
                }
            } else {
                errors.push("El campo código de referencia es obligatorio.");
                res = true;
            }

            this.$refs.settingCode.showAlertMessages(errors);
            return res;
        },
        createRecord() {
            const vm = this;
            if (vm.validatedFormatCode()) {
                return;
            }
            axios
                .post(`${window.app_url}/accounting/settings/code`, {
                    entries_reference: vm.code,
                })
                .then(() => {
                    vm.showMessage("store");
                    vm.redirect_back(`${window.app_url}/accounting/settings`);
                });
        },
    },
    watch: {
        code: function () {
            if (this.$refs.settingCode) {
                this.$refs.settingCode.reset();
            }
        },
    },
};
</script>
