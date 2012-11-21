<?
class image {
    /**
     * Load image
     * @static
     * @param string $image
     * @return bool|resource
     */
    static function load($image) {
        if (file_exists($image)) {
            $t = getimagesize($image);
            if ($t["mime"] == "image/jpg" || $t["mime"] == "image/jpeg") {
                $i = imagecreatefromjpeg($image);
            } elseif ($t["mime"] == "image/png") {
                $i = imagecreatefrompng($image);
            }
            elseif ($t["mime"] == "image/gif") {
                $i = imagecreatefromgif($image);
            }
            else {
                $i = false;
            }
            return $i;
        }
        return false;
    }

    /**
     * Resize image to fit the dimensions
     *
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return bool|resource
     */
    static function resample($image, $width, $height) {
        $i = image::load($image);
        if (!$i) {
            return false;
        }
        $towidth = $width;
        $toheight = $height;
        $ratio = $towidth / $toheight;
        $inwidth = imagesx($i);
        $inheight = imagesy($i);
        $inratio = $inwidth / $inheight;
        if ($inratio >= $ratio) {
            $scale = $inwidth / $towidth;
        } else {
            $scale = $inheight / $toheight;
        }
        $ni = imagecreatetruecolor($inwidth / $scale, $inheight / $scale);
        imagecopyresampled($ni, $i, 0, 0, 0, 0, $inwidth / $scale, $inheight / $scale, $inwidth, $inheight);
        return $ni;
    }

    /**
     * Crop image to fit the dimensions, does nothing if image is smaller.
     *
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return bool|resource
     */
    static function crop($image, $width, $height) {
        $i = image::load($image);
        if (!$i) {
            return false;
        }
        $inwidth = imagesx($i);
        $inheight = imagesy($i);
        if ($inwidth<=$width && $inheight<=$height) {
            return $i;
        }
        $towidth = $width;
        $toheight = $height;
        $ratio = $towidth / $toheight;
        $inratio = $inwidth / $inheight;
        if ($inratio >= $ratio) {
            $scale = $inwidth / $towidth;
        } else {
            $scale = $inheight / $toheight;
        }
        $ni = imagecreatetruecolor($inwidth / $scale, $inheight / $scale);
        imagecopyresampled($ni, $i, 0, 0, 0, 0, $inwidth / $scale, $inheight / $scale, $inwidth, $inheight);
        return $ni;
    }

    /**
     * Resize image to fixed width and height
     *
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return bool|resource
     */

    static function resize($image, $width, $height) {
        $i = image::load($image);
        if (!$i) {
            return false;
        }
        $towidth = $width;
        $toheight = $height;
        $inwidth = imagesx($i);
        $inheight = imagesy($i);
        $ni = imagecreatetruecolor($towidth, $toheight);
        imagecopyresampled($ni, $i, 0, 0, 0, 0, $towidth, $toheight, $inwidth, $inheight);
        return $ni;
    }

    /**
     * Create image thumbnail
     *
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return bool|resource image
     */
    static function createThumb($image, $width, $height) {
        $rv = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($rv, 255, 255, 255);
        imagefill($rv, 0, 0, $bg);
        $image = image::cropAndResample($image, $width, $height);
        if (!$image) {
            return false;
        }
        $ix = imagesx($image);
        $iy = imagesy($image);
        imagecopy($rv, $image, ($width - $ix) / 2, ($height - $iy) / 2, 0, 0, $ix, $iy);
        return $rv;
    }

    /**
     * @static Resize, crop to keep aspect.
     * @param string $path
     * @param integer $width
     * @param integer $height
     * @return bool|resource
     */
    static function cropAndResample($path, $width, $height) {
        $i=image::load($path);
        if (!$i) {
            return false;
        }
        $towidth = $width;
        $toheight = $height;
        $ni = imagecreatetruecolor($towidth, $toheight);
        $cropx = 0;
        $cropy = 0;
        $ratio = $towidth / $toheight;
        $inwidth = imagesx($i);
        $inheight = imagesy($i);
        $inratio = $inwidth / $inheight;
        if ($inratio >= $ratio) {
            $scale = $inheight / $toheight;
            $cropx = ($inwidth / $scale - $towidth) / 2;
        } else {
            $scale = $inwidth / $towidth;
            $cropy = ($inheight / $scale - $toheight) / 2;
        }
        if ($cropx > 0 || $cropy > 0) {
            $t = imagecreatetruecolor($inwidth / $scale, $inheight / $scale);
            imagecopyresampled($t, $i, 0, 0, 0, 0, $inwidth / $scale, $inheight / $scale, $inwidth, $inheight);
            imagecopy($ni, $t, 0, 0, $cropx, $cropy, $towidth, $toheight);
            imagedestroy($t);
        } else imagecopyresampled($ni, $i, 0, 0, 0, 0, $towidth, $toheight, $inwidth, $inheight);
        return $ni;
    }

