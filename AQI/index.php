<?php
require __DIR__ . '/inc/functions.inc.php';
?>


<?php require __DIR__ . '/views/header.inc.php'; ?>

<?php $cities = json_decode(file_get_contents(__DIR__ . '/data/index.json'), true); ?>

<!-- <pre> -->

<ul>
    <?php foreach ($cities as $city): ?>
        <li>
            <a style="color: white; text-decoration: none;" href="city.php?<?php echo http_build_query(['city' => $city['city']]); ?>">
                <?php echo e($city['city']); ?> ,
                <?php echo e($city['country']); ?>,
                (<?php echo e($city['flag']); ?>)
            </a>
            <span style="margin-left: 16px;">
                <a href="city-analytics.php?<?php echo http_build_query(['city' => $city['city']]); ?>"><span>See Analytics</span></a>
            </span>
        </li>
    <?php endforeach; ?>
</ul>

<!-- </pre> -->

<?php require __DIR__ . '/views/footer.inc.php'; ?>