<template>
    <div class="formula-calculator">
        <div v-if="withDisplay" class="form-group mb-4" :class="(requiredClass) ? 'is-required' : ''">
            <label>Fórmula</label>
            <textarea type="text"
                style="font-size: 1rem; font-weight: bold;"
                class="form-control input-sm"
                data-toggle="tooltip"
                readonly
                title="Fórmula a aplicar para la deducción. Utilice la siguiente calculadora para establecer los parámetros de la fórmula"
                rows="3" v-model="$parent.record[formulaInput]">
            </textarea>
        </div>
        <div class="form-group row mb-n1">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-clear btn-operator" data-toggle="tooltip"
                    title="Reinicia el valor del campo de la fórmula. Al pulsar la tecla D o Supr o Del se limpia el campo de la fórmula." @click="setFormula('C')"
                >
                    C
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-backspace btn-operator" data-toggle="tooltip"
                    title="presione para desahacer el último carácter" style="padding-right:11px;padding-left:11px;"
                    @click="setFormula('backspace')"
                >
                    <i class="fa fa-long-arrow-left"></i>
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-start-parenthesis btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de apertura de paréntesis" @click="setFormula('(')"
                >
                    (
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-end-parenthesis btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de cierre de paréntesis" @click="setFormula(')')"
                >
                    )
                </button>
            </div>
        </div>
        <div class="form-group row mb-n1">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-seven" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(7)"
                >
                    7
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-eight" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(8)"
                >
                    8
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-nine" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(9)"
                >
                    9
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-division btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de división" @click="setFormula('/')"
                >
                    /
                </button>
            </div>
        </div>
        <div class="form-group row mb-n1">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-four" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(4)"
                >
                    4
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-five" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(5)"
                >
                    5
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-six" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(6)"
                >
                    6
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-asterisk btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de multiplicación" @click="setFormula('*')"
                >
                    *
                </button>
            </div>
        </div>
        <div class="form-group row mb-n1">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-one" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(1)"
                >
                    1
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-two" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(2)"
                >
                    2
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-three" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(3)"
                >
                    3
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-minus btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de resta" @click="setFormula('-')"
                >
                    -
                </button>
            </div>
        </div>
        <div class="form-group row mb-n1">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-cero" data-toggle="tooltip"
                    title="presione para agregar este dígito" @click="setFormula(0)"
                >
                    0
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-dot" data-toggle="tooltip"
                    title="presione para agregar el separador de decimales" @click="setFormula('.')"
                >
                    .
                </button>
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-plus btn-operator" data-toggle="tooltip"
                    title="presione para agregar el signo de suma" @click="setFormula('+')"
                >
                    +
                </button>
            </div>
        </div>
        <div class="form-group row" v-if="withAmountButton">
            <div class="col-12 col-md-8 text-center mx-auto">
                <button
                    type="button" class="btn btn-info btn-sm btn-formula btn-variable" data-toggle="tooltip"
                    title="Variable a usar para el monto deducible cuando se realice el cálculo. Al pulsar la tecla D se agrega este valor a la fórmula." @click="setFormula(amountButtonValue)"
                >
                    {{ amountButtonText }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                elActive: false // Permite identificar si un elemento del formulario posee el foco en cuyo caso no se permite el uso del teclado
            }
        },
        props: {
            withDisplay: {
                type: Boolean,
                required: false,
                default: true
            },
            withAmountButton: {
                type: Boolean,
                required: false,
                default: false
            },
            amountButtonText: {
                type: String,
                required: false,
                default: '(D)EDUCIBLE'
            },
            amountButtonValue: {
                type: String,
                required: false,
                default: 'monto'
            },
            formulaInput: {
                type: String,
                required: true
            },
            requiredClass: {
                type: Boolean,
                required: false,
                default: false
            },
        },
        methods: {
            /**
             * Establece los datos de la fórmula según los elementos usados en la calculadora
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param   {String|Integer}  value  Valor del botón de la calculadora
             */
            setFormula(value) {
                const vm = this;
                let formulaDisplay = vm.$parent.record[vm.formulaInput];
                let symbols = ['+', '-', '/', '*', '%'];

                if (value === 'backspace') {
                    vm.$parent.record[vm.formulaInput] = vm.$parent.record[vm.formulaInput].substring(0, vm.$parent.record[vm.formulaInput].length-1);
                    return false;
                } else if (value === 'C') {
                    vm.$parent.record[vm.formulaInput] = '';
                    return false;
                }

                if (formulaDisplay.length === 0 && symbols.includes(value)) {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                        'No esta permitido indicar símbolos como primer elemento de la fórmula'
                    );
                    return false;
                } else if (symbols.includes(formulaDisplay.slice(-1)) && symbols.includes(value)) {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning',
                        'No esta permitido indicar símbolos de forma consecutiva'
                    );
                    return false;
                }

                if (value === 0 && vm.$parent.record[vm.formulaInput].slice(-1) === '/') {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning', 'La división por cero no esta permitida'
                    );
                    return false;
                }
                vm.$parent.record[vm.formulaInput] += value;
            }
        },
        mounted() {
            const vm = this;
            document.addEventListener('keydown', (event) => {
                vm.elActive = false;
                // Si alguno de estos elementos está activo, deshabilita los eventos del teclado para la calculadora de fórmulas
                const input = document.querySelectorAll("input, select, .select2, textarea, .ck-focused");
                input.forEach(el => {
                    if (el === document.activeElement) {
                        vm.elActive = true;
                    }
                });

                let keyCode = event.key;

                let selector = {
                    '1': '.btn-one',
                    '2': '.btn-two',
                    '3': '.btn-three',
                    '4': '.btn-four',
                    '5': '.btn-five',
                    '6': '.btn-six',
                    '7': '.btn-seven',
                    '8': '.btn-eight',
                    '9': '.btn-nine',
                    '0': '.btn-cero',
                    'backspace': '.btn-backspace',
                    '(': '.btn-start-parenthesis',
                    ')': '.btn-end-parenthesis',
                    '%': '.btn-percent',
                    '+': '.btn-plus',
                    '-': '.btn-minus',
                    '*': '.btn-asterisk',
                    '/': '.btn-division',
                    'c': '.btn-clear',
                    'delete': '.btn-clear',
                    'v': '.btn-variable',
                    'd': '.btn-variable'
                };

                if (!vm.elActive && keyCode.toLowerCase() in selector) {
                    document.querySelector(selector[keyCode.toLowerCase()]).click();
                }
            });
        }
    }
</script>
