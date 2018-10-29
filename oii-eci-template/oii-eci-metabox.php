<input type="hidden" id="eci-template" data-template="<?php echo implode("|", OII_ECI_Metabox::$template); ?>" />

<div class="external-content-new-wrap">
    <div class="section group">
        <button type="button" id="new-external-content" class="button">New External Content</button>
    </div>
</div>

<div id="external-content-wrap">
<?php
    $external_content_collection = $this->get_external_contents($post->ID);
    
    foreach($external_content_collection AS $key => $external_content)
    {
        if (property_exists($external_content, "id")) {
        ?>
    <div class="external-content-item-wrap">
        <input type="hidden" name="external-content-id[]" value="<?php echo (int) $external_content->id; ?>" />
        <input type="checkbox" class="oii-hidden" name="external-content-active[]" <?php checked($external_content->active, true); ?> />
        <div class="section group external-content-item">
            <div class="col span_2_of_12"><label>URL</label></div>
            <div class="col span_3_of_12">
                <input type="text" name="external-content-url[]" value="<?php echo $external_content->url; ?>" />
            </div>
            <div class="col span_2_of_12"><label>Header</label></div>
            <div class="col span_3_of_12">
                <input type="text" name="external-content-header[]" value="<?php echo $external_content->header; ?>" />
            </div>
            <div class="col span_2_of_12">
                <a href="#" class="move-external-content down<?php if($key == count($external_content_collection) - 1) echo " hidden"; ?>">
                    <span class="dashicons dashicons-arrow-down-alt"></span>
                </a>
                <a href="#" class="move-external-content up<?php if($key == 0) echo " hidden"; ?>">
                    <span class="dashicons dashicons-arrow-up-alt"></span>
                </a>
            </div>
        </div>
        <div class="section group external-content-item">
            <div class="col span_2_of_12"><label>Start Code</label></div>
            <div class="col span_3_of_12">
                <input type="text" name="external-content-start[]" value="<?php echo $external_content->start; ?>" />
            </div>
            <div class="col span_2_of_12"><label>End Code</label></div>
            <div class="col span_3_of_12">
                <input type="text" name="external-content-end[]" value="<?php echo $external_content->end; ?>" />
            </div>
            <div class="col span_2_of_12">
                <a href="external-content/<?php echo (int) $external_content->id; ?>" class="refresh-external-content" title="Refresh external content"><span class="dashicons dashicons-update"></span></a>
                <a href="#" class="disable-external-content" title="Disable external content config"><span class="dashicons dashicons-dismiss"></span></a>
                <a href="#" class="enable-external-content oii-hidden" title="Enable external content config"><span class="dashicons dashicons-yes"></span></a>
                <a href="#" class="delete-external-content" title="Delete external content"><span class="dashicons dashicons-trash"></span></a>
            </div>
        </div>
    </div>
<?php
        }
    } ?>
</div>