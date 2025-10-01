<?php
include './inc/functions.inc.php';
include './inc/images.inc.php';




?>
<?php include './views/header.php'; ?>


<?php if (!empty($_GET['image']) && !empty($imageTitles[$_GET['image']])): ?>
    <?php $image = $_GET['image']; ?>
    
    <div class="image-detail">
        <h2><?php echo e($imageTitles[$image]); ?></h2>
        <img src="./images/<?php echo rawurlencode($image); ?>" alt="<?php echo e($imageTitles[$image]); ?>" />
        <p><?php echo str_replace("\n", "<br />", e($imageDescriptions[$image])); ?></p>
        <a href="./index.php" class="back-button">Back to Gallery</a>
    </div>

<?php else: ?>
    <div class="notice">
        <h3>Image Not Found</h3>
        <p>The image you're looking for could not be found. Please check the URL or return to the gallery.</p>
        <a href="./index.php" class="back-button">Back to Gallery</a>
    </div>
<?php endif; ?>

<?php include './views/footer.php'; ?>