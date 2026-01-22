/**
 * Theme: PROP PIK - Virtual Web Reality Admin Dashboard
 * Author: CreArt Solutions Pvt Ltd
 * Module/App: Dashboard
 */

import ApexCharts from "apexcharts/dist/apexcharts";
window.ApexCharts = ApexCharts
import jsVectorMap from 'jsvectormap'
import 'jsvectormap/dist/maps/world-merc.js'
import 'jsvectormap/dist/maps/world.js'


// top section details booking, tours, customer, revenue  ->  CARDS;
const totalPropertyDiv = document.getElementById('total_property_card');
const liveTrousCardDiv = document.getElementById('live_tours_card');
const totalcustomerCardDiv = document.getElementById('total_customer_card');
const totalRevenueCardDiv = document.getElementById('total_revenue_card');

// sales_analytic and Revenue Chart section;
const salesAnalyticDiv = document.getElementById('sales_analytic');

const TourLiveCardDiv = document.getElementById('tour_live_card');

var totalPropertyCardOptions = {
    chart: {
        height: 95,
        parentHeightOffset: 0,
        type: "bar",
        toolbar: {
            show: !1
        },
    },
    plotOptions: {
        bar: {
            barHeight: "100%",
            columnWidth: "40%",
            startingShape: "rounded",
            endingShape: "rounded",
            borderRadius: 4,
            distributed: !0,
        },
    },
    grid: {
        show: !1,
        padding: {
            top: -20,
            bottom: -10,
            left: 0,
            right: 0
        },
    },
    colors: ["#eef2f7", "#eef2f7", "#604ae3", "#eef2f7"],
    dataLabels: {
        enabled: !1
    },
    series: [{
        name: 'Property Listing',
        data: window.weeklyProperties || [0, 0, 0, 0, 0, 0, 0]
    }],
    legend: {
        show: !1
    },
    xaxis: {
        categories: window.weeklyLabels || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
    },
    yaxis: {
        labels: {
            show: !1
        }
    },
    tooltip: {
        enabled: !0
    },
    responsive: [{
        breakpoint: 1025,
        options: {
            chart: {
                height: 199
            }
        }
    }],
};
var totalPropertyCard = new ApexCharts(totalPropertyDiv, totalPropertyCardOptions);
totalPropertyCard.render();

var liveToursCardOptions = {
    chart: {
        height: 95,
        parentHeightOffset: 0,
        type: "bar",
        toolbar: {
            show: !1
        },
    },
    plotOptions: {
        bar: {
            barHeight: "100%",
            columnWidth: "40%",
            startingShape: "rounded",
            endingShape: "rounded",
            borderRadius: 4,
            distributed: !0,
        },
    },
    grid: {
        show: !1,
        padding: {
            top: -20,
            bottom: -10,
            left: 0,
            right: 0
        },
    },
    colors: ["#eef2f7", "#eef2f7", "#604ae3", "#eef2f7"],
    dataLabels: {
        enabled: !1
    },
    series: [{
        name: 'Live Tours',
        data: window.weeklyTours || [0, 0, 0, 0, 0, 0, 0]
    }],
    legend: {
        show: !1
    },
    xaxis: {
        categories: window.weeklyLabels || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
    },
    yaxis: {
        labels: {
            show: !1
        }
    },
    tooltip: {
        enabled: !0
    },
    responsive: [{
        breakpoint: 1025,
        options: {
            chart: {
                height: 199
            }
        }
    }],
};
var liveToursCard = new ApexCharts(liveTrousCardDiv, liveToursCardOptions);
liveToursCard.render();

