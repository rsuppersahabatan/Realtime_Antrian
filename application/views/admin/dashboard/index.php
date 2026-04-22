<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php echo $dashboard_alert_file_install; ?>

        <!-- Info Boxes -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-maroon"><i class="fa fa-legal"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Licence</span>
                        <span class="info-box-number">Free</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">AdminLTE version</span>
                        <span class="info-box-number">2.3.1</span>
                    </div>
                </div>
            </div>

            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Users</span>
                        <span class="info-box-number"><?php echo $count_users; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-shield"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Security groups</span>
                        <span class="info-box-number"><?php echo $count_groups; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Antrian -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-pie-chart"></i>
                            Antrian per Status (<?php echo date('d M Y', strtotime($chart_tanggal)); ?>)
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartAntrianStatus" style="max-height:300px;"></canvas>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                            <?php foreach ($antrian_by_status as $status => $jml): ?>
                                <li>
                                    <a href="#">
                                        <?php echo ucfirst($status); ?>
                                        <span class="pull-right badge bg-blue"><?php echo (int) $jml; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i>
                            Antrian per Loket (<?php echo date('d M Y', strtotime($chart_tanggal)); ?>)
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartAntrianLoket" style="max-height:300px;"></canvas>
                    </div>
                    <div class="box-footer">
                        <div class="row text-center">
                            <div class="col-xs-6">
                                <span class="description-text">LOKET BUKA</span>
                                <h4 class="text-green"><?php echo (int) $loket_by_status['buka']; ?></h4>
                            </div>
                            <div class="col-xs-6">
                                <span class="description-text">LOKET TUTUP</span>
                                <h4 class="text-red"><?php echo (int) $loket_by_status['tutup']; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Resources -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">System Information</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-center"><strong>&nbsp;</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-center text-uppercase"><strong>Resources</strong></p>

                                <div class="progress-group">
                                    <span class="progress-text">Disk use space</span>
                                    <span class="progress-number">
                                        <strong><?php echo byte_format($disk_usespace, 2); ?></strong>/<?php echo byte_format($disk_totalspace, 2); ?>
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-aqua" role="progressbar"
                                             aria-valuenow="<?php echo $disk_usepercent; ?>"
                                             aria-valuemin="0" aria-valuemax="100"
                                             style="width:<?php echo $disk_usepercent; ?>%">
                                        </div>
                                    </div>
                                </div>

                                <div class="progress-group">
                                    <span class="progress-text">Memory usage</span>
                                    <span class="progress-number">
                                        <strong><?php echo byte_format($memory_usage, 2); ?></strong>/<?php echo byte_format($memory_peak_usage, 2); ?>
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-red" role="progressbar"
                                             aria-valuenow="<?php echo $memory_usepercent; ?>"
                                             aria-valuemin="0" aria-valuemax="100"
                                             style="width:<?php echo $memory_usepercent; ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$status_labels = array_keys($antrian_by_status);
$status_values = array_values($antrian_by_status);

$loket_labels = array();
$loket_totals = array();
$loket_selesai = array();
foreach ($antrian_by_loket as $lk)
{
    $label = trim($lk['nama_loket']);
    if ( ! empty($lk['kode_huruf']))
    {
        $label .= ' ('.$lk['kode_huruf'].')';
    }
    $loket_labels[]  = $label;
    $loket_totals[]  = (int) $lk['total_antrian'];
    $loket_selesai[] = (int) $lk['total_selesai'];
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') { return; }

    var statusLabels  = <?php echo json_encode(array_map('ucfirst', $status_labels)); ?>;
    var statusValues  = <?php echo json_encode($status_values); ?>;
    var loketLabels   = <?php echo json_encode($loket_labels); ?>;
    var loketTotals   = <?php echo json_encode($loket_totals); ?>;
    var loketSelesai  = <?php echo json_encode($loket_selesai); ?>;

    var elStatus = document.getElementById('chartAntrianStatus');
    if (elStatus) {
        new Chart(elStatus.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: ['#f39c12', '#3c8dbc', '#00a65a', '#dd4b39'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct   = total ? ((ctx.parsed * 100) / total).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    var elLoket = document.getElementById('chartAntrianLoket');
    if (elLoket) {
        new Chart(elLoket.getContext('2d'), {
            type: 'bar',
            data: {
                labels: loketLabels,
                datasets: [
                    {
                        label: 'Total Antrian',
                        data: loketTotals,
                        backgroundColor: 'rgba(60, 141, 188, 0.8)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Selesai',
                        data: loketSelesai,
                        backgroundColor: 'rgba(0, 166, 90, 0.8)',
                        borderColor: 'rgba(0, 166, 90, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
})();
</script>
