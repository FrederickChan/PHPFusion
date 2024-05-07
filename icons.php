<?php
require_once __DIR__ . '/maincore.php';
require_once FUSION_HEADER;

$image_repo = new \PHPFusion\ImageRepo();
$image_repo->cacheImages();
$list = $image_repo::getImageList();

\PHPFusion\Panels::getInstance()->hideAll();

echo '<div class="row">';
foreach ($list as $key => $val) {
    echo '<div class="col-xs-6 col-lg-2">';
    openside('');
    echo '<div class="text-center">';
    echo '<div>' . get_image($key) . '</div>';
    echo '<span>' . $key . '</span>';
    echo '</div>';
    closeside();
    echo '</div>';
}
echo '</div>';


require_once FUSION_FOOTER;
