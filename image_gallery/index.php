<?php
include './inc/functions.inc.php';
include './inc/images.inc.php';
?>
<?php include './views/header.php'; ?>

<div class="gallery-container">
    <?php foreach ($imageTitles as $image => $title): ?>
        <a href="image.php?<?php echo http_build_query(['image' => $image,]) ?>" class="gallery-item">
            <div class="image-container">
                <img src="images/<?php echo rawurlencode($image); ?>" alt="<?php echo e($title); ?>">
            </div>
            <h3><?php echo e($title) ?></h3>
        </a>
    <?php endforeach; ?>
</div>

<?php include './views/footer.php'; ?>