<?php
class ImageProcessor {
    public static function superimposeImages($baseImage, $overlayImage, $position) {
        $base = imagecreatefromstring(file_get_contents($baseImage));
        $overlay = imagecreatefromstring(file_get_contents($overlayImage));
        
        list($baseWidth, $baseHeight) = getimagesize($baseImage);
        list($overlayWidth, $overlayHeight) = getimagesize($overlayImage);
        
        imagecopy($base, $overlay, 
                 $position['x'], $position['y'],
                 0, 0, $overlayWidth, $overlayHeight);
        
        $output = tempnam(sys_get_temp_dir(), 'img');
        imagepng($base, $output);
        
        imagedestroy($base);
        imagedestroy($overlay);
        
        return $output;
    }
}