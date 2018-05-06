<?php
function amfm_link($path){
    if($path){
        return '/' . config('amfm.prefix') . '/' . $path;
    }
    return '';
}