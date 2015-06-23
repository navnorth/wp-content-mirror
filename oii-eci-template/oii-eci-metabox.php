<input type="hidden" id="eci-template" data-template="<?php echo implode("|", OII_ECI_Metabox::$template); ?>" />

<div class="external-content-new-wrap">
    <button type="button" id="new-external-content" class="button">New External Content</button>
</div>

<div id="external-content-wrap">
<?php
    foreach($this->get_external_contents($post->ID) AS $key => $external_content)
    { ?>
    <div class="external-content-item-wrap">
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
                <input type="text" name="external-content-start-code[]" value="<?php echo $external_content->start_code; ?>" />
            </div>
            <div class="col span_2_of_12"><label>End Code</label></div>
            <div class="col span_3_of_12">
                <input type="text" name="external-content-end-code[]" value="<?php echo $external_content->end_code; ?>" />
            </div>
            <div class="col span_2_of_12">
                <a href="#" class="refresh-external-content"><span class="dashicons dashicons-update"></span></a>
                <a href="#" class="delete-external-content"><span class="dashicons dashicons-trash"></span></a>
            </div>
        </div>
    </div>
<?php
    } ?>
</div>