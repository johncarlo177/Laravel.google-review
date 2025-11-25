<?php
    $enabled = $composer->designValue('desktop_customizations') == 'enabled';

    $left_url = file_url($composer->notEmptyDesignValue('desktop_left_image', ''));
    $right_url = file_url($composer->notEmptyDesignValue('desktop_right_image'));

    $left_color = $composer->notEmptyDesignValue('desktop_left_color');
    $right_color = $composer->notEmptyDesignValue('desktop_right_color');

    $should_render = $enabled && !empty(array_filter([$left_url, $right_url, $left_color, $right_color]));
?>

<?php if($should_render): ?>

    <?php if($left_url): ?>
        <img src="<?php echo e($left_url); ?>" class="preload-image" />
    <?php endif; ?>

    <?php if($right_url): ?>
        <img src="<?php echo e($right_url); ?>" class="preload-image" />
    <?php endif; ?>

    <style>
        .desktop-placeholder {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            height: 100%;
            width: 100%;
            pointer-events: none;
            user-select: none;
            display: flex;
            z-index: -1;
        }

        .desktop-placeholder .middle {
            max-width: 500px;
            width: 100%;
        }

        .desktop-placeholder .desktop-image {
            flex: 1;
            background-size: cover;
            background-position: center;
        }

        .desktop-placeholder .desktop-image.left {
            background-color: <?php echo e($left_color); ?>;

            background-image: url(<?php echo e($left_url); ?>);
        }

        .desktop-placeholder .desktop-image.right {
            background-color: <?php echo e($right_color); ?>;

            background-image: url(<?php echo e($right_url); ?>);
        }

        @media (max-width: 500px) {
            .desktop-placeholder {
                display: none;
            }
        }
    </style>

    <div class="desktop-placeholder">
        <div class="desktop-image left">
        </div>
        <div class="middle">
        </div>
        <div class="desktop-image right">
        </div>
    </div>

<?php endif; ?>
<?php /**PATH /home/johncarlo/Documents/Projects/FREELANCER-TASK/review/review/resources/views/qrcode/components/desktop-background.blade.php ENDPATH**/ ?>