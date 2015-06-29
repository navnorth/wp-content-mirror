<?php
class OII_ECI_Settings_Format {
    public function __construct()
    {
        
    }
    /**
     * Type
     * Description
     *
     * @param string $source The source
     * @param string $input The input
     *
     * @return string The type
     */
    public function type($source =  NULL, $input = NULL)
    {
        switch($source)
        {
            case "replace":
                /**
                 * Unpaired HTML Tag
                 * @code begin
                 */
                //$up = NULL;
                //$up_attribute = NULL;
                //$up_any = NULL;
                /**
                 * Unpaired HTML Tag
                 * @code end
                 */
                
                /**
                 * Paired HTML Tag
                 * @code begin
                 */
                $p = "/<(\w+)>\(.\*\)<\/\g{1}>$/";
                preg_match($p, $input, $p_match);
                
                $p_attribute = "/^<(\w+)(\s((\w+\=((\'\S+\')|(\"\S+\")))|(\w+)))+>\(.\*\)<\/\g{1}>$/";
                preg_match($p_attribute, $input, $p_attribute_match);
                
                //$p_any = NULL;
                //preg_match($p_any, $input, $p_any_match);                
                
                if(count($p_match))
                    return "paired";
                
                else if(count($p_attribute_match))
                    return "paired-attribute";
                
                else if(false)
                    return "paired-any";
                /**
                 * Paired HTML Tag
                 * @code end
                 */
                break;
            
            case "with":
                /**
                 * Unpaired HTML Tag
                 * @code begin
                 */
                $up = NULL;
                $up_attribute = NULL;
                $up_any = NULL;
                /**
                 * Unpaired HTML Tag
                 * @code end
                 */
                
                /**
                 * Paired HTML Tag
                 * @code begin
                 */
                $p = "/^<(\w+)>\\\\1<\/\g{1}>$/";
                preg_match($p, $input, $p_match);
                
                $p_attribute = "/^<(\w+)(\s((\w+\=((\'\S+\')|(\"\S+\")))|(\w+)))+>\\\\1<\/\g{1}>$/";
                preg_match($p_attribute, $input, $p_attribute_match);
                
                $p_any = NULL;
                
                if(count($p_match))
                    return "paired";
                /**
                 * Paired HTML Tag
                 * @code end
                 */
                break;
        }
        
        return NULL;
    }
}
