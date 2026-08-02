<?php
/**
 * Repository Analytics
 * Version : 1.0.0
 * Author  : Murad Maulana
 */

defined('INDEX_AUTH') or die('Direct access not allowed!');

/**
 * Execute COUNT(*) query and return integer result.
 *
 * @param mysqli $dbs Database connection
 * @param string $sql SQL COUNT query (must use: AS total)
 * @return int Total result, or 0 if query fails.
 */
function getTotal(mysqli $dbs, string $sql): int
{
    $result = $dbs->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int)$row['total'];
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'summary') {

    global $dbs;

    header('Content-Type: application/json');

    $data = [];

    $data['total_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read"
);

    $data['today_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read
     WHERE DATE(date_read)=CURDATE()"
);

    $data['month_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read
     WHERE YEAR(date_read)=YEAR(CURDATE())
       AND MONTH(date_read)=MONTH(CURDATE())"
);

    $data['year_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read
     WHERE YEAR(date_read)=YEAR(CURDATE())"
);

    $data['total_collection'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM biblio"
);

    $data['total_attachment'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files"
);

    $data['member_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read
     WHERE member_id IS NOT NULL
       AND member_id <> ''"
);

    $data['guest_download'] = getTotal(
    $dbs,
    "SELECT COUNT(*) AS total
     FROM files_read
     WHERE member_id IS NULL
        OR member_id = ''"
);

    echo json_encode($data);

    exit;
}

if ($action === 'top_download') {

    global $dbs;

    header('Content-Type: application/json');

    $sql = "
        SELECT
            f.file_title,
            COUNT(fr.file_id) AS total_download
        FROM files_read fr
        INNER JOIN files f
            ON fr.file_id = f.file_id
        GROUP BY
            fr.file_id,
            f.file_title
        ORDER BY total_download DESC
        LIMIT 10
    ";

    $result = $dbs->query($sql);

    $rows = [];

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $rows[] = $row;

        }

    }

    echo json_encode($rows);

    exit;
}
/* =========================================================
   MONTHLY CHART
========================================================= */
if ($action === 'monthly_chart') {

    global $dbs;

    header('Content-Type: application/json');

    $sql = "
        SELECT
            MONTH(date_read) AS month,
            COUNT(*) AS total
        FROM files_read
        WHERE YEAR(date_read)=YEAR(CURDATE())
        GROUP BY MONTH(date_read)
        ORDER BY MONTH(date_read)
    ";

    $result = $dbs->query($sql);

    $rows = [];

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $rows[] = [
                'month' => (int)$row['month'],
                'total' => (int)$row['total']
            ];

        }

    }

    echo json_encode($rows);

    exit;
}
?>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet"
      href="<?= SWB ?>plugins/repository_analytics/assets/repository.css">
	  
<?php

/**
 * Plugin Configuration
 */
$plugin_hash = md5(realpath(__FILE__));

$ajax_url = AWB .
    'plugin_container.php?mod=reporting&id=' .
    $plugin_hash;

?>

<div
    id="repository-analytics"
    class="ra-wrapper"
    data-ajax-url="<?= $ajax_url ?>">

<div class="per_title">
<h2>Repository Analytics</h2>
</div>

<div class="infoBox">
Dashboard Statistik Repository Digital SLiMS
</div>

<div class="ra-grid">

<?php

$cards = [

['id'=>'total_download','title'=>'Total Download','icon'=>'fa-download'],

['id'=>'today_download','title'=>'Hari Ini','icon'=>'fa-calendar-day'],

['id'=>'month_download','title'=>'Bulan Ini','icon'=>'fa-calendar'],

['id'=>'year_download','title'=>'Tahun Ini','icon'=>'fa-calendar-check'],

['id'=>'total_collection','title'=>'Total Koleksi','icon'=>'fa-book'],

['id'=>'total_attachment','title'=>'Attachment','icon'=>'fa-paperclip'],

['id'=>'member_download','title'=>'Member','icon'=>'fa-user'],

['id'=>'guest_download','title'=>'Guest','icon'=>'fa-users']

];

foreach($cards as $card){
?>

<div class="ra-card">

    <i class="fa-solid <?= $card['icon']; ?>"></i>

    <div class="ra-card-title">
        <?= $card['title']; ?>
    </div>

    <div class="ra-card-value" id="<?= $card['id']; ?>">
        ...
    </div>

</div>

<?php } ?>

</div> <!-- END ra-grid -->


<!-- =========================================
     TOP 10 DOWNLOAD
========================================= -->

<div class="ra-section">

    <div class="ra-section-header">

        <h3>
            <i class="fa-solid fa-ranking-star"></i>
            Top 10 Download
        </h3>

    </div>

    <table class="ra-table">

        <thead>

            <tr>

                <th width="70">Rank</th>

                <th>Judul</th>

                <th width="120">Download</th>

            </tr>

        </thead>

        <tbody id="top-download-body">

            <tr>

                <td colspan="3" style="text-align:center;padding:30px;">

                    Belum ada data

                </td>

            </tr>

        </tbody>

    </table>

</div> <!-- END ra-section -->

<!-- =========================================
     MONTHLY DOWNLOAD CHART
========================================= -->

<div class="ra-section">

    <div class="ra-section-header">

        <h3>
            <i class="fa-solid fa-chart-column"></i>
            Download Bulanan
        </h3>

    </div>

    <div class="ra-chart-container">

        <canvas id="monthlyChart"></canvas>

    </div>

</div>
</div> <!-- END repository-analytics -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= SWB ?>plugins/repository_analytics/assets/repository.js"></script>