var totalCustomerCardOptions = {
    chart: {
        height: 95,
        parentHeightOffset: 0,
        type: "bar",
        toolbar: {
            show: !1
        },
    },
    plotOptions: {
        bar: {
            barHeight: "100%",
            columnWidth: "40%",
            startingShape: "rounded",
            endingShape: "rounded",
            borderRadius: 4,
            distributed: !0,
        },
    },
    grid: {
        show: !1,
        padding: {
            top: -20,
            bottom: -10,
            left: 0,
            right: 0
        },
    },
    colors: ["#eef2f7", "#eef2f7", "#604ae3", "#eef2f7"],
    dataLabels: {
        enabled: !1
    },
    series: [{
        name: 'Customers',
        data: window.weeklyCustomers || [0, 0, 0, 0, 0, 0, 0]
    }],
    legend: {
        show: !1
    },
    xaxis: {
        categories: window.weeklyLabels || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
    },
    yaxis: {
        labels: {
            show: !1
        }
    },
    tooltip: {
        enabled: !0
    },
    responsive: [{
        breakpoint: 1025,
        options: {
            chart: {
                height: 199
            }
        }
    }],
};
var totalCustomerCard = new ApexCharts(totalcustomerCardDiv, totalCustomerCardOptions);
totalCustomerCard.render();

var totalRevenueCardOptions = {
    chart: {
        height: 95,
        parentHeightOffset: 0,
        type: "bar",
        toolbar: {
            show: !1
        },
    },
    plotOptions: {
        bar: {
            barHeight: "100%",
            columnWidth: "40%",
            startingShape: "rounded",
            endingShape: "rounded",
            borderRadius: 4,
            distributed: !0,
        },
    },
    grid: {
        show: !1,
        padding: {
            top: -20,
            bottom: -10,
            left: 0,
            right: 0
        },
    },
    colors: ["#eef2f7", "#eef2f7", "#604ae3", "#eef2f7"],
    dataLabels: {
        enabled: !1
    },
    series: [{
        name: 'Revenue',
        data: window.weeklyRevenue || [0, 0, 0, 0, 0, 0, 0]
    }],
    legend: {
        show: !1
    },
    xaxis: {
        categories: window.weeklyLabels || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
    },
    yaxis: {
        labels: {
            show: !1
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return "₹" + (val / 100).toFixed(2);
            }
        }
    },
    responsive: [{
        breakpoint: 1025,
        options: {
            chart: {
                height: 199
            }
        }
    }],
};
var totalRevenueCard = new ApexCharts(totalRevenueCardDiv, totalRevenueCardOptions);
totalRevenueCard.render();

// sales_analytic

// Fetch and render sales analytic chart data from API
function fetchAndRenderBookingAnalytic(type = 'week') {
    const apiUrl = salesAnalyticDiv.dataset.apiUrl + '?type=' + type;
    fetch(apiUrl)
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.date) return;
            const data = res.date;
            const salesChartOptions = {
                chart: {
                    height: 420,
                    type: "area",
                    dropShadow: {
                        enabled: true,
                        opacity: 0.2,
                        blur: 10,
                        left: -7,
                        top: 22,
                    },
                    toolbar: {
                        show: false,
                    },
                },
                colors: ["#604ae3", "#47ad94"],
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    show: true,
                    curve: "smooth",
                    width: 2,
                    lineCap: "square",
                },
                series: data.series,
                labels: data.categories,
                xaxis: {
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                    crosshairs: {
                        show: true,
                    },
                    labels: {
                        offsetX: 0,
                        offsetY: 5,
                        style: {
                            fontSize: "12px",
                            cssClass: "apexcharts-xaxis-title",
                        },
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function (value, index) {
                            return value >= 1000 ? (value / 1000).toFixed(1) + "K" : value.toFixed(0);
                        },
                        offsetX: -15,
                        offsetY: 0,
                        style: {
                            fontSize: "12px",
                            cssClass: "apexcharts-yaxis-title",
                        },
                    },
                },
                grid: {
                    borderColor: "#191e3a",
                    strokeDashArray: 5,
                    xaxis: {
                        lines: {
                            show: true,
                        },
                    },
                    yaxis: {
                        lines: {
                            show: false,
                        },
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 5,
                    },
                },
                legend: {
                    show: false,
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        type: "vertical",
                        shadeIntensity: 1,
                        inverseColors: !1,
                        opacityFrom: 0.12,
                        opacityTo: 0.1,
                        stops: [100, 100],
                    },
                },
                responsive: [{
                    breakpoint: 575,
                    options: {
                        legend: {
                            offsetY: -50,
                        },
                    },
                }],
            };
            if (window.salesAnalyticesChat) {
                window.salesAnalyticesChat.updateOptions(salesChartOptions);
            } else {
                window.salesAnalyticesChat = new ApexCharts(salesAnalyticDiv, salesChartOptions);
                window.salesAnalyticesChat.render();
            }
        });
}
// Initial load
fetchAndRenderBookingAnalytic('week');

