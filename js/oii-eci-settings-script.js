jQuery(document).ready(function() {
    /**
     * Submit jQuery Event Handler
     */
    jQuery('#submit').click(function(event) {
        var submit = true
        var format = new OII_ECI_Settings_Format()
        
        jQuery('.form-element').removeClass('error')
        
        var name = jQuery('#oii-eci-new-regex').attr('data-name')
        
        jQuery('.regex').each(function() {    
            var replace_object = jQuery(this).children('.regex-replace')
            var r = jQuery.trim(replace_object.val())
            
            if (r == '') {
                replace_object.addClass('error')
                
                submit = false
            
            } else {
                var replace_type = format.type('replace', r)
                console.log(replace_type)
                if ('paired-attribute' == replace_type || 'paired' == replace_type) {
                    // Pair HTML Tag
                    
                    var with_object = jQuery(this).children('.regex-with')
                        w = jQuery.trim(with_object.val())
                        
                    var with_type = format.type('with', w)
                    
                    if ('paired-attribute' == with_type || 'paired' == with_type) {
                        
                        
                    } else {
                        with_object.addClass('error')
                        submit = false
                    }
                    
                } else {
                    replace_object.addClass('error')
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
})