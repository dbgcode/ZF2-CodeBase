<?php
namespace AppBase\Filter\File\Resize\Adapter;

abstract class AbstractAdapter
{
    abstract public function resize($width, $height, $keepRatio, $file, $target, $keepSmaller = true);
 
    protected function _calculateWidth($oldWidth, $oldHeight, $width, $height)
    {
        // now we need the resize factor
        // use the bigger one of both and apply them on both
        $factor = max(($oldWidth/$width), ($oldHeight/$height));
        return array($oldWidth/$factor, $oldHeight/$factor);
    }
}
