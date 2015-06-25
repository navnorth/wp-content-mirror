<input type="hidden" id="eci-template" data-template="<?php echo implode("|", OII_ECI_Metabox::$template); ?>" />
<style>
    .external-content-new-wrap .notice .notice-dismiss {
        padding: 5px;
    }
    .external-content-new-wrap .notice p {
        margin: 2.5px;
    }
</style>
<div class="external-content-new-wrap">
    <div class="section group">
        <div class="col span_9_of_12">
            <div id="message" class="updated notice notice-success is-dismissible below-h2">
                <p style="margin: 2.5px;">Page updated</p>    
            </div>
        </div>
        <div class="col span_3_of_12">
            <button type="button" id="new-external-content" class="button">New External Content</button>
        </div>
    </div>
</div>

<div id="external-content-wrap">
<?php
    foreach($this->get_external_contents($post->ID) AS $key => $external_content)
    { ?>
    <div class="external-content-item-wrap">
        <input type="hidden" name="external-content-id[]" value="<?php echo (int) $external_content->id; ?>" />
        
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
                <a href="#" class="move-external-content down">
                    <span class="dashicons dashicons-arrow-down-alt"></span>
                </a>
                <a href="#" class="move-external-content up hidden">
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
                <a href="external-content/<?php echo (int) $external_content->id; ?>" class="refresh-external-content"><span class="dashicons dashicons-update"></span></a>
                <a href="#" class="delete-external-content"><span class="dashicons dashicons-trash"></span></a>
            </div>
        </div>
    </div>
<?php
    } ?>
</div>