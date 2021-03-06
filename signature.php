<?php
if(!isset($_GET["name"]) || !isset($_GET["tid"]))
{
	echo "kthnxbai.";
	exit();
}
$name = $_GET["name"];
$t = $_GET["tid"];
$stats = json_decode(file_get_contents('JSON_API_URL'.$name), true);
if($stats == NULL)
{
	echo "Wrong User id/name!";
	exit();
}
function imagettftextoutline(&$im,$size,$angle,$x,$y,&$col,
            &$outlinecol,$fontfile,$text,$width) {
    // For every X pixel to the left and the right
    for ($xc=$x-abs($width);$xc<=$x+abs($width);$xc++) {
        // For every Y pixel to the top and the bottom
        for ($yc=$y-abs($width);$yc<=$y+abs($width);$yc++) {
            // Draw the text in the outline color
            $text1 = imagettftext($im,$size,$angle,$xc,$yc,$outlinecol,$fontfile,$text);
        }
    }
    // Draw the main text
    $text2 = imagettftext($im,$size,$angle,$x,$y,$col,$fontfile,$text);
}

$file = fopen('templates/'.$t.'.json', "r") or die("Wrong template id!");
$info = fread($file, filesize('templates/'.$t.'.json'));
fclose($file);
$info = json_decode($info, true);
$im = imagecreatetruecolor($info[3]['width'], $info[3]['height']);
switch($info[2]['type'])
{
	case 0:
	{
		
		$black = imagecolorallocate($im, 0, 0, 0);
		imagecolortransparent($im, $black);
		break;
	}
	case 1:
	{
		
		$rgb = explode(',', $info[2]['back']);
		$col = imagecolorallocate($im, intval($rgb[0]), intval($rgb[1]), intval($rgb[2]));
		imagefill($im, 0, 0, $col);
		break;
	}
	case 2:
	{
		
		$bb = -1;
		$choice = explode('.', $info[2]['back']);
		$choice = strtolower(array_pop($choice));
		switch ($choice) 
		{
			case 'jpeg':
			case 'jpg':
				$bb = imagecreatefromjpeg($info[2]['back']);
				break;

			case 'png':
				$bb = imagecreatefrompng($info[2]['back']);
				break;

			case 'gif':
				$bb = imagecreatefromgif($info[2]['back']);
			default:die('Background Image type not supported');
		}
		imagecopyresized($im, $bb, 0, 0, 0, 0, $info[3]['width'], $info[3]['height'], imagesx($bb), imagesy($bb));
		imagedestroy($bb);	
	}
}

for($i = 0; $i < sizeof($info[4]); $i++)
{
	$tmp = -1;
	$choice = explode('.', $info[4][$i]['url']);
	$choice = strtolower(array_pop($choice));
	switch ($choice) 
	{
		case 'jpeg':
		case 'jpg':
			$tmp = imagecreatefromjpeg($info[4][$i]['url']);
			break;

		case 'png':
			$tmp = imagecreatefrompng($info[4][$i]['url']);
			break;

		case 'gif':
			$tmp = imagecreatefromgif($info[4][$i]['url']);
			break;
		
		default:die('A image which was inserted is not supported');
	}
	imagecopyresampled($im, $tmp, $info[4][$i]['x'], $info[4][$i]['y'], 0, 0, $info[4][$i]['width'], $info[4][$i]['height'], imagesx($tmp), imagesy($tmp));
	imagedestroy($tmp);	
}

for($i = 0; $i < sizeof($info[0]); $i++)
{	
	$rgb = explode(',', $info[0][$i]['color']);	
	$fcol = imagecolorallocate($im, intval($rgb[0]), intval($rgb[1]), intval($rgb[2]));
	$bbox = imagettfbbox ( $info[0][$i]['size'], 0.0 , 'gd_fonts/'.$info[0][$i]['font'].'.ttf' , $info[0][$i]['text'] );
	$y_offset = abs($bbox[7] - $bbox[1]);
	if($info[0][$i]['outline'] == 'none')imagettftext($im, $info[0][$i]['size'], 0.0, $info[0][$i]['x'], $info[0][$i]['y']+$y_offset, $fcol, 'gd_fonts/'.$info[0][$i]['font'].'.ttf', $info[0][$i]['text']);
	else
	{
		$rgb = explode(',', $info[0][$i]['outline']);
		$ocol = imagecolorallocate($im, intval($rgb[0]), intval($rgb[1]), intval($rgb[2]));
		imagettftextoutline(
        $im,
        $info[0][$i]['size'],            // font size
        0.0,             // angle in °
        $info[0][$i]['x'],             // x
        $info[0][$i]['y']+$y_offset,            // y
        $fcol,//font color
        $ocol,//outline color
        'gd_fonts/'.$info[0][$i]['font'].'.ttf',
        $info[0][$i]['text'],       // pattern
        1              // outline width
		);
		
	}
}

//Stats :
for($i = 0; $i < sizeof($info[1]); $i++)
{	
	$rgb = explode(',', $info[1][$i]['color']);		
	$fcol = imagecolorallocate($im, intval($rgb[0]), intval($rgb[1]), intval($rgb[2]));
	$bbox = imagettfbbox ( $info[1][$i]['size'], 0.0 , 'gd_fonts/'.$info[1][$i]['font'].'.ttf' , $info[1][$i]['text'] );
	$y_offset = abs($bbox[7] - $bbox[1]) ;
	$txt = '';
	switch($info[1][$i]['text'])
	{
		case 'Name':
		{
			$txt = $name;
			break;
		}
		case 'Score':
		{
			$txt = $stats["points"];
			break;
		}
		case 'Money':
		{
			$txt = '$'.number_format($stats["cash"]);
			break;
		}
		case 'OnlineTime':
		{
			$txt = round($stats["timePlayed"]/3600, 2);
			break;
		}
		case 'KDR':
		{
			$txt = round($stats["kills"]/$stats["deaths"], 2);
			break;
		}
		case 'Reactions':
		{
			$txt = $stats["reactionTestWin"];
			break;
		}
	}
	if($info[1][$i]['outline'] == 'none')imagettftext($im, $info[1][$i]['size'], 0.0, $info[1][$i]['x'], $info[1][$i]['y']+$y_offset, $fcol, 'gd_fonts/'.$info[1][$i]['font'].'.ttf', $txt);
	else
	{
		$rgb = explode(',', $info[1][$i]['outline']);
		$ocol = imagecolorallocate($im, intval($rgb[0]), intval($rgb[1]), intval($rgb[2]));
		imagettftextoutline(
        $im,
        $info[1][$i]['size'],            // font size
        0.0,             // angle in °
        $info[1][$i]['x'],             // x
        $info[1][$i]['y']+$y_offset,            // y
        $fcol,//font color
        $ocol,//outline color
        'gd_fonts/'.$info[1][$i]['font'].'.ttf',
        $txt,		    // pattern
        1              // outline width
		);
		
	}
}

header('Content-type: image/png');
imagepng($im);
imagedestroy($im);
?>

