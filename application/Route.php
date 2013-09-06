<?php

class Route
{
    
    static public function init()
    {
        //echo "Routes::init()";
                
        global $_REQUEST, $_GET, $ini;

        if(strpos($_REQUEST['workingdir'],'..') !== false) 
        {
            return false;
        }
        if(strpos($_REQUEST['delete'],'..') !== false) 
        {
            return false;
        }
        if(strpos($_REQUEST['delete'],'/') !== false) 
        {
	        return false;
        }
        if(strpos($_GET['edit'],'..') !== false) 
        {
            return false;
        }
    
        session_start();
            
        $timezone = $ini['timezone'];
        if($timezone != '') {
            putenv("TZ=$timezone"); 
        }

        return true;

    }
    
    static public function main($tmplData)
    {
        global $_REQUEST, $_GET, $_POST, $ini;
        
        //-----------------------------------------------------------------------------
        // Configuration variables
        //-----------------------------------------------------------------------------
        
        $newdirpermissions = 0700;
        
        // add the extensions of file types that you would like to be able to edit
        $editextensions = $ini['editableFileExtensions']; 
        
        // add any file names to this array which should remain invisible
        $hiddenfiles = $ini['hiddenFiles'];
        
        // make this = false if you dont want the to use the edit function at all
        $editon = ($ini['editFiles'] == 1);
        
        // make this = false if you dont want to be able to make directories
        $makediron = ($ini['makeDir'] == 1);
        
        // make this = false if you dont want to be able to make directories
        $deletediron = ($ini['deleteDir'] == 1);
        
        // directory path, must end with a '/'
        $path = $ini['basePath'];
        
        $type = $ini['fileTypes'];
        
        $fileiconCSS = $ini['fileIconCSS'];
        $foldericonCSS = $ini['folderIconCSS'];
        
        $hideCSS = $ini['hideCSS'];
        $showCSS = $ini['showCSS'];

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
    	//echo "setup.";
    
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
    	//echo "back link.";
    
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
    
            Render::edit($tmplData);
    	}
    	//echo "edit.";
    
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
    
