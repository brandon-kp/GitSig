<?php
class Github_signature {

	var $username;
	var $image       = 'res/sigtemp.png';				//template image
	var $font        = 'res/HelveticaLTStd-Bold.otf';	//font file
	var $uri_segment = 3;								//This depends on how many sub-folders the script is in
	
	function get_username()
	{
		$username       = explode('/',$_SERVER['PHP_SELF']);
		$username       = explode('.',$username[$this->uri_segment]);
		return $this->username = $username[0];
	}
	
	function load_json()
	{
		$json = file_get_contents('https://api.github.com/users/'.$this->get_username());
		$json = json_decode($json, true);
		return $json;
	}
	
	function public_repos()
	{
		$json = $this->load_json();
		return $json['public_repos'];
	}
	
	function private_repos()
	{
		$json = $this->load_json();
		
		if(array_key_exists($json,'private_repos'))
		{
			return $json['private_repos'];
		}
		else
		{
			return '0';
		}
	}
	
	public function followers()
	{
		$json = $this->load_json();
		return $json['followers'];
	}
	
	public function website()
	{
		$json = $this->load_json();
		return $json['blog'];
	}
	
	function add_avatar($im)
	{
		$json = $this->load_json();
		$avatar = $json['avatar_url'];
		$headers = get_headers($avatar);
		$content_type = $headers[3];
		
		if($content_type == 'Content-Type: image/jpeg' || $content_type == 'Content-Type: image/jpg')
		{
			$avatar = imagecreatefromjpeg($avatar);
		}
		elseif($content_type == 'Content-Type: image/gif')
		{
			$avatar = imagecreatefromgif($avatar);
		}
		elseif($content_type == 'Content-Type: image/png')
		{
			$avatar = imagecreatefrompng($avatar);
		}
		
		imagecopymerge($im, $avatar, 7, 6, 0, 0, 80, 80, 100);
	}
	
	function init()
	{	
		$im         = imagecreatefrompng($this->image);
		$name_gray  = imagecolorallocate($im,73,89,97);
		$stat_black = imagecolorallocate($im,0,0,0);
		
		$blog_dimensions = imagettfbbox(15,0,$this->font, $this->website());
		$website_x = abs($blog_dimensions[4]-$blog_dimensions[0]);
		$x = imagesx($im)-$website_x-8;
		
		//username
		imagettftext($im, 21, 0, 102, 29, $name_gray, $this->font, $this->get_username());
		
		//public repo count
		imagettftext($im, 28, 0, 102, 61, $stat_black, $this->font, $this->public_repos());
		
		//private repo count
		imagettftext($im, 28, 0, 200, 61, $stat_black, $this->font, $this->private_repos());
		
		//follower count
		imagettftext($im, 28, 0, 291, 61, $stat_black, $this->font, $this->followers());
		
		//github URL
		imagettftext($im, 15, 0, 7, 120, $name_gray, $this->font, 'github.com/'.$this->get_username());
		
		//blog URL
		imagettftext($im, 15, 0, $x, 120, $name_gray, $this->font, $this->website()); 
		
		$this->add_avatar($im);
		
		header('Content-Type: image/png');

		imagepng($im);
		imagedestroy($im);
	}

}
