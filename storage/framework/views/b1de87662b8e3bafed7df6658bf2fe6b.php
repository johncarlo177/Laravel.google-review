<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination Navigation" style="margin-top: 20px;">
        <ul style="display: flex; list-style: none; padding: 0; gap: 10px; justify-content: center; align-items: center;">
            
            <?php if($paginator->onFirstPage()): ?>
                <li style="padding: 8px 12px; color: #999; cursor: not-allowed;">
                    <span>« Previous</span>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?php echo e($paginator->previousPageUrl()); ?>" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                        « Previous
                    </a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_string($element)): ?>
                    <li style="padding: 8px 12px; color: #666;">
                        <span><?php echo e($element); ?></span>
                    </li>
                <?php endif; ?>

                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li style="padding: 8px 12px; background: #4285f4; color: white; border-radius: 4px;">
                                <span><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="<?php echo e($url); ?>" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                                    <?php echo e($page); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li>
                    <a href="<?php echo e($paginator->nextPageUrl()); ?>" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                        Next »
                    </a>
                </li>
            <?php else: ?>
                <li style="padding: 8px 12px; color: #999; cursor: not-allowed;">
                    <span>Next »</span>
                </li>
            <?php endif; ?>
        </ul>

        <div style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
            Showing <?php echo e($paginator->firstItem()); ?> to <?php echo e($paginator->lastItem()); ?> of <?php echo e($paginator->total()); ?> results
        </div>
    </nav>
<?php endif; ?>

<?php /**PATH /home/johncarlo/Documents/Projects/FREE-TESK/review/review/resources/views/pagination/default.blade.php ENDPATH**/ ?>