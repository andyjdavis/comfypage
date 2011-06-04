<?php
/************************************************
Purpose: Upload file class
*************************************************/
class FileUpload{
	var $diraddition = ''; //Gets added to $uploaddir, helpful when using hosting services
	var $tmpfile; //Temporary Filename
	var $filename; //File Name
	var $originalfilename; //Original File Name
	var $filelocation; //File Location
	var $afiletypes = array(); //File types allowed to be uploaded
	var $abadfiletypes = array(); //Files types not allowed to be uploaded
	var $extension; //Extension type of file
	var $err = ""; //Current Error
	var $maxfilesize = ""; //You can define here as a constant and/or set through SetMaxFileSize function in the class (stored as bytes)
	var $filesize; //File size of uploaded file
	var $overwrite = ""; //overwrite if file already exists

	function FileUpload(){
	}

	function AddGoodFileType($ext){
		$this->afiletypes[] = $ext;
	}

	function AddBadFileType($ext){
		$this->abadfiletypes[] = $ext;
	}

	function SetMaxFileSize($maxsize){
		$this->maxfilesize = $maxsize;
	}
	
	function SetOverwrite($ow){
		$this->overwrite = $ow;
	}

	function UploadFile($input, $uploaddir, $newfilename)
  {
    $this->err = '';
  
		$i = TRUE;
		set_time_limit(600);
		$this->tmpfile = $_FILES[$input]['tmp_name'];
		$this->originalfilename = $_FILES[$input]['name'];
		$this->extension = strtolower(strstr($this->originalfilename, '.'));
		if(count($this->afiletypes) > 0)
    {
			$i = array_search($this->extension, $this->afiletypes);
			if($i === FALSE)
      {
				$this->err .= "File type is not allowed!<br>";
			}
		}
		if(count($this->abadfiletypes) > 0){
			$i = array_search($this->extension, $this->abadfiletypes);
			if($i === TRUE){
				$this->err .= "File type had been blocked!<br>";
			}
		}

		if($newfilename !=null && $newfilename != '')
    {
      $this->filename = $newfilename . $this->extension;
			$this->filelocation = $uploaddir . $newfilename . $this->extension;
		}
    else
    {
      $this->filename = $this->originalfilename;
			$this->filelocation = $uploaddir . $this->originalfilename;
		}
		
    if(file_exists($this->filelocation) && $this->overwrite==false)
    {
			$this->err .= "This filename already exists in " . $uploaddir . " and cannot be overwritten!<br>";
		}
		
    //$srcfile = $diraddition . $uploaddir . $newfilename . $this->extension;
		$srcfile = $this->filelocation;
		
    if($this->err == '')
    {
    			if (is_uploaded_file($this->tmpfile)){
				if (!copy($this->tmpfile,$srcfile)){
					$this->err .= "Error Uploading File!<br>";
				}else{
					$this->filesize = filesize($srcfile);
					if($this->maxfilesize != ''){
						if($this->filesize > $this->maxfilesize){
							$this->err .= "File size is greater than allowable max. file size (" . $this->maxfilesize . " bytes)<br>";
							@unlink($srcfile);
						}
					}
				}
    			}
		}
		@unlink($this->tmpfile);
	}

	function GetExtension(){
		return $this->extension;
	}

	function GetFilename(){
		return $this->filename;
	}

	function GetOriginalFilename(){
		return $this->originalfilename;
	}

	function GetFileLocation(){
		return $this->filelocation;
	}

	function GetFilesize(){
		return $this->filesize;
	}

	function GetFileTypes(){
		return implode(", ", $this->afiletypes);
	}

	function GetBadFileTypes(){
		return implode(", ", $this->abadfiletypes);
	}

	function GetError(){
		return $this->err;
	}
		
}
?>
