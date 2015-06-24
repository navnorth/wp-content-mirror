jQuery(document).ready(function() {
    /**
     * Submit jQuery Event Handler
     */
    jQuery('#submit').click(function(event) {
        var submit = true
        
        jQuery('.form-element').removeClass('error')
        
        var name = jQuery('#oii-eci-new-regex').attr('data-name')
        
        jQuery('.regex').each(function() {
            
            var replace = jQuery(this).children('.regex-replace')
            
            if (jQuery.trim(replace.val()) == '') {
                replace.addClass('error')
                
                submit = false
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
     */
    jQuery(document).delegate('.delete-regex', 'click', function(event) {
        event.preventDefault()
        
        jQuery(this).parents('.regex').remove()
    })
    /**
     * New Regex jQuery Event Handler
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
})