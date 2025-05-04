<template>
    <div class="custom-multiselect" style="display: grid">
        <div
            class="btn-group"
            style="
                background-color: white;
                color: darkgray;
                white-space: nowrap;
            "
        >
            <button
                id="custom-multiselect_button"
                type="button"
                class="btn btn-secondary dropdown-toggle text-left"
                data-toggle="dropdown"
                data-display="static"
                aria-expanded="false"
                style="background-color: white; color: darkgray"
            >
                <div
                    class="multiselect__tags"
                    style="display: flex; flex-wrap: wrap"
                >
                    <div
                        class="multiselect__tags-wrap"
                        style="inline-grid"
                        v-for="(selection, index) in selections"
                        :key="index"
                    >
                        <span
                            class="multiselect__tag"
                            :style="styleMultiselectTag"
                        >
                            {{ selection[track_by] }}
                            <span
                                class="badge badge-light"
                                v-if="selection.count > 1"
                            >
                                {{ selection.count }}
                            </span>
                            <i
                                tabindex="1"
                                class="multiselect__tag-icon"
                                @click="removeSelection(index, $event)"
                            ></i>
                        </span>
                    </div>
                    <input
                        ref="search"
                        name=""
                        type="text"
                        :placeholder="placeholder"
                        :value="search"
                        tabindex="0"
                        class="multiselect__input"
                        @input="updateSearch($event.target.value)"
                        @click="toggleDropdown($event)"
                    />
                </div>
                <i tabindex="1" class="custom-multiselect-icon"></i>
            </button>
            <div
                class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left"
                :style="styleDropDown"
            >
                <button
                    class="dropdown-item"
                    type="button"
                    @click="addSelection(option, $event)"
                    v-for="option in filteredOptions"
                    :key="option.id"
                >
                    <slot name="customOptionLabel" v-bind:option="option">
                        {{ option[track_by] }}
                    </slot>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            search: "",
            selections: [],
            styleMultiselectTag:
                "white-space: nowrap; overflow: hidden; text-overflow: ellipsis;",
            styleDropDown:
                "max-height: 15rem; font-size: 16px; overflow-y: auto; overflow-x: auto; max-width: 100%;",
        };
    },
    props: {
        id: {
            type: String,
            required: false,
            default: "custom-multiselect",
        },
        placeholder: {
            type: String,
            required: false,
            default: "Seleccione...",
        },
        track_by: {
            type: String,
            required: false,
            default: "text",
        },
        options: {
            type: [Array, Object],
            required: true,
            default: () => [],
        },
        optionsLimit: {
            type: Number,
            required: false,
            default: Infinity,
        },
        value: {
            type: [String, Array, Object],
            required: false,
            default: function () {
                return [];
            },
        },
        limitCount: {
            type: Number,
            required: false,
            default: 1,
        },
    },
    methods: {
        addSelection(option, event) {
            const existingSelection = this.selections.find(
                (sel) => sel.id === option.id
            );
            if (existingSelection) {
                if (option.max && option.max > existingSelection.count) {
                    existingSelection.count++;
                } else if (this.limitCount > existingSelection.count) {
                    existingSelection.count++;
                }
            } else {
                this.selections.push({ ...option, count: 1 });
            }
            event.stopPropagation();
        },
        removeSelection(index, event) {
            if (this.selections[index].count > 1) {
                this.selections[index].count--;
            } else {
                this.selections.splice(index, 1);
            }
            if (this.selections.length > 0) {
                event.stopPropagation();
            }
        },
        toggleDropdown(event) {
            event.stopPropagation();
        },
        updateSearch(query) {
            this.search = query;
        },
        includes(str, query) {
            if (str === undefined) str = "undefined";
            if (str === null) str = "null";
            if (str === false) str = "false";
            const text = str.toString().toLowerCase();
            return text.indexOf(query.trim()) !== -1;
        },
        filterOptions(options, search, value) {
            return options.filter((option) =>
                this.includes(option[value], search)
            );
        },
    },
    mounted() {
        const vm = this;

        const tags = document.querySelectorAll(".multiselect__tag");
        tags.forEach(function (tag) {
            tag.addEventListener("click", function (event) {
                event.stopPropagation();
            });
        });

        const elementos = document.querySelectorAll(
            ".custom-multiselect .dropdown-menu"
        );
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                const isActive = mutation.target.classList.contains("show");
                const dropdownInput =
                    mutation.target.previousElementSibling.lastElementChild
                        .previousElementSibling.lastElementChild;
                if (isActive) {
                    dropdownInput.style.width = "100%";
                    dropdownInput.style.position = "relative";
                    dropdownInput.style.padding = "";
                } else if (vm.selections.length > 0) {
                    dropdownInput.style.width = "0";
                    dropdownInput.style.position = "absolute";
                    dropdownInput.style.padding = "0";
                }
            });
        });

        const opciones = {
            attributes: true,
            attributeFilter: ["class"],
        };
        elementos.forEach(function (elemento) {
            observer.observe(elemento, opciones);
        });
        vm.selections = this.value;
        vm.$nextTick(() => {
            if (vm.selections.length > 0) {
                vm.$refs.search.style.width = "0";
                vm.$refs.search.style.position = "absolute";
                vm.$refs.search.style.padding = "0";
            }
        });
    },
    watch: {
        selections: function () {
            this.$emit("input", this.selections);
        },
        value: function (selected) {
            this.selections = selected;
        },
    },
    computed: {
        filteredOptions() {
            const search = this.search || "";
            const normalizedSearch = search.toLowerCase().trim();

            let options = this.options.concat();

            options = this.filterOptions(
                options,
                normalizedSearch,
                this.track_by
            );

            return options.slice(0, this.optionsLimit);
        },
    },
};
</script>