    				Render::success($tmplData, "Uploaded file '" . $_REQUEST['workingdir'].$_FILES['uploadedfile']['name']  . "' successfully.");
    			}
                else
                {
    				$maxkb = round($maxfilesize/1000); // show the max file size in Kb
    				Render::error($tmplData, "The file was greater than the maximum allowed file size of $maxkb Kb and could not be uploaded.");
    			}
    		} 
            else 
            {
    			Render::info($tmplData, "Please press the browse button and select a file to upload before you press the upload button.");
    		}
            
    	}
    	//echo "upload.";
    
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
            
            Render::success($tmplData, "'" . $_POST['savefile'] . "' saved successfully.");
    	}
    	//echo "save.";
    
        //-----------------------------------------------------------------------------
        // ACTION: File/folder delete
        //-----------------------------------------------------------------------------
    	if($_GET['delete'] != '') 
        {
    		// delete the file or directory
    		if(is_dir($path.$_REQUEST['workingdir'].$_GET['delete'])) 
            {    
                $result = Util::rrmdir($path.$_REQUEST['workingdir'].$_GET['delete']);
    			
                if($result == 0) 
                {
    				Render::error($tmplData, "The folder '" . $_REQUEST['workingdir'].$_GET['delete'] . "' could not be deleted. The folder must be empty before you can delete it.");
    			}
    			else
    			{
    				Render::success($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' deleted successfully.");
    			}
    		}
            else 
            {
    			if(file_exists($path.$_REQUEST['workingdir'].$_GET['delete'])) 
                {
    				if( unlink($path.$_REQUEST['workingdir'].$_GET['delete']) )
    				{
    	                Render::success($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' deleted successfully.");
    				}
    				else
    				{
                          Render::error($tmplData, "'" . $_REQUEST['workingdir'].$_GET['delete'] . "' could not be deleted.");
    				}
    			}
    			else
    			{
    				Render::info($tmplData, "Somebody deleted '" . $_REQUEST['workingdir'].$_GET['delete'] . "' before I could.");
    			}
    		}
    	}
    	//echo "delete.";
    
        //-----------------------------------------------------------------------------
        // ACTION: File rename
        //-----------------------------------------------------------------------------
    	if($_GET['rename'] != '') 
        {
            if( trim($_GET['newname']) == "" ) 
            {
                Render::error($tmplData, "Unable to rename. Invalid filename '" . $_GET['newname'] . "'.");
            }
    		elseif( file_exists($path.$_REQUEST['workingdir'].$_GET['newname']) )
    		{
    			Render::error($tmplData, "Unable to rename '" . $_GET['rename'] . "'. '" . $_GET['newname'] . "' already exists.");
    		}
            else
    		{
    			$result = @rename ($path.$_REQUEST['workingdir'].$_GET['rename'], $path.$_REQUEST['workingdir'].$_GET['newname']);
                
                if($result == 0) 
    			{
                    Render::error($tmplData, "The folder/file '" . $_GET['rename'] . "' could not be moved/renamed to '" . $_GET['newname'] . "'.");
                }
    			else
    			{
    				Render::success($tmplData, "'" . $_GET['rename'] . "' successfully moved/renamed to '" . $_GET['newname'] . "'.");
    			}
    		}
    	} 
    	//echo "rename.";
    	
        //-----------------------------------------------------------------------------
        // ACTION: Directory Zip
        //-----------------------------------------------------------------------------
        if($_GET['zip'] != '') 
        {
            if( file_exists($path.$_REQUEST['workingdir'].$_GET['zip']) == false ) 
    	    {
    			Render::error($tmplData, "Unable zip '" . $_GET['newname'] . "'. Directory '" . $_GET['newname'] . "' does not exist.");
    		}
    		elseif( file_exists($path.$_REQUEST['workingdir'].$_GET['newname']) )
            {
                Render::error($tmplData, "The file '" . $_GET['newname'] . "' already exists. Please select a new file name.");
            }
            else
            {
    			 if (Util::zipDirectory( $path.$_REQUEST['workingdir'].$_GET['zip'], $path.$_REQUEST['workingdir'].$_GET['newname'] )) 
                {
    				Render::success($tmplData, "'" . $_GET['zip'] . "/' successfully zipped to '" . $_GET['newname'] . "'.");
    			}
                else
                {
    				Render::error($tmplData, "'" . $_GET['zip'] . "/' could not be zipped to '" . $_GET['newname'] . "'.");
    			}
            }
        }
        //echo "zip.";
        
        //-----------------------------------------------------------------------------
        // ACTION: File unzip
        //-----------------------------------------------------------------------------
        if($_GET['unzip'] != '') 
        {
            if( trim($_GET['newname']) == "" ) 
    	    {
    			Render::error($tmplData, "Unable to unzip. Invalid filename '" . $_GET['newname'] . "'.");
    		}
    		elseif( file_exists($path.$_REQUEST['workingdir'].$_GET['newname']) )
            {
                Render::error($tmplData, "The directory '" . $_GET['newname'] . "' already exists.");
            }
            else
            {
    			if (@exec("unzip -n ". $path.$_REQUEST['workingdir'].$_GET['unzip'] ." -d ". $path.$_REQUEST['workingdir'].$_GET['newname'])) 
                {
    				Render::success($tmplData, "'" . $_GET['unzip'] . "' successfully unzipped to '" . $_GET['newname'] . "'.");
    			}
                else
                {
    				Render::error($tmplData, "'" . $_GET['unzip'] . "' could not be unzipped to '" . $_GET['newname'] . "'.");
    			}
            }
        }
        //echo "unzip.";
    
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
    				Render::error($tmplData, "The folder '" . $_POST['dirname'] . "' could not be created. Make sure the name you entered is a valid folder name.");
    			}
    			else
                {
    				Render::success($tmplData, "Created folder '" . $_REQUEST['workingdir'].$_POST['dirname'] . "'");
    			}
    		}
    		else 
            {
    			Render::error($tmplData, "Requested directory name '" . $_POST['dirname'] . "' failed validation.");
    		}
    	}
    	
    	//echo "mkdir.";
    
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
    	
    	//echo "loop.";
    
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
                    $showZip = false;
    				
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
    
                    //echo "checkdir.";
    
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
                        $showZip = true;
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
                    $tmplData['{{showZip}}'] = ($showZip) ? $showCSS : $hideCSS;
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
    				
                    //echo "row.";
    			}
    		}
    	}
    	
        // now that all the rows have been built close the directory
        closedir($dir); 
    
        //-----------------------------------------------------------------------------
        
        $tmplData['{{content}}'] = $content;
        Render::main($tmplData);
        
        //echo "end.";
    }
    
}


?>