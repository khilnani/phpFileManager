<?php

class Render
{
    
    static public function init()
    {
        //echo "Render::init()";
    }
    
    static public function error(&$tmplData, $msg)
    {
        //echo "Render::error() " . $msg;
        
        $tmplData['{{msg}}'] = "<span class='error'>" . $msg . "</span><br /><br />";
    }
    
    static public function success(&$tmplData, $msg)
    {
        //echo "Render::success() " . $msg;
        
        $tmplData['{{msg}}'] = "<span class='success'>" . $msg . "</span><br /><br />";
    }
    
    static public function info(&$tmplData, $msg)
    {
        //echo "Render::info() " . $msg;
        
        $tmplData['{{msg}}'] = "<span class='info'>" . $msg . "</span><br /><br />";
    }
    
    //-----------------------------------------------------------------------------
    
    static public function login($tmplData)
    {
        //echo "Render::login()";
        
        global $ini;
        
        $tmplData['{{content}}'] = Template::render($ini['loginTemplate'], $tmplData);
        Template::display($ini['layoutTemplate'], $tmplData);
    }
    
    static public function edit($tmplData)
    {
        //echo "Render::edit()";
        
        global $ini;
        
        $tmplData['{{content}}'] = Template::render($ini['editTemplate'], $tmplData);
        Template::display($ini['layoutTemplate'], $tmplData);
    }
    
    static public function main($tmplData)
    {
        //echo "Render::main()";
        
        global $ini;
        
        $tmplData['{{content}}'] = Template::render($ini['mainTemplate'], $tmplData);
        Template::display($ini['layoutTemplate'], $tmplData);    
    }
    
    static public function antiTampering($tmplData)
    {
        //echo "Render::antiTampering()";
        
        Render::error($tmplData, "Detected tampering of data. Stop it!!");
        Render::login($tmplData);
        exit;
    }

}

?>