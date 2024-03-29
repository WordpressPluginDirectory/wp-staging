<?php

use WPStaging\Framework\Facades\Sanitize;

?>
<div class="wpstg_admin" id="wpstg-clonepage-wrapper">
    <?php

    require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/header.php');

    $isActiveSystemInfoPage = true;
    require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/main-navigation.php');
    ?>
    <div class="wpstg-loading-bar-container">
        <div class="wpstg-loading-bar"></div>
    </div>
    <div class="wpstg-tabs-container" id="wpstg-tools">
        <div class="wpstg-metabox-holder">
            <?php
            $numberOfLoadingBars = 100;
            include(WPSTG_PLUGIN_DIR . 'Backend/views/_main/loading-placeholder.php');
            require_once($this->path . "views/tools/tabs/system-info.php");
            ?>
        </div>
    </div>
</div>
<?php
    require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/footer.php');
?>
