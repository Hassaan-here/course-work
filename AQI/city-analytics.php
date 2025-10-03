<?php require __DIR__ . '/inc/functions.inc.php';

$city = null;

if (!empty($_GET['city'])) {
    $city = $_GET['city'];
}

$fileName = null;

$cityInfo = [];

if (!empty($city)) {
    $cities = json_decode(file_get_contents(__DIR__ . '/data/index.json'), true);

    foreach ($cities as $currentCity) {
        if ($currentCity['city'] === $city) {
            $fileName = $currentCity['filename'];
            $cityInfo = $currentCity;
            break;
        }
    }
}

if (!empty($fileName)) {
    $results = json_decode(file_get_contents('compress.bzip2://' . __DIR__ . '/data/' . $fileName), true)['results'];

    $units = [
        'pm25' => null,
        'pm10' => null,
    ];

    foreach ($results as $result) {
        if (!empty($units['pm25']) && !empty($units['pm10'])) break;
        if ($result['parameter'] === 'pm25') {
            $units['pm25'] = $result['unit'];
        }
        if ($result['parameter'] === 'pm10') {
            $units['pm10'] = $result['unit'];
        }
    }

    $stats = [];

    foreach ($results as $result) {
        if ($result['parameter'] !== 'pm25' && $result['parameter'] !== 'pm10') continue;
        if ($result['value'] < 0) continue;

        $month = substr($result['date']['local'], 0, 7);

        if (!isset($stats[$month])) {
            $stats[$month] = [
                'pm25' => [],
                'pm10' => [],
            ];
        }
        $stats[$month][$result['parameter']][] = $result['value'];
    }
}

?>


<?php require __DIR__ . '/views/header.inc.php'; ?>


<?php if (empty($city)) : ?>
    <div class="error">
        <h2>City not found</h2>
        <p>Please select a valid city to view analytics.</p>
        <a href="index.php" class="button">← Back to Cities</a>
    </div>
<?php else: ?>
    <?php if (!empty($stats)): ?>
        <div class="analytics-header">
            <h1><?php echo e($cityInfo['city']) ?> <?php echo e($cityInfo['flag']) ?> - Air Quality Analytics</h1>
            <p>Monthly average PM2.5 and PM10 measurements</p>
        </div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <h3>PM2.5 & PM10 Levels Over Time</h3>
                <canvas id="combinedChart" width="400" height="200"></canvas>
            </div>

            <div class="charts-row">
                <div class="chart-wrapper">
                    <h3>PM2.5 Levels</h3>
                    <canvas id="pm25Chart" width="400" height="200"></canvas>
                </div>
                <div class="chart-wrapper">
                    <h3>PM10 Levels</h3>
                    <canvas id="pm10Chart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Prepare data for JavaScript -->
        <?php
        $chartLabels = array_keys($stats);
        $pm25Data = [];
        $pm10Data = [];

        foreach ($stats as $month => $stat) {
            $pm25Data[] = !empty($stat['pm25']) ? round(array_sum($stat['pm25']) / count($stat['pm25']), 2) : 0;
            $pm10Data[] = !empty($stat['pm10']) ? round(array_sum($stat['pm10']) / count($stat['pm10']), 2) : 0;
        }
        ?>

        <script src="srcipts/chart.umd.js"></script>
        <script>
            // Chart data
            const chartLabels = <?php echo json_encode($chartLabels); ?>;
            const pm25Data = <?php echo json_encode($pm25Data); ?>;
            const pm10Data = <?php echo json_encode($pm10Data); ?>;
            const pm25Unit = '<?php echo e($units['pm25']); ?>';
            const pm10Unit = '<?php echo e($units['pm10']); ?>';

            // Combined Chart
            const combinedCtx = document.getElementById('combinedChart').getContext('2d');
            const combinedChart = new Chart(combinedCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: `PM2.5 (${pm25Unit})`,
                        data: pm25Data,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: `PM10 (${pm10Unit})`,
                        data: pm10Data,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 2,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Average Air Quality Measurements'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Concentration (μg/m³)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });

            // PM2.5 Chart
            const pm25Ctx = document.getElementById('pm25Chart').getContext('2d');
            const pm25Chart = new Chart(pm25Ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: `PM2.5 (${pm25Unit})`,
                        data: pm25Data,
                        backgroundColor: 'rgba(231, 76, 60, 0.8)',
                        borderColor: '#e74c3c',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: `Concentration (${pm25Unit})`
                            }
                        }
                    }
                }
            });

            // PM10 Chart
            const pm10Ctx = document.getElementById('pm10Chart').getContext('2d');
            const pm10Chart = new Chart(pm10Ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: `PM10 (${pm10Unit})`,
                        data: pm10Data,
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: '#3498db',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: `Concentration (${pm10Unit})`
                            }
                        }
                    }
                }
            });
        </script>

        <!-- Data Summary Table -->
        <div class="data-summary">
            <h3>Data Summary</h3>
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Year-Month</th>
                        <th>Average PM2.5 (<?php echo e($units['pm25']) ?>)</th>
                        <th>Average PM10 (<?php echo e($units['pm10']) ?>)</th>
                        <th>Data Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $month => $stat): ?>
                        <tr>
                            <td><?php echo e($month); ?></td>
                            <td><?php echo !empty($stat['pm25']) ? e(round(array_sum($stat['pm25']) / count($stat['pm25']), 2)) : 'N/A'; ?></td>
                            <td><?php echo !empty($stat['pm10']) ? e(round(array_sum($stat['pm10']) / count($stat['pm10']), 2)) : 'N/A'; ?></td>
                            <td>PM2.5: <?php echo count($stat['pm25']); ?>, PM10: <?php echo count($stat['pm10']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>



<?php require __DIR__ . '/views/footer.inc.php'; ?>