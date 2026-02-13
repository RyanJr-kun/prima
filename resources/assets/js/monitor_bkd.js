
'use strict';

// document.addEventListener('DOMContentLoaded', function (e) {
//   let cardColor, headingColor, legendColor, labelColor, shadeColor, borderColor, fontFamily;
//   cardColor = config.colors.cardColor;
//   headingColor = config.colors.headingColor;
//   legendColor = config.colors.bodyColor;
//   labelColor = config.colors.textMuted;
//   borderColor = config.colors.borderColor;
//   fontFamily = config.fontFamily;

//   const chartOrderStatistics = document.querySelector('#bkdDetailChart'),
//     orderChartConfig = {
//       chart: {
//         height: 185,
//         width: 156,
//         type: 'donut',
//         offsetX: 15
//       },
//       labels: ['Teori ', 'Praktik ', 'Lapangan ',],
//       series: [25, 25, 5],
//       colors: [config.colors.success, config.colors.primary, config.colors.info],
//       stroke: {
//         width: 5,
//         colors: [cardColor]
//       },
//       dataLabels: {
//         enabled: false,
//         formatter: function (val, opt) {
//           return parseInt(val) + 'SKS';
//         }
//       },
//       legend: {
//         show: false
//       },
//       grid: {
//         padding: {
//           top: 0,
//           bottom: 0,
//           right: 15
//         }
//       },
//       states: {
//         hover: {
//           filter: { type: 'none' }
//         },
//         active: {
//           filter: { type: 'none' }
//         }
//       },
//       plotOptions: {
//         pie: {
//           donut: {
//             size: '75%',
//             labels: {
//               show: true,
//               value: {
//                 fontSize: '1.125rem',
//                 fontFamily: fontFamily,
//                 fontWeight: 500,
//                 color: headingColor,
//                 offsetY: -17,
//                 formatter: function (val) {
//                   return parseInt(val) + ' sks';
//                 }
//               },
//               name: {
//                 offsetY: 17,
//                 fontFamily: fontFamily
//               },
//               total: {
//                 show: true,
//                 fontSize: '13px',
//                 color: legendColor,
//                 label: 'Total SKS',
//                 formatter: function (w) {
//                   return '50';
//                 }
//               }
//             }
//           }
//         }
//       }
//     };
//   if (typeof chartOrderStatistics !== undefined && chartOrderStatistics !== null) {
//     const statisticsChart = new ApexCharts(chartOrderStatistics, orderChartConfig);
//     statisticsChart.render();
//   }

// });
