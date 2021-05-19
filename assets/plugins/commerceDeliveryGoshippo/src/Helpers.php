<?php


namespace CommerceDeliveryGoshippo;


class Helpers
{
    public static function getTVValue($field,$docId){
        global $modx;
        $tv = $modx->getTemplateVar($field, '*', $docId);
        return ($tv['value'] != '') ? $tv['value'] : $tv['defaultText'];
    }
}