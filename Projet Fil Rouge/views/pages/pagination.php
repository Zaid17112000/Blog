<div class="clean-pagination">
    <?php if ($current_page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="page-arrow"><i class="ion-chevron-left"></i></a>
    <?php else: ?>
        <span class="page-arrow disabled"><i class="ion-chevron-left"></i></span>
    <?php endif; ?>
    
    <!-- Always show first page -->
    <?php if ($current_page > 4): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="page-number">1</a>
    <?php endif;
    
    // Show ellipsis if needed
    if ($current_page > 5): ?>
        <span class="page-ellipsis">...</span>
    <?php endif;
    
    // Show pages around current page (3 before, 3 after)
    $start = max(1, $current_page - 3);
    $end = min($total_pages, $current_page + 3);

    for ($i = $start; $i <= $end; $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-number <?= ($i == $current_page) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor;
    
    // Show ellipsis if needed
    if ($current_page < $total_pages - 4): ?>
        <span class="page-ellipsis">...</span>
    <?php endif;

    // Always show last page if not already shown
    if ($current_page < $total_pages - 3): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" class="page-number"><?= $total_pages ?></a>
    <?php endif; ?>
    
    <?php if ($current_page < $total_pages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="page-arrow"><i class="ion-chevron-right"></i></a>
    <?php else: ?>
        <span class="page-arrow disabled"><i class="ion-chevron-right"></i></span>
    <?php endif; ?>
</div>