    /**
     * @static Prepare product image
     * @param integer $id
     * @param integer $n
     * @param integer $x
     * @param integer $y
     * @param bool $force
     * @return string
     */
    static function prepareProductImage($id, $n, $x, $y, $force = false) {
        //debug_print_backtrace();
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/prodimages/$id") || !file_exists($_SERVER['DOCUMENT_ROOT'] . "/prodimages/$id/$n.jpg") || $n == 0) {
            return "http://placehold.it/{$x}x$y.png";
        }
        if (!file_exists("prodimages/$id/thumbs")) {
            mkdir("prodimages/$id/thumbs");
        }
        $fn = "prodimages/$id/thumbs/{$n}_{$x}_{$y}.jpg";
        if ($force || !file_exists($fn)) {
            imagejpeg(image::createThumb("prodimages/$id/$n.jpg", $x, $y), $fn, 80);
        }
        return "/$fn";
    }

    /**
     * Add watermark to image
     *
     * @param resource image $image
     * @param string $watermark
     * @param array $args
     * @return image
     */
    static function addWatermark($image, $watermark, $args = array()) {
        $defaults = array(
            'position' => 'bottom center',
            'scale' => .8,
            'opacity' => 20
        );
        $args = array_merge($defaults, $args);
        $width = imagesx($image);
        $height = imagesy($image);
        $position = explode(' ', $args['position']);
        $scale = $args['scale'];
        $opacity = $args['opacity'];
        if (substr($watermark, -3) == 'jpg') {
            $watermark = imagecreatefromjpeg($watermark);
        } elseif (substr($watermark, -3) == 'png') {
            $watermark = imagecreatefrompng($watermark);
        }
        else {
            return $image;
        }
        if ($scale != 0) {
            $watermark = image::resizeAlpha($watermark, $width * $scale, $height, true);
        }
        $newimage = imagecreatetruecolor($width, $height);
        if ($position[1] == 'center') {
            $destx = $width * ((1 - $scale) / 2);
        } elseif ($position[1] == 'right') {
            $destx = $width - imagesx($watermark) - 10;
        }
        elseif ($position[1] == 'left') {
            $destx = 10;
        }
        else {
            $destx = $position[1];
        }
        if ($position[0] == 'bottom') {
            $desty = $height - imagesy($watermark) - 10;
        } elseif ($position[0] == 'top') {
            $desty = 10;
        }
        else {
            $desty = $position[0];
        }
        imagecopymerge($newimage, $image, 0, 0, 0, 0, $width, $height, 100);
        imagecopymerge($newimage, $watermark, $destx, $desty, 0, 0, imagesx($watermark), imagesy($watermark), $opacity);
        imagesavealpha($newimage, true);
        return $newimage;
    }

    /**
     * Resize a PNG file with transparency to given dimensions
     * and still retain the alpha channel information
     * @author Alex Le - http://www.alexle.net
     * @param resource image $src
     * @param integer $w
     * @param integer $h
     * @param bool $preserveratio
     * @return resource
     */
    private function resizeAlpha(&$src, $w, $h, $preserveratio = false) {
        /* create a new image with the new width and height */
        if ($preserveratio) {
            $ox = imagesx($src);
            $oy = imagesy($src);
            $ratio = $ox / $oy;
            if ($w / $h > $ratio) {
                $w = $h * $ratio;
            } else {
                $h = $oy / ($ox / $w);
            }
        }
        $temp = imagecreatetruecolor($w, $h);

        /* making the new image transparent */
        $background = imagecolorallocate($temp, 0, 0, 0);
        ImageColorTransparent($temp, $background); // make the new temp image all transparent
        imagealphablending($temp, true); // turn off the alpha blending to keep the alpha channel

        /* Resize the PNG file */
        /* use imagecopyresized to gain some performance but loose some quality */
        //imagecopyresized($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        /* use imagecopyresampled if you concern more about the quality */
        imagecopyresampled($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        return $temp;
    }

    static function getThumbPath($product, $n, $x, $y) {
        return '/prodimages/' . $product . '/thumbs/' . $n . '_' . $x . '_' . $y . '.jpg';
    }
	
	static function prepareCategoryImage($category_id, $w, $h, $force = false) {
		if(file_exists("catimages/$category_id.jpg")) {
			$image_res = ("catimages/$category_id.jpg");
			
		}
		else {
			$image_res = ("images/noimage.jpg");
		}
		if(!file_exists("catimages/thumbs/".$category_id."_".$w."_".$h.".jpg") || $force) {
			imagejpeg(image::createThumb2($image_res, $w, $h), "catimages/thumbs/".$category_id."_".$w."_".$h.".jpg", 80);
		}
		return "/catimages/thumbs/".$category_id."_".$w."_".$h.".jpg";
	
	}
	
		static function resampleDeprecated($image, $width, $height) {
		if (file_exists($image)) {
			$t=getimagesize($image);
			if ($t["mime"]=="image/jpg" || $t["mime"]=="image/jpeg") $i=imagecreatefromjpeg($image);
			elseif ($t["mime"]=="image/png") $i=imagecreatefrompng($image);
			elseif ($t["mime"]=="image/gif") $i=imagecreatefromgif($image);
			else return false;
			$towidth=$width;
			$toheight=$height;
			$ratio=$towidth/$toheight;
			$inwidth=$t[0];
			$inheight=$t[1];
			$inratio=$inwidth/$inheight;
			if ($inratio>=$ratio) {
				$scale=$inwidth/$towidth;
			}
			elseif ($inratio<$ratio) {
				$scale=$inheight/$toheight;
			}
			$ni=imagecreatetruecolor($inwidth/$scale, $inheight/$scale);
			imagecopyresampled($ni, $i, 0, 0, 0, 0, $inwidth/$scale, $inheight/$scale, $inwidth, $inheight);
			return $ni;
		}
		else {
			return false;
		}
 }
 /**
  * Create image thumbnail
  *
  * @param string $image
  * @param integer $width
  * @param integer $height
  * @return image
  */
	static function createThumb2($image, $width, $height) {
	  $rv=imagecreatetruecolor($width, $height);
	  $bg=imagecolorallocate($rv, 255, 255, 255);
	  imagefill($rv, 0, 0, $bg);
	  $image=image::resampleDeprecated($image, $width, $height);
	  $ix=imagesx($image);
	  $iy=imagesy($image);
	  imagecopy($rv, $image, ($width-$ix)/2, ($height-$iy)/2, 0, 0, $ix, $iy);
	  return $rv;
	  
	 }
}