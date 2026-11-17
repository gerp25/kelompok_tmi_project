<?php
$document_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$footer_folder = rtrim(str_replace('\\', '/', __DIR__), '/');

$base_url_path = '';
if ($document_root !== '' && strpos($footer_folder, $document_root) === 0) {
    $base_url_path = substr($footer_folder, strlen($document_root));
}

$logo_path = $base_url_path . '/image/logo_resto.jpg';
?>

<style>
/* Menjaga tinggi area konten agar footer terdorong ke bawah tanpa merusak layout */
section.content {
    min-height: calc(100vh - 100px) !important;
    position: relative !important;
    padding-bottom: 70px !important;
}

.footer-sticky-bottom {
    position: absolute !important;
    bottom: 15px !important;
    left: 15px !important;
    right: 15px !important;
    margin-top: 0 !important;
}
</style>

<div class="row clearfix footer-sticky-bottom">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div style="background-color: #bb0a1e; padding: 12px 20px; border-radius: 4px; color: #ffffff;">
            <div class="pull-left" style="line-height: 28px;">
                <img src="<?= htmlspecialchars($logo_path, ENT_QUOTES); ?>" alt="POS" style="width: 25px; height: 28px; vertical-align: middle; margin-right: 8px;">
                <span style="vertical-align: middle;">SISTEM PENJUALAN RESTO | Copyright &copy; MP BUMIAYU <?= date("Y"); ?></span>
            </div>
            <div class="pull-right hidden-xs" style="line-height: 28px;">
                <b>Versi</b> 1.0
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>