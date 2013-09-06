<?php

class Session
{
    
    static public function logoff()
    {
        global $_SESSION, $_COOKIE;
        
        $_SESSION = array();
    	if (isset($_COOKIE[session_name()])) 
        {
       	    setcookie(session_name(), '', time()-42000, '/');
    	}
    	session_destroy();
    }
    
    static public function login(&$tmplData)
    {
        global $ini, $_POST, $_SESSION;
        
        if(!($_POST['username'] == $ini['userName'] && $_POST['password'] == $ini['password'])) 
        {
    		Render::error($tmplData, "The login details were incorrect");
            return false;
    	} 
        else 
        {
    		$_SESSION['user'] = $ini['userName'];
    	}
        
        return true;
    }
    
    static public function isLoggedIn()
    {
        global $ini, $_SESSION;
            
        if($ini['loginRequired'] == 1) 
        { 
            if($_SESSION['user'] == $ini['userName']) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        return true;
    }

}

?>