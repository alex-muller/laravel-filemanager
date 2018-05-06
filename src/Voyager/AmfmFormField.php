<?php namespace AlexMuller\Filemanager\Voyager;

use TCG\Voyager\FormFields\AbstractHandler;

class AmfmImageFormField extends AbstractHandler
{
    protected $codename = 'amfm_image';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
       return view('amfm::formfields.image', [
           'row' => $row,
           'options' => $options,
           'dataType' => $dataType,
           'dataTypeContent' => $dataTypeContent
       ]);
    }
}