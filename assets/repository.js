$(function () {

    const ajaxUrl = $('#repository-analytics').data('ajax-url');

    // ==========================
    // SUMMARY DASHBOARD
    // ==========================

    $.getJSON(
        ajaxUrl + "&action=summary",
        function (d) {

            $("#total_download").text(d.total_download);
            $("#today_download").text(d.today_download);
            $("#month_download").text(d.month_download);
            $("#year_download").text(d.year_download);
            $("#total_collection").text(d.total_collection);
            $("#total_attachment").text(d.total_attachment);
            $("#member_download").text(d.member_download);
            $("#guest_download").text(d.guest_download);

        }
    );

    // ==========================
    // TOP DOWNLOAD
    // ==========================

    $.getJSON(
        ajaxUrl + "&action=top_download",
        function (d) {

            let html = '';

            d.forEach(function (item, index) {

                let rank = index + 1;

                if (rank === 1) {
                    rank = "🥇";
                } else if (rank === 2) {
                    rank = "🥈";
                } else if (rank === 3) {
                    rank = "🥉";
                }

                let rowClass = '';

                if (index === 0) {
                    rowClass = 'rank-gold';
                } else if (index === 1) {
                    rowClass = 'rank-silver';
                } else if (index === 2) {
                    rowClass = 'rank-bronze';
                }

                html += `
                    <tr class="${rowClass}">
                        <td class="ra-rank">${rank}</td>

                        <td>
                            <div
                                class="ra-title"
                                title="${item.file_title}">
                                ${item.file_title}
                            </div>
                        </td>

                        <td class="ra-download">
                            <span class="ra-download-badge">
                                ${item.total_download}
                            </span>
                        </td>

                    </tr>
                `;

            });

            $("#top-download-body").html(html);

        }
    );

    // ==========================================
// MONTHLY CHART (DATABASE)
// ==========================================

const chartCanvas = document.getElementById('monthlyChart');

if (chartCanvas) {

    $.getJSON(
        ajaxUrl + "&action=monthly_chart",
        function(rows){

            const labels = [
                'Jan','Feb','Mar','Apr','Mei','Jun',
                'Jul','Agu','Sep','Okt','Nov','Des'
            ];

            const totals = new Array(12).fill(0);

            rows.forEach(function(item){

                totals[item.month - 1] = item.total;

            });

            new Chart(chartCanvas, {

                type: 'bar',

                data: {

                    labels: labels,

                    datasets: [{

                        label: 'Download',

                        data: totals,

                        backgroundColor: '#1976d2',

                        borderRadius: 6

                    }]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            display: false

                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                precision: 0

                            }

                        }

                    }

                }

            });

        }
    );

}


});