<?php

class Template
{
    static public function init()
    {
        //echo "Template::init()";
        
        global $ini, $_SERVER;
    
        $tmplData = array();
        $tmplData['{{title}}'] = $ini['title'];
        $tmplData['{{action}}'] = $_SERVER[PHP_SELF];
        $tmplData['{{baseUrl}}'] = $ini['baseUrl'];
        $tmplData['{{maxfilesize}}'] = $ini['maxFileSizeBytes'];
        $tmplData['{{msg}}'] = "";
        return $tmplData;
    }
    
    static public function render($tmpl, $data)
    {
        //echo "Template::render()";
        
        global $ini;
    
        //var_dump( $data );
    
        $html = file_get_contents($tmpl);
        $html = str_replace(array_keys($data),array_values($data),$html);
        return $html;
    }

    static public function display($tmpl, $data)
    {
        //echo "Template::display()";
        
        global $ini;
    
        //var_dump( $data );
    
        $html = file_get_contents($tmpl);
        $html = str_replace(array_keys($data),array_values($data),$html);
        echo $html;
        exit;
    }
}

?>