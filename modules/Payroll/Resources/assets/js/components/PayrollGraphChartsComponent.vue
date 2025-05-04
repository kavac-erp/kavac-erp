<template>
    <canvas :id="canvaId" v-show="data.length > 0"></canvas>
</template>

<script>
    export default {
        data() {
            return {
                record: {},
                data: [],
                labels: [],
                descriptions: [],
                errors: [],
                datachart: null,
            }
        },
        props: {
            type: {
                type: String,
                required: false,
                default: '',
            },
            title: {
                type: String,
                required: false,
                default: '',
            },
            canvaId: {
                type: String,
                required: false,
                default: "chart-1"
            }

        },
        mounted() {
            const vm = this;
            this.datachart = new Chart(document.getElementById(this.canvaId), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: '',
                        data: [],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1,
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: vm.title,
                        position: 'bottom'
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            title: function (tooltipItem, data) {
                                return data.labels[tooltipItem[0].index];
                            },
                            label: function(tooltipItems, data) {
                                return `Total: ${tooltipItems.yLabel} trabajadores`;
                            },
                            footer: function (tooltipItem, data) {
                                return '';
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            display: true,
                            ticks: {
                                beginAtZero: true,
                            }
                        }]
                    }
                }
            });
        },
        methods: {
            updateChart(data, labels) {
                this.data = data;
                this.labels = labels;
                this.datachart.update();
            }
        },
        watch: {
            data: function(data) {
                this.datachart.config.data.datasets[0].data = data;
                this.datachart.update();
            },
            labels: function(labels) {
                this.datachart.config.data.labels = labels;
                this.datachart.update();
            },
            title: function(title) {
                this.datachart.config.options.title.text = title;
                this.datachart.update();
            },
            type: function(type) {
                const vm = this;
                if (this.datachart.config.type == type) {
                    this.datachart.update();
                    return;
                }
                else if (type == 'bar') {
                    this.datachart.destroy();
                    this.datachart = new Chart(this.$el, {
                        type: type,
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: '',
                                data: this.data,
                                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            legend: {
                                display: false,
                            },
                            title: {
                                display: true,
                                text: this.title,
                                position: 'bottom'
                            },
                            tooltips: {
                                enabled: true,
                                mode: 'single',
                                callbacks: {
                                    title: function (tooltipItem, data) {
                                        return data.labels[tooltipItem[0].index];
                                    },
                                    label: function(tooltipItems, data) {
                                        return "Total: " + tooltipItems.yLabel;
                                    },
                                    footer: function (tooltipItem, data) {
                                        return vm.descriptions[tooltipItem[0].index];
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    display: true,
                                    ticks: {
                                        beginAtZero: true,
                                    }
                                }]
                            }
                        }
                    });
                }
                else if ((type == 'doughnut') || (type == 'pie')) {
                    this.datachart.destroy();
                    this.datachart = new Chart(this.$el, {
                        type: type,
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: '',
                                data: this.data,
                                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            legend: {
                                display: true,
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: this.title,
                                position: 'bottom'
                            },
                            tooltips: {
                                enabled: true,
                                mode: 'single',
                                callbacks: {
                                    title: function (tooltipItem, data) {
                                        return data.labels[tooltipItem[0].index];
                                    },
                                    label: function(tooltipItems, data) {
                                        return "Total: " + tooltipItems.yLabel;
                                    },
                                    footer: function (tooltipItem, data) {
                                        return vm.descriptions[tooltipItem[0].index];
                                    }
                                }
                            }
                        }
                    });
                }
            },
        },
    };
</script>
