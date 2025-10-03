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

<!-- 
<pre>
  <?php var_dump($stats); ?>
  </pre> -->

<?php if (empty($city)) : ?>
    <div class="error">
        City not found. Please try again later.
    </div>
<?php else: ?>
    <?php if (!empty($stats)): ?>
        <h1><?php echo e($cityInfo['city']) ?> <?php echo e($cityInfo['flag']) ?></h1>
        <table>
            <thead>
                <tr>
                    <th>Year-Month</th>
                    <th>Average 2.5pm recorded</th>
                    <th>Average 10pm recorded</th>
                </tr>
            </thead>
            <?php foreach ($stats as $month => $stat): ?>
                <tbody>
                    <td><?php echo e($month); ?></td>
                    <td><?php echo e(round(array_sum($stat['pm25']) / count($stat['pm25']), 2)); ?> <?php echo e($units['pm25']) ?></td>
                    <td><?php echo e(round(array_sum($stat['pm10']) / count($stat['pm10']), 2)); ?> <?php echo e($units['pm25']) ?>
                    </td>
                </tbody>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>



<?php require __DIR__ . '/views/footer.inc.php'; ?>