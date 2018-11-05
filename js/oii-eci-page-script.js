jQuery(document).ready(function() {
    /**
     * Page
     * @code begin
     */
    if ('page' == pagenow)
    {
        jQuery('#post').submit(function() {
            var post = true
            
            var page_template = jQuery('#page_template').val()
            var template = jQuery('#eci-template').attr('data-template').split('|');
            
            jQuery('#external-content-wrap').find('.error').removeClass('error')
            
            if (template.indexOf(page_template) > -1) {
                jQuery('.external-content-item-wrap').each(function() {
                    var url = jQuery(this).find('input[name="external-content-url[]"]')
                    var start = jQuery(this).find('input[name="external-content-start[]"]')
                    var end = jQuery(this).find('input[name="external-content-end[]"]')
                    
                    if (url.val() || start.val() || end.val()) {
                        
                        if (url.val() == '') {
                            url.addClass('error')
                        }
                        
                        if (start.val() == '') {
                            start.addClass('error')
                        }
                        
                        if (end.val() == '') {
                            end.addClass('error')
                        }
                        
                        if (url.val() == '' || start.val() == '' || end.val() == '') {
                            post = false
                            jQuery('input[type="text"].error').first().focus()
                        }
                    }
                })
            }
            
            return post
        })
    }
    /**
     * Page
     * @code end
     */
    
    /**
     * Page Template Change jQuery Event Handler
     * Description
     */
    jQuery('#page_template').change(function() {
        var page_template = jQuery(this).val()
        var template = jQuery('#eci-template').attr('data-template').split('|');
        
        if (template.indexOf(page_template) == -1) {
            jQuery('#eci-metabox').addClass('hidden')
        
        } else {
            jQuery('#eci-metabox').removeClass('hidden')
        }
    })
    /**
     * New External Content jQuery Event Handler
     * Description
     */
    jQuery('#new-external-content').click(function() {
        var item_wrap = jQuery('<div />').attr({class: 'external-content-item-wrap'})
        
            jQuery('<input />').attr({
                    type: 'hidden',
                    name: 'external-content-id[]',
                    value: 0
                })
                .appendTo(item_wrap)
            
            jQuery('<input />').attr({
                    type: 'checkbox',
                    name: 'external-content-active[]',
                    class: 'oii-hidden',
                    checked: true
                })
                .appendTo(item_wrap)
                
        var text_wrap = jQuery('<div />').attr({class: 'section group external-content-item'})
            // URL Label 
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<label />').text('URL')
                )
                .appendTo(text_wrap)
        
            // URL Input
            jQuery('<div />').attr({class: 'col span_3_of_12'})
                .append(
                    jQuery('<input />').attr({type: 'text', name: 'external-content-url[]'})
                )
                .appendTo(text_wrap)
                
            // Header Label 
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<label />').text('Header')
                )
                .appendTo(text_wrap)
        
            // Header Input
            jQuery('<div />').attr({class: 'col span_3_of_12'})
                .append(
                    jQuery('<input />').attr({type: 'text', name: 'external-content-header[]'})
                )
                .appendTo(text_wrap)
        
            // Order Button
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<a />').attr({href: '#', class: 'move-external-content down hidden'})
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-arrow-down-alt'})
                        )
                )
                .append(' ')
                .append(
                    jQuery('<a />').attr({href: '#', class: 'move-external-content up'})
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-arrow-up-alt'})
                        )
                )
                .appendTo(text_wrap)
            
        var code_wrap = jQuery('<div />').attr({class: 'section group external-content-item'})
        
            // Start Code Label
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<label />').text('Start Code')
                )
                .appendTo(code_wrap)
                
            // Start Code Input
            jQuery('<div />').attr({class: 'col span_3_of_12'})
                .append(
                    jQuery('<input />').attr({type: 'text', name: 'external-content-start[]'})
                )
                .appendTo(code_wrap)
            
            // End Code Label
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<label />').text('End Code')
                )
                .appendTo(code_wrap)
                
            // End Code Input
            jQuery('<div />').attr({class: 'col span_3_of_12'})
                .append(
                    jQuery('<input />').attr({
                        type: 'text',
                        name: 'external-content-end[]'
                    })
                )
                .appendTo(code_wrap)
                
            // Refresh and Delete Button
            jQuery('<div />').attr({class: 'col span_2_of_12'})
                .append(
                    jQuery('<a />').attr({
                            href: 'external-content/0',
                            class:'refresh-external-content',
                            'data-nth': jQuery('.external-content-item-wrap').length + 1
                        })
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-update'})
                        )
                )
                .append(' ')
                .append(
                    jQuery('<a />').attr({
                            href: '#',
                            class: 'disable-external-content'
                        })
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-dismiss'})
                        )
                )
                .append(' ')
                .append(
                    jQuery('<a />').attr({
                            href: '#',
                            class: 'enable-external-content oii-hidden'
                        })
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-yes'})
                        )
                )
                .append(' ')
                .append(
                    jQuery('<a />').attr({
                            href: '#',
                            class: 'delete-external-content'
                        })
                        .append(
                            jQuery('<span />').attr({class: 'dashicons dashicons-trash'})
                        )
                )
                .appendTo(code_wrap)
                
        item_wrap.append(text_wrap).append(code_wrap)
        
        jQuery('#external-content-wrap').append(item_wrap)
        
        change_direction()
    })
    
    /**
     * Migrate External Content jQuery Event Handler
     * Description
     */
    jQuery('#migrate-external-content').on('click', function(event) {
        
        var proceed = confirm('This option will copy the external content into this WordPress page and deactivate future content mirroring. \nWould you like to proceed?');
        
        if (proceed==true) {
            event.preventDefault()
            var my = jQuery(this)
            var section = my.parents('.external-content-item-wrap')
            
            my.find('span.dashicons').addClass('fa-spin')
            
            var post_id = jQuery('#post_ID').val()
            
            if (post_id) {
                
                jQuery.post(ajaxurl, {
                    action: 'migrate_external_content',
                    post_id: post_id
                }, function(response) {
                    try {
                        console.log(response);
                        response = jQuery.parseJSON(response)
                        
                        if ('success' == response.status) {
                            document.location.reload(true);
                        } else {
                            notice(section, response.error.message, 'danger')
                        }
                        
                    } catch(e) {
                    
                    }
                    my.find('span.dashicons').removeClass('fa-spin')
                })
                
            }
        }
    })
    
    /**
     * Delete External Content jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.delete-external-content', 'click', function(event) {
        event.preventDefault()
        
        jQuery(this).parents('.external-content-item-wrap').fadeOut('fast', function() {
            jQuery(this).remove()
            change_direction()
        })
    })
    
    /**
     * Disable External Content jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.disable-external-content', 'click', function(event) {
        event.preventDefault()
        
        var parent = jQuery(this).parents('.external-content-item-wrap');
        parent.addClass('oii-grey-bg');
        parent.find("input[type='text']").attr('readonly', true);
        parent.find("input.oii-external-content-active").attr("checked", false);
        parent.find('.enable-external-content').removeClass('oii-hidden');
        
        jQuery(this).addClass('oii-hidden');
    })
    
    /**
     * Enable External Content jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.enable-external-content', 'click', function(event) {
        event.preventDefault()
        
        var parent = jQuery(this).parents('.external-content-item-wrap');
        parent.removeClass('oii-grey-bg');
        parent.find("input[type='text']").removeAttr('readonly');
        parent.find("input.oii-external-content-active").attr("checked",true);
        parent.find('.disable-external-content').removeClass('oii-hidden');
        
        jQuery(this).addClass('oii-hidden');
    })
    
    
    /**
     * Refresh External Content jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.refresh-external-content', 'click', function(event) {
        event.preventDefault()
        var my = jQuery(this)
        var section = my.parents('.external-content-item-wrap')
            section.find('.error').removeClass('error')
        
        var id = parseInt(my.attr('href').split('/').pop())
        
        if (id) {
            my.find('span.dashicons').addClass('fa-spin')
            
            var post_id = jQuery('#post_ID').val()
            
            var url = section.find('input[name="external-content-url[]"]')
            if (url.val() == '')
                url.addClass('error')
                
            var header = section.find('input[name="external-content-header[]"]')
            
            var open_tag = section.find('input[name="external-content-start[]"]')
            if (open_tag.val() == '')
                open_tag.addClass('error')
                
            var close_tag = section.find('input[name="external-content-end[]"]')
            if (close_tag.val() == '')
                close_tag.addClass('error')
            
            if (url.val() && open_tag.val() && close_tag.val())
            {
                jQuery.post(ajaxurl, {
                    action: 'refresh_external_content',
                    post_id: post_id,
                    id: id,
                    url: url.val(),
                    header: header.val(),
                    open_tag: open_tag.val(),
                    close_tag: close_tag.val()
                }, function(response) {
                    try {
                        response = jQuery.parseJSON(response)
                        
                        if ('success' == response.status) {
                            notice(section, response.success.message, 'success')
                        } else {
                            notice(section, response.error.message, 'danger')
                        }
                        
                    } catch(e) {
                    
                    }
                    my.find('span.dashicons').removeClass('fa-spin')
                })
            }
        }
        else
        {
            notice(section, 'Save the page first before refreshing this external content.', 'danger')
        }
    })
    
    
    /**
     * Move External Content jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.move-external-content', 'click', function(event) {
        event.preventDefault()
        
        if (jQuery(this).hasClass('up')) {
            var up = jQuery(this).parents('.external-content-item-wrap')
            var upper = up.prev('.external-content-item-wrap')
            
            if (upper.length) {
                up.detach()
                
                up.insertBefore(upper)
            }
            
        } else if (jQuery(this).hasClass('down')) {
            var low = jQuery(this).parents('.external-content-item-wrap')
            var lower = low.next('.external-content-item-wrap')
            
            if (lower.length) {
                low.detach()
                
                low.insertAfter(lower)
            }
        }
        
        change_direction()
    })
    /**
     * Change Direction
     * Description
     */
    /**
     * External Content Notice Button jQuery Event Handler
     * Description
     */
    jQuery('#eci-metabox').delegate('.external-content-notice-button', 'click', function() {
        jQuery(this).parents('.external-content-notice').remove()
    })
    function change_direction()
    {
        jQuery('.external-content-item-wrap').map(function(index, object) {
            jQuery(object).find('.move-external-content').removeClass('hidden')
            
            if (index == 0) {
                jQuery(object).find('.move-external-content.up').addClass('hidden')
                
            } else if(index == jQuery('.external-content-item-wrap').length - 1) {
                jQuery(object).find('.move-external-content.down').addClass('hidden')
            }
            
            jQuery(object).find('.refresh-external-content').attr('data-nth', index + 1)
        })
    }
    /**
     * Notice
     * Description
     *
     * @param object section The section
     * @param string message The notice message
     * @param string type The notice type
     */
    function notice(section, message, type)
    {
        section.find('.external-content-notice-button').trigger('click')
        
        section.prepend(
            jQuery('<div />').attr({
                    class: 'external-content-notice ' + (type ? type : 'default')
                })
                .append(
                    jQuery('<span />').text(message)
                ).append(
                    jQuery('<button />').attr({
                            type: 'button',
                            class: 'external-content-notice-button'
                        })
                        .append(
                            jQuery('<span />').attr({
                                    class:'dashicons dashicons-dismiss'
                                })
                        )
                )
        )
    }
})