// Fetch and render sales analytic chart data from API
function fetchAndRenderSalesAnalytic(type = 'week') {
    const salesChartDiv = document.getElementById('sales_chart');
    const apiUrl = salesChartDiv.dataset.apiUrl + '?type=' + type;
    fetch(apiUrl)
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.date) return;
            const data = res.date;
            // Update footer with totals
            let totalSales = 0;
            let totalBookings = 0;
            if (data.series && data.series[0] && data.series[0].data) {
                totalSales = data.series[0].data.reduce((a, b) => Number(a) + Number(b), 0);
                totalBookings = data.series[0].data.length > 0 ? data.series[0].data.filter(x => x > 0).length : 0;
            }
            document.getElementById('total_sales_amount').textContent = '₹' + (totalSales).toFixed(2);
            document.getElementById('total_bookings_count').textContent = totalBookings;

            const salesChartOptions = {
                chart: {
                    height: 420,
                    type: "area",
                    dropShadow: {
                        enabled: true,
                        opacity: 0.2,
                        blur: 10,
                        left: -7,
                        top: 22,
                    },
                    toolbar: {
                        show: false,
                    },
                },
                colors: ["#47ad94","#604ae3"],
                dataLabels: {   
                    enabled: false,
                },
                stroke: {
                    show: true,
                    curve: "smooth",
                    width: [2, 2],
                    lineCap: "round",
                    lineJoin: "round",
                    dashArray: [0, 5],
                },
                series: data.series,
                labels: data.categories,
                xaxis: {
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                    crosshairs: {
                        show: true,
                        width: 1,
                        position: 'front',
                        stroke: {
                            color: '#b6c1d5',
                            width: 1,
                        }
                    },
                    labels: {
                        offsetX: 0,
                        offsetY: 5,
                        style: {
                            fontSize: "12px",
                            cssClass: "apexcharts-xaxis-title",
                        },
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function (value, index) {
                            return "₹" + (value >= 100000 ? (value / 100000).toFixed(0) + "L" : (value / 1000).toFixed(1) + "K");
                        },
                        offsetX: -15,
                        offsetY: 0,
                        style: {
                            fontSize: "12px",
                            cssClass: "apexcharts-yaxis-title",
                        },
                    },
                },
                grid: {
                    borderColor: "#e3e3e3",
                    strokeDashArray: 3,
                    xaxis: {
                        lines: {
                            show: true,
                        },
                    },
                    yaxis: {
                        lines: {
                            show: true,
                        },
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 5,
                    },
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        type: "vertical",
                        shadeIntensity: 0.3,
                        inverseColors: false,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [20, 100],
                    },
                },
                tooltip: {
                    theme: "light",
                    y: {
                        formatter: function (value) {
                            return "₹" + (value).toFixed(0);
                        }
                    }
                },
                responsive: [{
                    breakpoint: 575,
                    options: {
                        legend: {
                            offsetY: -50,
                        },
                    },
                }],
            };
            if (window.salesChart) {
                window.salesChart.updateOptions(salesChartOptions);
                window.salesChart.updateSeries(data.series);
            } else {
                window.salesChart = new ApexCharts(salesChartDiv, salesChartOptions);
                window.salesChart.render();
            }
        });
}
// Initial load for sales chart
fetchAndRenderSalesAnalytic('week');

