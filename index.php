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

// Configuration variables

$maxfilesize = $ini['maxFileSizeBytes'];
$newdirpermissions = 0700;

$editextensions = $ini['editableFileExtensions']; // add the extensions of file types that you would like to be able to edit
$hiddenfiles = $ini['hiddenFiles'];    // add any file names to this array which should remain invisible
$editon = ($ini['editFiles'] == 1);     // make this = false if you dont want the to use the edit function at all
$makediron = ($ini['makeDir'] == 1);  // make this = false if you dont want to be able to make directories
$deletediron = ($ini['deleteDir'] == 1);  // make this = false if you dont want to be able to make directories

$path = $ini['basePath'];  // directory path, must end with a '/'
$baseUrl = $ini['baseUrl'];  // Url to match the basePath above
$type = $ini['fileTypes'];

$fileiconCSS = $ini['fileIconCSS'];
$foldericonCSS = $ini['folderIconCSS'];

$hideCSS = $ini['hideCSS'];
$showCSS = $ini['showCSS'];

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------

$tmplData = Template::init();

start();

//-----------------------------------------------------------------------------

if($_GET['logoff']) 
{
    //echo "Logoff.";
    doLogoff();
    
    renderLogin($tmplData);
}
elseif(($_POST['login'])  && doLogin($tmplData) == false)  
{
    //echo "Login Failed.";
    renderLogin($tmplData);
}

