<template>
    <section class="row" id="draggableTable">
        <div class="row col-md-12 justify-content-between d-flex">
            <div class="form-group form-inline">
                <div class="VueTables__search-field">
                    <label class="">Buscar:</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar..."
                        v-model="search"
                    />
                </div>
            </div>
            <div class="form-group form-inline">
                <div class="VueTables__limit-field">
                    <label class="">Registros</label>
                    <select2
                        :options="perPageValues"
                        v-model="perPage"
                    ></select2>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="overflow-x: auto">
            <table
                ref="draggableTable"
                class="table table-hover table-striped draggable-table table-responsive"
                style="display: table"
            >
                <thead>
                    <tr>
                        <th
                            v-for="(column, index) in columns"
                            :key="index"
                            :class="{
                                'draggable-column': true,
                                'highlighted-column':
                                    index !== dragIndex && dragIndex !== null,
                            }"
                            style="min-width: 100px"
                        >
                            <span>{{ column.name }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rIndex) in visibleRows" :key="rIndex">
                        <td v-for="(column, index) in columns" :key="index">
                            <span v-if="column.type == 'text'">
                                {{ row[column.name] }}
                            </span>
                            <span
                                v-else-if="
                                    column.type == 'input' && column.group
                                "
                            >
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    v-model="
                                        inputValues[
                                            column.name + '-' + row.staff_id
                                        ]
                                    "
                                    v-input-mask
                                    data-inputmask="'alias': 'integer', 'allowMinus': 'false'"
                                    maxlength="3"
                                    :disabled="column.disabled"
                                />
                            </span>
                            <span v-else-if="column.type == 'input_text'">
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    v-model="
                                        inputValues[
                                            column.name + '-' + row.staff_id
                                        ]
                                    "
                                    v-is-text
                                    :disabled="column.disabled"
                                />
                            </span>
                            <span v-else-if="column.type == 'custom'">
                                <div
                                    class="d-flex text-center"
                                    v-for="(component, i) in custom_components"
                                    :key="i"
                                >
                                    <div
                                        class="col-6"
                                        v-if="
                                            column.name == component['column']
                                        "
                                    >
                                        <i
                                            class="fa fa-plus-circle cursor-pointer"
                                            v-if="
                                                !inputValues[
                                                    column.name +
                                                        '-' +
                                                        row.staff_id
                                                ]
                                            "
                                            @click="
                                                setCustomComponent(
                                                    component['ref'],
                                                    row.staff_id,
                                                    component['modalId']
                                                )
                                            "
                                        >
                                        </i>
                                        <i
                                            class="fa fa-eye cursor-pointer"
                                            v-else
                                            @click="
                                                setCustomComponent(
                                                    component['ref'],
                                                    row.staff_id,
                                                    component['modalId'],
                                                    inputValues[
                                                        column.name +
                                                            '-' +
                                                            row.staff_id
                                                    ],
                                                    column.field
                                                )
                                            "
                                        >
                                        </i>
                                    </div>
                                </div>
                            </span>
                            <span
                                v-else-if="column.type == 'subtotal'"
                                :class="
                                    column.max &&
                                    calculate[
                                        column.group + '-' + row.staff_id
                                    ] > column.max
                                        ? 'form-control text-center align-middle info-danger'
                                        : 'form-control text-center align-middle'
                                "
                            >
                                <input
                                    type="hidden"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    :v-model="
                                        (inputValues[
                                            'subtotal - ' +
                                                columns[index - 1].group +
                                                '-' +
                                                row.staff_id
                                        ] =
                                            calculate[
                                                columns[index - 1].group +
                                                    '-' +
                                                    row.staff_id
                                            ])
                                    "
                                    disabled
                                />
                                {{
                                    calculate[
                                        columns[index - 1].group +
                                            "-" +
                                            row.staff_id
                                    ]
                                }}
                            </span>
                            <span v-else-if="column.type == 'total'">
                                <input
                                    type="hidden"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Indique el valor del campo"
                                    :v-model="
                                        (inputValues['total-' + row.staff_id] =
                                            calculate['total-' + row.staff_id])
                                    "
                                    disabled
                                />
                                {{ calculate["total-" + row.staff_id] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="VuePagination-2 row col-md-12">
                <nav class="text-center">
                    <ul class="pagination VuePagination__pagination" style="">
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                            v-if="page != 1"
                        >
                            <a class="page-link" @click="changePage(1)"
                                >PRIMERO</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk disabled"
                        >
                            <a class="page-link">&lt;&lt;</a>
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-page"
                            v-if="page > 1"
                        >
                            <a class="page-link" @click="changePage(page - 1)"
                                >&lt;</a
                            >
                        </li>
                        <li
                            :class="
                                page == number
                                    ? 'VuePagination__pagination-item page-item active'
                                    : 'VuePagination__pagination-item page-item'
                            "
                            v-for="(number, index) in filteredPageValues"
                            :key="index"
                        >
                            <a
                                class="page-link active"
                                role="button"
                                @click="changePage(number)"
                                >{{ number }}</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-page"
                            v-if="page < lastPage"
                        >
                            <a class="page-link" @click="changePage(page + 1)"
                                >&gt;</a
                            >
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-chunk disabled"
                        >
                            <a class="page-link">&gt;&gt;</a>
                        </li>
                        <li
                            class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                            v-if="lastPage != page"
                        >
                            <a class="page-link" @click="changePage(lastPage)"
                                >ÚLTIMO</a
                            >
                        </li>
                    </ul>
                    <p
                        class="VuePagination__count text-center col-md-12"
                        style=""
                    ></p>
                </nav>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            dragIndex: null,
            inputValues: {},
            pageValues: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            lastPage: "",
            page: 1,
            search: "",
            perPage: 10,
            perPageValues: [
                {
                    id: 10,
                    text: "10",
                },
                {
                    id: 25,
                    text: "25",
                },
                {
                    id: 50,
                    text: "50",
                },
            ],
            tmpData: [],
        };
    },
    props: {
        /**
         * @brief Estable el formato de las celdas de la tabla
         * @param {string} name Nombre de la columna
         * @param {string} type por defecto texto y permite como otro tipo de campo input
         * @param {boolean} isDraggable establece si la columna se puede mover
         * {
         *		'name': 'code',
         *		'type': 'text',
         *		'isDraggable': false
         *	},
         */
        columns: {
            type: Array,
            required: true,
            default: function () {
                return [];
            },
        },
        data: {
            type: Array,
            required: true,
            default: function () {
                return [];
            },
        },
        value: {
            type: [String, Array, Object],
            required: false,
            default: function () {
                return [];
            },
        },
        custom_components: {
            type: [Array, Object],
            required: false,
            default: function () {
                return [];
            },
        },
        subTotal: {
            type: Boolean,
            required: false,
            default: false,
        },
        total: {
            type: Boolean,
            required: false,
            default: false,
        },
        options: {
            type: Object,
            required: false,
            default: function () {
                return {
                    draggableBy: this.columns
                        .filter(function (option) {
                            return true === (option.isDraggable ?? true);
                        })
                        .map(function (option) {
                            return option.name;
                        }),
                    inputBy: this.columns
                        .filter(function (option) {
                            return "input" === option.type;
                        })
                        .map(function (option) {
                            return option.name;
                        }),
                    subTotals: [],
                    totals: [],
                };
            },
        },
    },
    watch: {
        inputValues: function () {
            this.$emit("input", this.inputValues);
        },
        value: function (selected) {
            this.inputValues = selected;
        },
        perPage(res) {
            this.lastPage = Math.ceil(this.data.length / this.perPage);
        },
        page(res) {
            this.changePage(res);
        },
        search(res) {
            this.page = 1;
            if (res == "") {
                this.tmpData = this.data;
            } else {
                this.tmpData = this.data.filter((item) => {
                    const nombre = item.Nombre;
                    const ficha = item.Ficha;
                    const n = item["N°"].toString();

                    return (
                        nombre.includes(res) ||
                        ficha.includes(res) ||
                        n.includes(res)
                    );
                });
            }
            this.lastPage = Math.ceil(this.tmpData.length / this.perPage);
        },
        data(newData) {
            this.tmpData = newData;
            this.lastPage = Math.ceil(this.data.length / this.perPage);
        },
    },
    methods: {
        handleDragStart(event, index) {
            const vm = this;
            event.dataTransfer.setData("text/plain", index);
            vm.dragIndex = index;
        },
        handleDragDrop(event, index) {
            const vm = this;
            event.preventDefault();
            const sourceIndex = event.dataTransfer.getData("text/plain");

            if (
                sourceIndex !== index &&
                vm.columns[index]["isDraggable"] === true
            ) {
                const columnToMove = vm.columns[sourceIndex];
                vm.columns.splice(sourceIndex, 1);
                vm.columns.splice(index, 0, columnToMove);
            }

            vm.dragIndex = null;
            event.target.classList.remove("over-column");
        },
        handleDragEnter(event) {
            event.preventDefault();
            event.target.classList.add("over-column");
        },

        handleDragLeave(event) {
            event.target.classList.remove("over-column");
        },

        handleDragEnd() {
            const ths = this.$refs.draggableTable.querySelectorAll("thead th");
            ths.forEach((th) => {
                th.classList.remove("highlighted-column");
                th.classList.remove("over-column");
            });
        },

        setCustomComponent(ref, id, modal, values = null, field = null) {
            const vm = this;

            if (values && field) {
                vm.$parent.$refs[ref].record[field] = values;
            }

            vm.$parent.$refs[ref].record.id = id;

            $(`#${modal}`).modal("show");
        },

        changePage(page) {
            const vm = this;
            vm.page = page;
            var pag = 0;
            while (1) {
                if (pag + 10 >= vm.page) {
                    pag += 1;
                    break;
                } else {
                    pag += 10;
                }
            }
            vm.pageValues = [];
            for (var i = 0; i < 10; i++) {
                vm.pageValues.push(pag + i);
            }
        },
    },
    created() {},
    mounted() {
        const vm = this;
        const intervalId = setInterval(() => {
            const ths = vm.$refs.draggableTable.querySelectorAll("thead th");
            if (ths.length > 0) {
                ths.forEach((th, index) => {
                    th.draggable = vm.columns[index]["isDraggable"];
                    th.addEventListener("dragstart", (e) =>
                        vm.handleDragStart(e, index)
                    );
                    th.addEventListener("dragenter", (e) =>
                        vm.handleDragEnter(e, index)
                    );
                    th.addEventListener("dragleave", (e) =>
                        vm.handleDragLeave(e, index)
                    );
                    th.addEventListener("dragover", (e) => e.preventDefault());
                    th.addEventListener("drop", (e) =>
                        vm.handleDragDrop(e, index)
                    );
                    th.setAttribute("contenteditable", false);
                    th.onselectstart = function () {
                        return false;
                    };
                });
                clearInterval(intervalId);
            }
        }, 1000);

        vm.lastPage = Math.ceil(vm.data.length / vm.perPage);
        vm.tmpData = vm.data;
    },
    computed: {
		filteredPageValues() {
			return this.pageValues.filter(number => number <= this.lastPage);
		},
        calculate: function () {
            const vm = this;
            let groups = {};
            vm.data.forEach((row, rIndex) => {
                vm.columns.forEach((column) => {
                    if (column.group) {
                        groups[column.group + "-" + row.staff_id] =
                            (vm.inputValues[column.name + "-" + row.staff_id]
                                ? parseFloat(
                                      vm.inputValues[
                                          column.name + "-" + row.staff_id
                                      ]
                                  )
                                : 0) +
                            (groups[column.group + "-" + row.staff_id]
                                ? parseFloat(
                                      groups[column.group + "-" + row.staff_id]
                                  )
                                : 0);
                        groups["total-" + row.staff_id] =
                            (groups["total-" + row.staff_id]
                                ? parseFloat(groups["total-" + row.staff_id])
                                : 0) +
                            (vm.inputValues[column.name + "-" + row.staff_id]
                                ? parseFloat(
                                      vm.inputValues[
                                          column.name + "-" + row.staff_id
                                      ]
                                  )
                                : 0);
                    }
                });
            });
            return groups;
        },
        headers: function () {
            return this.columns.map((option) => option.name);
        },
        visibleRows() {
            const vm = this;
            let records = vm.tmpData;
            const startIndex = (vm.page - 1) * vm.perPage;
            const endIndex = startIndex + parseInt(vm.perPage);

            return records.slice(startIndex, endIndex).map((item, index) => ({
                ...item,
                index: startIndex + index + 1,
            }));
        },
    },
};
</script>