// sales_funnel
var TourLiveCardOptions = {
    chart: {
        height: 120,
        parentHeightOffset: 0,
        type: "bar",
        toolbar: {
            show: !1
        },
    },
    plotOptions: {
        bar: {
            barHeight: "100%",
            columnWidth: "40%",
            startingShape: "rounded",
            endingShape: "rounded",
            borderRadius: 4,
            distributed: !0,
        },
    },
    grid: {
        show: true,
        padding: {
            top: -20,
            bottom: -10,
            left: 0,
            right: 0
        },
    },
    colors: ["#604ae3", "#604ae3", "#604ae3", "#604ae3"],
    dataLabels: {
        enabled: !1
    },
    series: [{
        name: 'Property Sales',
        data: [40, 50, 65, 45, 40, 70, 40]
    }],
    legend: {
        show: !1
    },
    xaxis: {
        categories: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
    },
    yaxis: {
        labels: {
            show: true
        }
    },
    tooltip: {
        enabled: !0
    },
    responsive: [{
        breakpoint: 1025,
        options: {
            chart: {
                height: 199
            }
        }
    }],
};

var TourLiveCard = new ApexCharts(TourLiveCardDiv, TourLiveCardOptions);
TourLiveCard.render();

//
// GRADIENT CIRCULAR CHART
//
var colors = ["#7f56da", "#4697ce"];
var options = {
    chart: {
        height: 349,
        type: "radialBar",
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 225,
            hollow: {
                margin: 0,
                size: "70%",
                background: "transparent",
                image: undefined,
                imageOffsetX: 0,
                imageOffsetY: 0,
                position: "front",
                dropShadow: {
                    enabled: true,
                    top: 3,
                    left: 0,
                    blur: 4,
                    opacity: 0.24,
                },
            },
            track: {
                background: "rgba(170,184,197, 0.4)",
                strokeWidth: "67%",
                margin: 0,
            },

            dataLabels: {
                showOn: "always",
                name: {
                    offsetY: -10,
                    show: true,
                    color: "#888",
                    fontSize: "17px",
                },
                value: {
                    formatter: function (val) {
                        return parseInt(val);
                    },
                    color: "#111",
                    fontSize: "36px",
                    show: true,
                },
            },
        },
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: "dark",
            type: "horizontal",
            shadeIntensity: 0.5,
            gradientToColors: colors,
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100],
        },
    },
    series: [70],
    stroke: {
        lineCap: "round",
    },
    labels: ["Total Buyer"],
};
var chart = new ApexCharts(document.querySelector("#own-property"), options);
chart.render();



// most-sales-location

class VectorMap {
    initWorldMapMarker() {
        const map = new jsVectorMap({
            map: "world",
            selector: "#most-sales-location",
            zoomOnScroll: true,
            zoomButtons: false,
            markersSelectable: true,
            markers: [{
                name: "Canada",
                coords: [56.1304, -106.3468]
            },
            {
                name: "Brazil",
                coords: [-14.235, -51.9253]
            },
            {
                name: "Russia",
                coords: [61, 105]
            },
            {
                name: "China",
                coords: [35.8617, 104.1954]
            },
            {
                name: "United States",
                coords: [37.0902, -95.7129]
            },
            {
                name: "Ahmedabad",
                coords: [23.03, 72.58]
            },
            ],
            markerStyle: {
                initial: {
                    fill: "#7f56da"
                },
                selected: {
                    fill: "#1bb394"
                },
            },
            labels: {
                markers: {
                    render: (marker) => marker.name,
                },
            },
            regionStyle: {
                initial: {
                    fill: "rgba(169,183,197, 0.3)",
                    fillOpacity: 1,
                },
            },
        });
    }

    init() {
        this.initWorldMapMarker();
    }
}

document.addEventListener("DOMContentLoaded", function (e) {
    new VectorMap().init();
});

// sales_analytic
const bookingAnaliticDropdown = document.getElementById('booking-analytic-dropdown');
bookingAnaliticDropdown.addEventListener('click', function (event) {
    const target = event.target;
    if (target.classList.contains('dropdown-item')) {
        event.preventDefault();
        let type = target.dataset.type || 'week';
        fetchAndRenderBookingAnalytic(type);
    }
})

// sales chart dropdown
const salesAnaliticDropdown = document.getElementById('sales-analytic-dropdown');
if (salesAnaliticDropdown) {
    salesAnaliticDropdown.addEventListener('click', function (event) {
        const target = event.target;
        if (target.classList.contains('dropdown-item')) {
            event.preventDefault();
            let type = target.dataset.type || 'week';
            fetchAndRenderSalesAnalytic(type);
        }
    })
}