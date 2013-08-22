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

$arrowiconImage = "<img alt='Back' src='data:image/gif;base64,R0lGODlhCgALAJECALS0tAAAAP///wAAACH5BAEAAAIALAAAAAAKAAsAAAIblBOmB5AbWnsiynnQRBCv6nUOwoGXw5wPyQYFADs=' />";
$fileiconImage = "<img alt='' src='data:image/gif;base64,R0lGODlhCwANAJECAAAAAP///////wAAACH5BAEAAAIALAAAAAALAA0AAAIchG+iEO0pmGsMxEkRnmY/6XVeIIbgVqInhrRHAQA7' />";
$foldericonImage = "<img alt='' src='data:image/gif;base64,R0lGODlhDwANAKIGAMfHx5eXlwAAAEhISOfn5+/v7////wAAACH5BAEAAAYALAAAAAAPAA0AAAM0aDbMohCOQggA48UVug9Ns1RkWQGBMFhX66Iq+7rpOr+1fMP2fuW+XyzI+xg/D4FyyWQmAAA7' />";

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------

start();

$tmplData = Template::init();


if(strpos($_REQUEST['pathext'],'..') !== false) {
	exit;
}
if(strpos($_REQUEST['delete'],'..') !== false) {
	exit;
}
if(strpos($_REQUEST['delete'],'/') !== false) {
	exit;
}

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
	if($_REQUEST['pathext'] != '') 
    {
        // remove the forward or backwards slash from the path
		$newpath = substr($path.$_REQUEST['pathext'], 0, -1);   
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
		$_REQUEST['pathext'] = substr($_REQUEST['pathext'],0,-1);
		$slashpos = strrpos($_REQUEST['pathext'],'/');
		if($slashpos == 0) 
        {
			$_REQUEST['pathext'] = '';	
		}
        else
        {
			$_REQUEST['pathext'] = substr($_REQUEST['pathext'],0,$slashpos+1);
		}
	}

    //-----------------------------------------------------------------------------
    // ACTION: File edit
    //-----------------------------------------------------------------------------
	if($_GET['edit'] != '') 
    { 
		$fp = fopen($path.$_REQUEST['pathext'].$_GET['edit'], "r");
		$oldcontent = fread($fp, filesize($path.$_REQUEST['pathext'].$_GET['edit']));
		fclose($fp);

        $tmplData['{{pathext}}'] = $_REQUEST['pathext'];
        $tmplData['{{editingFile}}'] = $_GET['edit'];
        $tmplData['{{oldcontent}}'] =  $oldcontent;
        $tmplData['{{u}}'] = $_POST[u];

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
				move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $path.$_REQUEST['pathext'].$_FILES['uploadedfile']['name']);	

				success($tmplData, "Uploaded file '" . $_REQUEST['pathext'].$_FILES['uploadedfile']['name']  . "' successfully.");
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
        
		$fp = fopen($path.$_REQUEST['pathext'].$_POST['savefile'], "w");
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
		if(is_dir($path.$_REQUEST['pathext'].$_GET['delete'])) 
        {    
            $result = rrmdir($path.$_REQUEST['pathext'].$_GET['delete']);
			
            if($result == 0) 
            {
				error($tmplData, "The folder '" . $_REQUEST['pathext'].$_GET['delete'] . "' could not be deleted. The folder must be empty before you can delete it.");
			}
			else
			{
				success($tmplData, "'" . $_REQUEST['pathext'].$_GET['delete'] . "' deleted successfully.");
			}
		}
        else 
        {
			if(file_exists($path.$_REQUEST['pathext'].$_GET['delete'])) 
            {
				if( unlink($path.$_REQUEST['pathext'].$_GET['delete']) )
				{
	                success($tmplData, "'" . $_REQUEST['pathext'].$_GET['delete'] . "' deleted successfully.");
				}
				else
				{
                      error($tmplData, "'" . $_REQUEST['pathext'].$_GET['delete'] . "' could not be deleted.");
				}
			}
			else
			{
				info($tmplData, "Somebody deleted '" . $_REQUEST['pathext'].$_GET['delete'] . "' before I could.");
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
		elseif( file_exists($path.$_REQUEST['pathext'].$_GET['newname']) )
		{
			error($tmplData, "Unable to rename '" . $_GET['rename'] . "'. '" . $_GET['newname'] . "' already exists.");
		}
        else
		{
			$result = @rename ($path.$_REQUEST['pathext'].$_GET['rename'], $path.$_REQUEST['pathext'].$_GET['newname']);
            
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
		elseif( file_exists($path.$_REQUEST['pathext'].$_GET['newname']) )
        {
            error($tmplData, "The directory '" . $_GET['newname'] . "' already exists.");
        }
        else
        {
			if (@exec("unzip -n ". $path.$_REQUEST['pathext'].$_GET['unzip'] ." -d ". $path.$_REQUEST['pathext'].$_GET['newname'])) 
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
		if(strpos($path.$_REQUEST['pathext'].$_POST['dirname'],'//') === false ) 
        {
			$result = @mkdir($path.$_REQUEST['pathext'].$_POST['dirname'], $newdirpermissions);
			
            if($result == 0) 
            {
				error($tmplData, "The folder '" . $_POST['dirname'] . "' could not be created. Make sure the name you entered is a valid folder name.");
			}
			else
            {
				success($tmplData, "Created folder '" . $_REQUEST['pathext'].$_POST['dirname'] . "'");
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
	if($filemanager == '') 
    { 


		// if $makediron has been set to on show some html for making directories
		if($makediron === true) {
			$mkdirhtml = "<span class='wf-label'>Create a Folder: </span> <input type='text' name='dirname' size='15' /><input type='submit' name='mkdir' value='Create' /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  ";
		}

		// build the html that makes up the file manager
		// the $filemanager variable holds the first part of the html
		// including the form tags and the top 2 heading rows of the table which
		// dont display files
		$filemanager = "
		<center>
		<table class='wf' cellspacing='0' cellpadding='20'>
		<tr>
		<td>
		<span class='wf-heading'>$heading</span> <a href='$_SERVER[PHP_SELF]?logoff=1'>Log Off</a><br /><br />
		" . $tmplData['{{msg}}'] . "
		<form name='form1' method='post' action='$_SERVER[PHP_SELF]' enctype='multipart/form-data'>
		<input type='hidden' name='MAX_FILE_SIZE' value='$maxfilesize' />
		$mkdirhtml <span class='wf-label'>Upload File: </span><input type='file' name='uploadedfile' />
		<input type='submit' name='upload' value='Upload' />
		<input type='hidden' name='u' value='$_REQUEST[u]' />
		<input type='hidden' name='pathext' value='$_REQUEST[pathext]' />
		</form>
		<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
		<tr class='wf-heading'> 
		<td width='25'>&nbsp;</td>
		<td><span class='wf-headingrow'>&nbsp;FILENAME&nbsp;</span></td>
		<td><span class='wf-headingrow'>&nbsp;TYPE&nbsp;</span></td>
		<td><span class='wf-headingrow'>&nbsp;SIZE&nbsp;</span></td>
		<td><span class='wf-headingrow'>&nbsp;LAST MODIFIED&nbsp;</span></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		<tr class='wf-line'> 
		<td colspan='9' height='2'></td>
		</tr>";

	// if the current directory is a sub directory show a back link to get back to the previous directory
		if($_REQUEST['pathext'] != '') {
			$filemanager  .= "
			<tr>
			<td class='wf-lightcolumn'>&nbsp;" . $arrowiconImage . "&nbsp;</td>
			<td class='wf-darkcolumn'>&nbsp;<a href='$_SERVER[PHP_SELF]?u=$_REQUEST[u]&amp;back=1&amp;pathext=$_REQUEST[pathext]'>&laquo;BACK</a>&nbsp;</td>
			<td class='wf-lightcolumn'></td>
			<td class='wf-darkcolumn'></td>
			<td class='wf-lightcolumn'></td>
			<td class='wf-darkcolumn'></td>
			<td class='wf-lightcolumn'></td>
			<td class='wf-darkcolumn'></td>
			<td class='wf-lightcolumn'></td>
			</tr>
			<tr class='wf-darkline'> 
			<td colspan='9' height='1'></td>
			</tr>";
		}

		// build the table rows which contain the file information
		$newpath = substr($path.$_REQUEST['pathext'], 0, -1);   // remove the forward or backwards slash from the path
		$dir = @opendir($newpath); // open the directory
		while($file = readdir($dir)) { // loop once for each name in the directory
			$filearray[] = $file;
		}
		natcasesort($filearray);

		foreach($filearray as $key => $file) {

			// check to see if the file is a directory and if it can be opened, if not hide it
			$hiddendir = 0;
			if(is_dir($path.$_REQUEST['pathext'].$file)) {
				$tempdir = @opendir($path.$_REQUEST['pathext'].$file);
				if($tempdir == false) { $hiddendir = 1;}
				@closedir($tempdir);
			}

			// if the name is not a directory and the name is not the name of this program file 
			if($file != '.' && $file != '..' && $file != basename(__FILE__) && $hiddendir != 1) {
				$match = false;
				foreach($hiddenfiles as $name) { // for each value in the hidden files array

					if($file == $name) { // check the name is not the same as the hidden file name
						$match = true;	 // set a flag if this name is supposed to be hidden
					}
				}	

				if($match === false) { // if there were no matches the file should not be hidden

						$filedata = stat($path.$_REQUEST['pathext'].$file); // get some info about the file
						$encodedfile = rawurlencode($file);

						// find out if the file is one that can be edited
						$editlink = '';
						if($editon === true && !is_dir($path.$_REQUEST['pathext'].$file)) { // if the edit function is turned on and the file is not a directory

							$dotpos = strrpos($file,'.');
							foreach($editextensions as $editext) {

								$ext = substr($file, ($dotpos+1));
								if(strcmp( strtolower( $ext ) , $editext) == 0) {
									$editlink = "&nbsp;<a href='$_SERVER[PHP_SELF]?edit=$encodedfile&amp;u=$_REQUEST[u]&amp;pathext=$_REQUEST[pathext]'>EDIT</a>&nbsp;";
								}
							}
						}


						// create some html for a link to download files 
						$downloadlink = "<a href='" . $baseUrl.$_REQUEST[pathext].$encodedfile . "' target='_blank'>VIEW</a>";

						// create some html for a link to delete files 
						$deletelink = "<a href=\"javascript:var c=confirm('Delete \'" . $encodedfile  . "\' ?'); if(c) document.location='$_SERVER[PHP_SELF]?delete=$encodedfile&amp;u=$_REQUEST[u]&amp;pathext=$_REQUEST[pathext]'\">DELETE</a>";

                                                $renamelink = "<a href=\"javascript:var c=prompt('Rename \'" . $encodedfile  . "\' to'); if(c) document.location='$_SERVER[PHP_SELF]?rename=$encodedfile&amp;newname=' + c + '&amp;u=$_REQUEST[u]&amp;pathext=$_REQUEST[pathext]'\">RENAME</a>";

						$unziplink = "<a href=\"javascript:var c=prompt('Unzip \'" . $encodedfile  . "\' to'); if(c) document.location='$_SERVER[PHP_SELF]?unzip=$encodedfile&amp;newname=' + c + '&amp;u=$_REQUEST[u]&amp;pathext=$_REQUEST[pathext]'\">UNZIP</a>";


						// if it is a directory change the file name to a directory link
						if(is_dir($path.$_REQUEST['pathext'].$file)) {
							$filename = "<a href='$_SERVER[PHP_SELF]?u=$_REQUEST[u]&amp;pathext=$_REQUEST[pathext]$encodedfile/'>$file</a>";
							$fileicon = "&nbsp;" . $foldericonImage . "&nbsp;";
							$downloadlink = '';
							$filedata[7] = '';
							$modified = '';
							$filetype = '';
							if($deletediron === false) {
								$deletelink = '';
							}
							$tmpeditlink = "";
						} else {
							$filename = $file;
							$fileicon = "&nbsp;" . $fileiconImage . "&nbsp;";

							$pathparts = pathinfo($file);
							$filetype = $type[ strtolower($pathparts['extension']) ];

							$tmpeditlink = $editlink;

                                                	if( strtolower( $pathparts['extension'] ) == 'zip') {
                                                        	$tmpeditlink = $unziplink;
                                                	}

							$modified = date('d-M-y g:ia',$filedata[9]);


							if($filedata[7] > 1024) {
								$filedata[7] = round($filedata[7]/1024);
								if($filedata[7] > 1024) {
									$filedata[7] = round($filedata[7]/1024);
									if($filedata[7] > 1024) {
										$filedata[7] = round($filedata[7]/1024,1);
										if($filedata[7] > 1024) {
											$filedata[7] = round($filedata[7]/1024);
												$filedata[7] = $filedata[7].'Tb';
										} else {
											$filedata[7] = $filedata[7].'Gb';
										}
									} else {
										$filedata[7] = $filedata[7].'Mb';
									}
								} else {
									$filedata[7] = $filedata[7].'Kb';
								}
							} else {
								$filedata[7] = $filedata[7].'b';
							}
						}

						// append 2 table rows to the $content variable, the first row has the file
						// informtation, the 2nd row makes a black line 1 pixel high

						$content .= "
						<tr>
						<td class='wf-lightcolumn'>$fileicon</td>
						<td class='wf-darkcolumn'>&nbsp;<span class='wt-text'>$filename</span>&nbsp;</td>
						<td class='wf-lightcolumn'>&nbsp;<span class='wt-text'>$filetype</span>&nbsp;</td>
						<td class='wf-darkcolumn'>&nbsp;<span class='wt-text'>$filedata[7]</span>&nbsp;</td>
						<td class='wf-lightcolumn'>&nbsp;<span class='wt-text'>$modified</span>&nbsp;</td>
						<td class='wf-darkcolumn'>&nbsp;$downloadlink&nbsp;</td>
						<td class='wf-lightcolumn'>&nbsp;$deletelink&nbsp;</td>
						<td class='wf-darkcolumn'>&nbsp;$tmpeditlink&nbsp;</td>
						<td class='wf-lightcolumn'>&nbsp;$renamelink&nbsp;</td>
						</tr>
						<tr class='wf-darkline'> 
						<td colspan='9' height='1'></td>
						</tr>";
				}
			}
		}
		closedir($dir); // now that all the rows have been built close the directory
		$content .= "</table></td></tr></table></center>"; // add some closing tags to the $content variable
		$filemanager  .= $content; // append the html to the $filemanager variable
	}
    
    $tmplData['{{content}}'] = $filemanager;
    renderMain($tmplData);

}

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------

function start()
{
    global $ini;
    
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
    
    if(!($_POST['u'] == $ini['userName'] && $_POST['password'] == $ini['password'])) 
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
    $tmplData['{{msg}}'] = "<span class='wf-error'>" . $msg . "</span><br /><br />";
}

function success(&$tmplData, $msg)
{
    //echo "OK: " . $msg;
    $tmplData['{{msg}}'] = "<span class='wf-success'>" . $msg . "</span><br /><br />";
}

function info(&$tmplData, $msg)
{
    //echo "INFO: " . $msg;
    $tmplData['{{msg}}'] = "<span class='wf-info'>" . $msg . "</span><br /><br />";
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
    
    //$tmplData['{{content}}'] = Template::render($ini['mainTemplate'], $tmplData);
    Template::display($ini['layoutTemplate'], $tmplData);    
}

//-----------------------------------------------------------------------------

class Template
{
    static public function init()
    {
        global $ini, $_SERVER;
    
        $tmplData = array();
        $tmplData['{{heading}}'] = $ini['heading'];
        $tmplData['{{action}}'] = $_SERVER[PHP_SELF];
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



