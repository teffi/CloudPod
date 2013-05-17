<?php
namespace CloudPod\ClassroomBundle\Twig;

class FormatSizeTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'size' => new \Twig_Filter_Method($this, 'sizeFilter'),
        );
    }


    public function sizeFilter( $bytes )
    {
        $filesize = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $filesize ) -1 ); $bytes /= 1024, $i++ );
                return( round( $bytes, 2 ) . " " . $filesize[$i] );
    }

    public function getName()
    {
        return 'twig_extension';
    }
}