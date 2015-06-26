function External_Content_Format() {
    var my = this
    /**
     */
    this.type = function(source, input) {
        switch (source) {
            case 'replace':
                /**
                 * Unpaired HTML Tag
                 * @code begin
                 * @todo
                 */
                /**
                var up = new RegExp('^<\\w+\\s?/?>$')
                var up_attribute = new RegExp('^<\\w+(\\s((\\w+=(("\\S+")|(\'\\S+\')))|(\\w+)))+((>)|(\\s?/>))$')
                var up_any = new RegExp()
                
                if (up.test(input) && my.is_single_tag(input)) {
                    return 'single'
                
                } else if (up_attribute.test(input) && my.is_single_tag(input)) {
                    return 'single-attribute'
                
                } else if (up_any.test(input) && my.is_single_tag(input)) {
                    return 'single-any'
                }
                */
                /**
                 * Unpaired HTML Tag
                 * @code begin
                 */
                
                /**
                 * Paired HTML Tag
                 * @code begin
                 */
                // var p = new RegExp('^<(\\w+)>(.\*)</\\1>$')
                var p_attribute = new RegExp('^<(\\w+)(\\s((\\w+=((\'\\S+\')|("\\S+")))|(\\w+)))+>(.*)</\\1>$')
                // var p_any = new RegExp()
                
                /**
                if (p.test(input) && my.is_pair_tag(input)) {
                    return 'paired'
                
                }
                else */if (p_attribute.test(input) && my.is_pair_tag(input)) {
                    return 'paired-attribute'
                
                }
                /**
                else if (p_any.test(input) && my.is_pair_tag(input)) {
                    return 'paired-any'
                }
                */
                /**
                 * Paired HTML Tag
                 * @code end
                 */
                
                return null
                break;
            
            case 'with':
                /**
                var up = new RegExp()
                var up_attribute = new RegExp()
                var up_any = new RegExp()
                
                if (up.test(input) && my.is_single_tag(input)) {
                    return 'single'
                
                } else if (up_attribute.test(input) && my.is_single_tag(input)) {
                    return 'single-attribute'
                
                } else if (up_any.test(input) && my.is_single_tag(input)) {
                    return 'single-any'
                }
                */
                
                var p = new RegExp('^<(\\w+)>\1</\\1>$')
                //var p_attribute = new RegExp('^<(\\w+)(\\s((\\w+=((\'\\S+\')|("\\S+")))|(\\w+)))+>\1</\\1>$')
                //var p_any = new RegExp()
                
                if (p.test(input) && my.is_pair_tag(input)) {
                    return 'paired'
                
                }
                /**
                else if (p_attribute.test(input) && my.is_pair_tag(input)) {
                    return 'paired-attribute'
                
                } else if (p_any.test(input) && my.is_pair_tag(input)) {
                    return 'paired-any'
                }
                */
                
                return null
                break;
        }
    
        return null;
    }
    /**
     * Tag
     * Description
     *
     * @param string input The input
     * @returns string The tag
     */
    this.tag = function(input) {
        if (input.match(/\w+/)) {
            return input.match(/\w+/).shift()
        }
        
        return null
    }
    /**
     * Is Single Tag
     * Description
     *
     * @param string input The input
     * @returns boolean Is Single?
     */
    this.is_single_tag = function(input) {
        var single = [
            'area',
            'base', 'br',
            'col',
            'embed',
            'hr',
            'img', 'input',
            'keygen',
            'link',
            'meta',
            'param',
            'source',
            'track'
        ]
        
        return (single.indexOf(my.tag(input)) == -1) ? false : true;
    }
    /**
     * Is Pair Tag
     * Description
     *
     * @param string input The input
     * @returns boolean Is Pair?
     */
    this.is_pair_tag = function(input) {
        var pair = [
            'a', 'abbr', 'address', 'article', 'aside', 'audio',
            'b', 'bdi', 'bdo', 'blockquote', 'body', 'button',
            'canvas', 'caption', 'cite', 'code', 'colgroup',
            'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'dt',
            'em',
            'fieldset', 'figcaption', 'figure', 'footer', 'form',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'html',
            'i', 'iframe', 'ins',
            'kbd',
            'label', 'legend', 'li',
            'main', 'map', 'mark', 'menu', 'menuitem', 'meter',
            'nav', 'noscript',
            'object', 'ol', 'optgroup', 'option', 'output',
            'p', 'pre', 'progress',
            'q',
            'rp', 'rt', 'ruby',
            's', 'samp', 'script', 'section', 'select', 'small', 'span', 'strong', 'style', 'sub', 'summary', 'sup',
            'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr',
            'u', 'ul', 'var', 'video', 'wbr'
        ]
        
        return (pair.indexOf(my.tag(input)) == -1) ? false : true
    }
}