if( isLoggedIn() === false) 
{ 
    //echo "Not logged in.";
    renderLogin($tmplData);
}
else
{
    //-----------------------------------------------------------------------------
	// Data setup 
    //
    // test the directory to see if it can be opened,
    // this is tested so that it does not try and open
	// and display the contents of directories which the user 
    // does not have persmission to view 
    //-----------------------------------------------------------------------------
	if($_REQUEST['workingdir'] != '') 
    {
        // remove the forward or backwards slash from the path
		$newpath = substr($path.$_REQUEST['workingdir'], 0, -1);   
		$dir = @opendir($newpath);
		// if the directory could not be opened
        if($dir == false) 
        { 
            // go back to displaying the previous screen
			$_GET['back'] = 1; 
		} else {
			@closedir($dir);
		}
	}

    //-----------------------------------------------------------------------------
    // ACTION: Back link
    //-----------------------------------------------------------------------------
	if($_GET['back'] != '') 
    { 
		$_REQUEST['workingdir'] = substr($_REQUEST['workingdir'],0,-1);
		$slashpos = strrpos($_REQUEST['workingdir'],'/');
		if($slashpos == 0) 
        {
			$_REQUEST['workingdir'] = '';	
		}
        else
        {
			$_REQUEST['workingdir'] = substr($_REQUEST['workingdir'],0,$slashpos+1);
		}
	}

    //-----------------------------------------------------------------------------
    // ACTION: File edit
    //-----------------------------------------------------------------------------
	if($_GET['edit'] != '') 
    { 
		$fp = fopen($path.$_REQUEST['workingdir'].$_GET['edit'], "r");
		$oldcontent = fread($fp, filesize($path.$_REQUEST['workingdir'].$_GET['edit']));
		fclose($fp);

        $tmplData['{{workingdir}}'] = $_REQUEST['workingdir'];
        $tmplData['{{editingFile}}'] = $_GET['edit'];
        $tmplData['{{oldcontent}}'] =  $oldcontent;

        renderEdit($tmplData);
	}

    //-----------------------------------------------------------------------------
    // ACTION: File upload
    //-----------------------------------------------------------------------------
	if ($_POST['upload'] != '') 
    { 
        // if a file was actually uploaded 
		if($_FILES['uploadedfile']['name'] != '') 
        { 
            // remove any % signs from the file name
			$_FILES['uploadedfile']['name'] = str_replace('%','',$_FILES['uploadedfile']['name']);  
		
            // if the file size is within allowed limits
			if($_FILES['uploadedfile']['size'] > 0 && $_FILES['uploadedfile']['size'] < $maxfilesize) 
            {
				// put the file in the directory
				move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $path.$_REQUEST['workingdir'].$_FILES['uploadedfile']['name']);	

				success($tmplData, "Uploaded file '" . $_REQUEST['workingdir'].$_FILES['uploadedfile']['name']  . "' successfully.");
			}
            else
            {
				$maxkb = round($maxfilesize/1000); // show the max file size in Kb
				error($tmplData, "The file was greater than the maximum allowed file size of $maxkb Kb and could not be uploaded.");
			}
		} 
        else 
        {
			info($tmplData, "Please press the browse button and select a file to upload before you press the upload button.");
		}
        
	}

    //-----------------------------------------------------------------------------
    // ACTION: Edit file save
    //-----------------------------------------------------------------------------
	if($_POST['save'] != '') 
    { 
		$newcontent = $_POST['newcontent'];
		$newcontent = stripslashes($newcontent);
        
		$fp = fopen($path.$_REQUEST['workingdir'].$_POST['savefile'], "w");
		fwrite($fp, $newcontent);
		fclose($fp);
        
        success($tmplData, "'" . $_POST['savefile'] . "' saved successfully.");
	}

    //-----------------------------------------------------------------------------
    // ACTION: File/folder delete
    //-----------------------------------------------------------------------------
	if($_GET['delete'] != '') 
    {
		// delete the file or directory
		if(is_dir($path.$_REQUEST['workingdir'].$_GET['delete'])) 
        {    
            $result = rrmdir($path.$_REQUEST['workingdir'].$_GET['delete']);
			
            if($result == 0) 
            {
				error($tmplData, "The folder '" . $_REQUEST['workingdir'].$_GET['delete'] . "' could not be deleted. The folder must be empty before you can delete it.");
			}
			else
			{
				success($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' deleted successfully.");
			}
		}
        else 
        {
			if(file_exists($path.$_REQUEST['workingdir'].$_GET['delete'])) 
            {
				if( unlink($path.$_REQUEST['workingdir'].$_GET['delete']) )
				{
	                success($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' deleted successfully.");
				}
				else
				{
                      error($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' could not be deleted.");
				}
			}
			else
			{
				info($tmplData, "Somebody deleted '" . $_REQUEST['workingdir'].$_GET['delete'] . "' before I could.");
			}
		}
	}

    //-----------------------------------------------------------------------------
    // ACTION: File rename
    //-----------------------------------------------------------------------------
	if($_GET['rename'] != '') 
    {
        if( trim($_GET['newname']) == "" ) 
        {
            error($tmplData, "Unable to rename. Invalid filename '" . $_GET['newname'] . "'.");
        }
		elseif( file_exists($path.$_REQUEST['workingdir'].$_GET['newname']) )
		{
			error($tmplData, "Unable to rename '" . $_GET['rename'] . "'. '" . $_GET['newname'] . "' already exists.");
		}
        else
		{
			$result = @rename ($path.$_REQUEST['workingdir'].$_GET['rename'], $path.$_REQUEST['workingdir'].$_GET['newname']);
            
            if($result == 0) 
			{
                error($tmplData, "The folder/file '" . $_GET['rename'] . "' could not be moved/renamed to '" . $_GET['newname'] . "'.");
            }
			else
			{
				success($tmplData, "'" . $_GET['rename'] . "' successfully moved/renamed to '" . $_GET['newname'] . "'.");
			}
		}
	} 
    
    //-----------------------------------------------------------------------------
    // ACTION: File unzip
    //-----------------------------------------------------------------------------
    if($_GET['unzip'] != '') 
    {
        if( trim($_GET['newname']) == "" ) 
	    {
			error($tmplData, "Unable to unzip. Invalid filename '" . $_GET['newname'] . "'.");
		}
		elseif( file_exists($path.$_REQUEST['workingdir'].$_GET['newname']) )
        {
            error($tmplData, "The directory '" . $_GET['newname'] . "' already exists.");
        }
        else
        {
			if (@exec("unzip -n ". $path.$_REQUEST['workingdir'].$_GET['unzip'] ." -d ". $path.$_REQUEST['workingdir'].$_GET['newname'])) 
            {
				success($tmplData, "'" . $_GET['unzip'] . "' successfully unzipped to '" . $_GET['newname'] . "'.");
			}
            else
            {
				error($tmplData, "'" . $_GET['unzip'] . "' could not be unzipped to '" . $_GET['newname'] . "'.");
			}
        }
    }

    //-----------------------------------------------------------------------------
    // ACTION: Make dir
    //-----------------------------------------------------------------------------
	if($_POST['mkdir'] != '' && $makediron === true) 
    { 
		if(strpos($path.$_REQUEST['workingdir'].$_POST['dirname'],'//') === false ) 
        {
			$result = @mkdir($path.$_REQUEST['workingdir'].$_POST['dirname'], $newdirpermissions);
			
            if($result == 0) 
            {
				error($tmplData, "The folder '" . $_POST['dirname'] . "' could not be created. Make sure the name you entered is a valid folder name.");
			}
			else
            {
				success($tmplData, "Created folder '" . $_REQUEST['workingdir'].$_POST['dirname'] . "'");
			}
		}
		else 
        {
			error($tmplData, "Requested directory name '" . $_POST['dirname'] . "' failed validation.");
		}
	}

    //-----------------------------------------------------------------------------
    // Main Page display
    //-----------------------------------------------------------------------------

    $content = '';
    $tmplData['{{workingdir}}'] = $_REQUEST['workingdir'];   
        
	// if $makediron has been set to on show some html for making directories
	if($makediron === true) 
    {
		$tmplData['{{displayCreateFolder}}'] = $showCSS;
	}
    else
    {
        $tmplData['{{displayCreateFolder}}'] = $hideCSS;
    } 
        
    // if the current directory is a sub directory show a back 
    // link to get back to the previous directory
    if($_REQUEST['workingdir'] != '') 
    {
		$tmplData['{{displayBackLink}}'] = $showCSS;
	}
    else
    {
        $tmplData['{{displayBackLink}}'] = $hideCSS;
    }
    
    //-----------------------------------------------------------------------------
 
	// build the table rows which contain the file information
    // remove the forward or backwards slash from the path
	$newpath = substr($path.$_REQUEST['workingdir'], 0, -1);   
	
    // open the directory
    $dir = @opendir($newpath); 
	
    // loop once for each name in the directory
    while($file = readdir($dir)) 
    { 
		$filearray[] = $file;
	}
	natcasesort($filearray);

	foreach($filearray as $key => $file) 
    {

		// check to see if the file is a directory and if it can be opened, if not hide it
		$hiddendir = 0;
		if(is_dir($path.$_REQUEST['workingdir'].$file)) 
        {
			$tempdir = @opendir($path.$_REQUEST['workingdir'].$file);
			if($tempdir == false) { $hiddendir = 1;}
			@closedir($tempdir);
		}

		// if the name is not a directory and the name is not the name of this program file 
		if($file != '.' && $file != '..' && $file != basename(__FILE__) && $hiddendir != 1) 
        {
			$match = false;
            // for each value in the hidden files array
			foreach($hiddenfiles as $name) 
            { 
                // check the name is not the same as the hidden file name
				if($file == $name) 
                { 
                    // set a flag if this name is supposed to be hidden
					$match = true;	 
				}
			}	

            // if there were no matches the file should not be hidden
			if($match === false) 
            { 
                // get some info about the file
				$filedata = stat($path.$_REQUEST['workingdir'].$file); 
				$encodedfile = rawurlencode($file);
				
				$showEdit = false;
    			$showView = true;
				$showDelete = true;
                $showUnzip = false;
				
                // find out if the file is one that can be edited
                // if the edit function is turned on and the file is not a directory
                if($editon === true && !is_dir($path.$_REQUEST['workingdir'].$file)) 
                { 
					$dotpos = strrpos($file,'.');
					foreach($editextensions as $editext) 
                    {
						$ext = substr($file, ($dotpos+1));
						if(strcmp( strtolower( $ext ) , $editext) == 0) 
                        {
							$showEdit = true;
						}
					}
				}



                // if it is a directory change the file name to a directory link
                if(is_dir($path.$_REQUEST['workingdir'].$file)) 
                {
					$isFolder = true;
					$fileicontypeCSS = $foldericonCSS;
					$downloadlink = '';
					$filedata[7] = '';
					$modified = '';
					$filetype = '';
					if($deletediron === false) 
                    {
						$showDelete = false;
					}
					$showEdit = false;
                    $showView = false;
				}
                else 
                {
					$isFolder = false;
					$fileicontypeCSS = $fileiconCSS;

					$pathparts = pathinfo($file);
					$filetype = $type[ strtolower($pathparts['extension']) ];


                    if( strtolower( $pathparts['extension'] ) == 'zip') 
                    {
                        $showUnzip = true;
                        $showEdit = false;
                    }

                    $modified = date('d-M-y g:ia',$filedata[9]);

                    if($filedata[7] > 1024) 
                    {
                        $filedata[7] = round($filedata[7]/1024);
                        if($filedata[7] > 1024) 
                        {
                            $filedata[7] = round($filedata[7]/1024);
                            if($filedata[7] > 1024) 
                            {
                                $filedata[7] = round($filedata[7]/1024,1);
                                if($filedata[7] > 1024) 
                                {
                                    $filedata[7] = round($filedata[7]/1024);
                                    $filedata[7] = $filedata[7].'Tb';
                                }
                                else 
                                {
                                    $filedata[7] = $filedata[7].'Gb';
                                }
                            }
                            else
                            {
                                $filedata[7] = $filedata[7].'Mb';
                            }
                        }
                        else 
                        {
                            $filedata[7] = $filedata[7].'Kb';
                        }
                    }
                    else 
                    {
						$filedata[7] = $filedata[7].'b';
					}
				}

				// append 2 table rows to the $content variable, the first row has the file
				// informtation, the 2nd row makes a black line 1 pixel high
                        
                $tmplData['{{fileicontypeCSS}}'] = $fileicontypeCSS;
                $tmplData['{{filename}}'] = $file;                 
                $tmplData['{{isFolder}}'] = ($isFolder) ? $showCSS : $hideCSS;
                $tmplData['{{isFile}}'] = ($isFolder) ? $hideCSS : $showCSS;
                $tmplData['{{encodedfile}}'] = $encodedfile;
                $tmplData['{{filetype}}'] = $filetype;
                $tmplData['{{filedata}}'] = $filedata[7];
                $tmplData['{{modified}}'] = $modified;
                $tmplData['{{showView}}'] = ($showView) ? $showCSS : $hideCSS;
                $tmplData['{{showDelete}}'] = ($showDelete) ? $showCSS : $hideCSS;
                $tmplData['{{showEdit}}'] = ($showEdit) ? $showCSS : $hideCSS;
                $tmplData['{{showUnzip}}'] = ($showUnzip) ? $showCSS : $hideCSS;

				$content .= Template::render($ini['mainRowTemplate'], $tmplData);
			}
		}
	}
	
    // now that all the rows have been built close the directory
    closedir($dir); 

    //-----------------------------------------------------------------------------
    
    $tmplData['{{content}}'] = $content;
    renderMain($tmplData);

}

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------

function start()
{
    global $ini, $_REQUEST, $_GET;

    if(strpos($_REQUEST['workingdir'],'..') !== false) 
    {
        renderAntiTemperingView();
    }
    if(strpos($_REQUEST['delete'],'..') !== false) 
    {
        renderAntiTemperingView();
    }
    if(strpos($_REQUEST['delete'],'/') !== false) 
    {
	    renderAntiTemperingView();
    }
    if(strpos($_GET['edit'],'..') !== false) 
    {
        renderAntiTemperingView();
    }
    
    session_start();
            
    $timezone = $ini['timezone'];
    if($timezone != '') {
        putenv("TZ=$timezone"); 
    }
}


function doLogoff()
{
    global $_SESSION, $_COOKIE;
    
    $_SESSION = array();
	if (isset($_COOKIE[session_name()])) 
    {
   	    setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();
}

function doLogin(&$tmplData)
{
    global $ini, $_POST, $_SESSION;
    
    if(!($_POST['username'] == $ini['userName'] && $_POST['password'] == $ini['password'])) 
    {
		error($tmplData, "The login details were incorrect");
        return false;
	} 
    else 
    {
		$_SESSION['user'] = $ini['userName'];
	}
    
    return true;
}

function isLoggedIn()
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


function rrmdir($dir) 
{
    if (is_dir($dir)) 
    {
        $objects = scandir($dir);
        foreach ($objects as $object) 
        {
            if ($object != "." && $object != "..") 
            {
                if (filetype($dir."/".$object) == "dir") 
                    rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
    
    return true;
}

//-----------------------------------------------------------------------------

function error(&$tmplData, $msg)
{
    //echo "ERROR: " . $msg;
    $tmplData['{{msg}}'] = "<span class='error'>" . $msg . "</span><br /><br />";
}

function success(&$tmplData, $msg)
{
    //echo "OK: " . $msg;
    $tmplData['{{msg}}'] = "<span class='success'>" . $msg . "</span><br /><br />";
}

function info(&$tmplData, $msg)
{
    //echo "INFO: " . $msg;
    $tmplData['{{msg}}'] = "<span class='info'>" . $msg . "</span><br /><br />";
}

//-----------------------------------------------------------------------------

function renderLogin($tmplData)
{
    global $ini;
    
    $tmplData['{{content}}'] = Template::render($ini['loginTemplate'], $tmplData);
    Template::display($ini['layoutTemplate'], $tmplData);
}

function renderEdit($tmplData)
{
    global $ini;
    
    $tmplData['{{content}}'] = Template::render($ini['editTemplate'], $tmplData);
    Template::display($ini['layoutTemplate'], $tmplData);
}

function renderMain($tmplData)
{
    global $ini;
    
    $tmplData['{{content}}'] = Template::render($ini['mainTemplate'], $tmplData);
    Template::display($ini['layoutTemplate'], $tmplData);    
}

function renderAntiTemperingView()
{
    global $tmplData;
    
    error($tmplData, "Detected tampering of data. Stop it!!");
    renderLogin($tmplData);
    exit;
}

//-----------------------------------------------------------------------------

class Template
{
    static public function init()
    {
        global $ini, $_SERVER, $baseUrl, $maxfilesize;
    
        $tmplData = array();
        $tmplData['{{title}}'] = $ini['title'];
        $tmplData['{{action}}'] = $_SERVER[PHP_SELF];
        $tmplData['{{baseUrl}}'] = $baseUrl;
        $tmplData['{{maxfilesize}}'] = $maxfilesize;
        $tmplData['{{username}}'] = $_REQUEST[username];
        $tmplData['{{msg}}'] = "";
        return $tmplData;
    }
    
    static public function render($tmpl, $data)
    {
        global $ini;
    
        //var_dump( $data );
    
        $html = file_get_contents($tmpl);
        $html = str_replace(array_keys($data),array_values($data),$html);
        return $html;
    }

    static public function display($tmpl, $data)
    {
        global $ini;
    
        //var_dump( $data );
    
        $html = file_get_contents($tmpl);
        $html = str_replace(array_keys($data),array_values($data),$html);
        echo $html;
        exit;
    }
}

//-----------------------------------------------------------------------------

?>



