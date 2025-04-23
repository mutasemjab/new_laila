<?php

function uploadImage($folder, $image)
{
  $extension = strtolower($image->extension());
 // $filename = time() . rand(100, 999) . '.' . $extension;
  $filename= $image->getClientOriginalName() ;
  $image->move($folder, $filename);
  return $filename;
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}


function categoryLabel($category=6)
{
   $text        = '';
   $label_color = '';
   if($category == 1){
       $label_color = '#D10000';
       $text        = __('messages.Speaker');
    }elseif ($category == 2) {
       $label_color = '#2E50D6';
       $text        = __('messages.Participant');
    }elseif ($category == 3) {
       $label_color = '#2E9E3B';
       $text        = __('messages.Exhibitor');
    }elseif ($category == 4) {
       $label_color = '#219C29';
       $text        = __('messages.Committee');
    }elseif ($category == 5) {
       $label_color = '#6F437F';
       $text        = __('messages.Press');
    }else {
       $label_color = '#835C3B';//835C3B
       $text        = __('messages.Other');
    }
    return [
        'label_color' => $label_color,
        'text' => $text ,
    ];
}
