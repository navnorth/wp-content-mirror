jQuery(document).ready(function() {
    /**
     * Submit jQuery Event Handler
     * Description
     */
    jQuery('#submit').click(function(event) {
        var submit = true
        var format = new OII_ECI_Settings_Format()
        
        jQuery('.form-element').removeClass('error')
        
        var name = jQuery('#oii-eci-new-regex').attr('data-name')
        
        jQuery('.regex > .description.pattern').remove()
        
        jQuery('.regex').each(function() {    
            var replace_object = jQuery(this).children('.regex-replace')
            var r = jQuery.trim(replace_object.val())
            
            if (r == '') {
                replace_object.addClass('error')
                
                jQuery(this).append(
                    jQuery('<p />').attr({
                        class: 'description pattern',
                        style: 'color: #C3363F; margin-left: 56px'
                    }).text('Replace pattern is required')
                )
                
                submit = false
            
            } else {
                var replace_type = format.type('replace', r)
                
                var with_object = jQuery(this).children('.regex-with')
                    w = jQuery.trim(with_object.val())
                
                if ('paired-attribute' == replace_type || 'paired' == replace_type) {
                    // Pair HTML Tag
                    var with_type = format.type('with', w)
                    
                    if ('paired-attribute' == with_type || 'paired' == with_type) {
                    
                    } else {
                        with_object.addClass('error')
                        
                        jQuery(this).append(
                            jQuery('<p />').attr({
                                class: 'description pattern',
                                style: 'color: #C3363F; margin-left: 56px'
                            }).text('Incorrect/ unsupported replace with pattern')
                        )
                        
                        submit = false
                    }
                    
                } else if('single-attribute-any' == replace_type) {
                    if (w) {
                        with_object.addClass('error')
                        
                        jQuery(this).append(
                            jQuery('<p />').attr({
                                class: 'description pattern',
                                style: 'color: #C3363F; margin-left: 56px'
                            }).text('Incorrect/ unsupported replace with pattern')
                        )
                        
                        submit = false
                    }
                } else {
                    replace_object.addClass('error')
                    
                    jQuery(this).append(
                        jQuery('<p />').attr({
                            class: 'description pattern',
                            style: 'color: #C3363F; margin-left: 56px'
                        }).text('Incorrect/ unsupported replace pattern')
                    )
                    
                    submit = false
                }
            }
        })
        
        if (jQuery('#schedule').val() == '') {
            jQuery('#schedule').addClass('error')
            
            submit = false
        }
        
        return submit
    })
    /**
     * Delete Regex jQuery Event Handler
     * Description
     */
    jQuery(document).delegate('.delete-regex', 'click', function(event) {
        event.preventDefault()
        
        jQuery(this).parents('.regex').remove()
    })
    /**
     * New Regex jQuery Event Handler
     * Description
     */
    jQuery('#oii-eci-new-regex').click(function() {
        var name = jQuery(this).attr('data-name')
        
        var replace = jQuery('<input />').attr({
            type: 'text',
            class: 'form-element regex-replace',
            name: name + '[replace][]'
        })
        
        var to = jQuery('<input />').attr({
            type: 'text',
            class: 'form-element regex-with',
            name: name + '[with][]'
        })
        
        var anchor = jQuery('<a />').attr({
            href: '#',
            style: 'color: #555; text-decoration: none',
            
        }).html(
            jQuery('<span />').attr({
                class: 'delete-regex dashicons dashicons-trash',
                style: 'vertical-align: text-bottom'
            })
        )
        
        var div = jQuery('<div />').attr({class: 'regex', style:'margin-top: 15px'})
            .append('Replace ')
            .append(replace)
            .append(' with ')
            .append(to)
            .append(anchor)
            .insertAfter(
                jQuery('.regex').last()
            )
    })
    /**
     * Refresh All Contents jQuery Event Handler
     * Description
     */
    jQuery('#refresh-all-external-contents').click(function() {
        if (window.confirm("Are you sure you want to refresh all external content? \nThis will take some time and may be taxing on the server.")) {
            var button = jQuery(this)
            var notice = button.parent().find('.notice')
            var spinner = button.parent().find('.spinner')
            var normal = button.text()
            var desc = button.parent().find('.description')
            
            button.addClass('disabled')
                .text(jQuery(this).data('loading-text'))
            
            spinner.addClass('is-active')
            
            notice.addClass('hidden')
                .find('strong').text('')
            
            desc.css('color', '#666').text(desc.data('default-text'))
                
            jQuery.post(ajaxurl, {
                action: 'refresh_all_external_contents'
            }, function(response) {
                notice.removeClass('hidden').find('strong').text('Contents successfully refreshed.')
                desc.css('color', '#3c763d').text('Contents successfully refreshed.')
            })
            .always(function() {
                button.text(normal).removeClass('disabled')
                spinner.removeClass('is-active')
            })
        }
    })
    
    /**
     * Refresh All Contents jQuery Event Handler
     * Description
     */
    jQuery('#migrate-all-external-contents').click(function() {
        if (window.confirm("Are you sure you want to migrate all external contents? \nThis will take some time and will process 50 pages at a time.")) {
            var button = jQuery(this)
            var notice = button.parent().find('.notice')
            var spinner = button.parent().find('.spinner')
            var normal = button.text()
            var desc = button.parent().find('.description')
            
            button.addClass('disabled')
                .text(jQuery(this).data('loading-text'))
            
            spinner.addClass('is-active')
            
            notice.addClass('hidden')
                .find('strong').text('')
            
            desc.css('color', '#666').text(desc.data('default-text'))
                
            jQuery.post(ajaxurl, {
                action: 'migrate_all_external_contents'
            }, function(response) {
                notice.removeClass('hidden').find('strong').text('Contents successfully migrated.')
                desc.css('color', '#3c763d').text('Contents successfully migrated.')
            })
            .always(function() {
                button.text(normal).removeClass('disabled')
                spinner.removeClass('is-active')
            })
        }
    })
    
    /**
     * Refresh All Contents jQuery Event Handler
     * Description
     */
    jQuery('#reset-all-external-contents').click(function() {
        if (window.confirm("Are you sure you want to reset migrate count? \nThis will enable again the migrate all button.")) {
            var button = jQuery(this)
            var notice = button.parent().find('.notice')
            var spinner = button.parent().find('.spinner')
            var normal = button.text()
            var desc = button.parent().find('.description')
            
            button.addClass('disabled')
                .text(jQuery(this).data('loading-text'))
            
            spinner.addClass('is-active')
            
            notice.addClass('hidden')
                .find('strong').text('')
            
            desc.css('color', '#666').text(desc.data('default-text'))
                
            jQuery.post(ajaxurl, {
                action: 'reset_all_external_contents'
            }, function(response) {
                notice.removeClass('hidden').find('strong').text('Migrate count successfully reset.')
                desc.css('color', '#3c763d').text('Migrate count successfully reset.')
                document.location.reload(true)
            })
            .always(function() {
                button.text(normal).removeClass('disabled')
                spinner.removeClass('is-active')
            })
        }
    })
})