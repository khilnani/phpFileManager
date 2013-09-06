<?php

/* 
* phpFileManager
*
* Author: Nik Khilnani / http://khilnani.org
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*/

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------

$ini = parse_ini_file("./config.ini");

//-----------------------------------------------------------------------------

require_once './app/autoload.php';

//----------------------------------------------------------------------------- 

Render::init();
$tmplData = Template::init();
$initedOK = Route::init();

if($initedOK == false)
    Render::antiTampering($tmplData);

//-----------------------------------------------------------------------------

if($_GET['logoff']) 
{
    //echo "Logoff.";
    Session::logoff();
    
    Render::login($tmplData);
}
elseif(($_POST['login'])  && Session::login($tmplData) == false)  
{
    //echo "Login Failed.";
    Render::login($tmplData);
}

if( Session::isLoggedIn() === false) 
{
    //echo "Not logged in.";
    Render::login($tmplData);
}
else
{
    //echo "showlist.";
    Route::main($tmplData);

}

//-----------------------------------------------------------------------------
//----------------------------------------------------------------------------- 

?>



