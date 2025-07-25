<?php

namespace Intervention\Image\Imagick;

use Intervention\Image\AbstractEncoder;
use Intervention\Image\Exception\NotSupportedException;
use WP_Smart_Image_Resize\Utilities\Env;

class Encoder extends AbstractEncoder
{
    /**
     * Processes and returns encoded image as JPEG string
     *
     * @return string
     */
    protected function processJpeg()
    {
        $format = 'jpeg';
        $compression = \Imagick::COMPRESSION_JPEG;

        $imagick = $this->image->getCore();
        $imagick->setImageBackgroundColor('white');
        $imagick->setBackgroundColor('white');
        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE);
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as PNG string
     *
     * @return string
     */
    protected function processPng()
    {
        $format = 'png';
        $compression = \Imagick::COMPRESSION_ZIP;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as GIF string
     *
     * @return string
     */
    protected function processGif()
    {
        $format = 'gif';
        $compression = \Imagick::COMPRESSION_LZW;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        return $imagick->getImagesBlob();
    }

    protected function processWebp()
    {
        if ( ! \Imagick::queryFormats('WEBP')) {
            throw new NotSupportedException(
                "Webp format is not supported by Imagick installation."
            );
        }

        $format = 'webp';
        $compression = \Imagick::COMPRESSION_JPEG;

        $imagick = $this->image->getCore();

        // Backward compat with Imagick 6.x
        if(version_compare(Env::getImagickVersion(), '7', '<')) {
            $color = maybe_hash_hex_color(strtolower(wp_sir_get_settings()['bg_color']));
            if(empty($color) && is_callable(array($imagick, 'getImageAlphaChannel')) &&  !$imagick->getImageAlphaChannel()){ 
                $color = 'white';
            }
        }
        
        $color = !empty($color) ? new \ImagickPixel($color) : new \ImagickPixel('none');
        $imagick->setImageBackgroundColor($color);

        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    protected function processAvif()
    {
        if ( ! \Imagick::queryFormats('AVIF')) {
            throw new NotSupportedException(
                "Avif format is not supported by Imagick installation."
            );
        }

        $format = 'avif';
        $compression = \Imagick::COMPRESSION_JPEG;

        $imagick = $this->image->getCore();

        // Backward compat with Imagick 6.x
        if(version_compare(Env::getImagickVersion(), '7', '<')) {
            $color = maybe_hash_hex_color(strtolower(wp_sir_get_settings()['bg_color']));
            if(empty($color) && is_callable(array($imagick, 'getImageAlphaChannel')) &&  !$imagick->getImageAlphaChannel()){ 
                $color = 'white';
            }
        }
        
        $color = !empty($color) ? new \ImagickPixel($color) : new \ImagickPixel('none');
        $imagick->setImageBackgroundColor($color);

        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as TIFF string
     *
     * @return string
     */
    protected function processTiff()
    {
        $format = 'tiff';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as BMP string
     *
     * @return string
     */
    protected function processBmp()
    {
        $format = 'bmp';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as ICO string
     *
     * @return string
     */
    protected function processIco()
    {
        $format = 'ico';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as PSD string
     *
     * @return string
     */
    protected function processPsd()
    {
        $format = 'psd';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        return $imagick->getImagesBlob();
    }
}
