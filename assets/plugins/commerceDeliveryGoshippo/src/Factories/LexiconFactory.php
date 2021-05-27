<?php
namespace CommerceDeliveryGoshippo\Factories;


use Helpers\Lexicon;

class LexiconFactory
{
    /**
     * @return Lexicon
     */
    public static function build()
    {
        global $modx;
        $commerce = $modx->commerce;

        $availableLanguages = [
            'english'
        ];

        $lang = in_array($commerce->getCurrentLang(), $availableLanguages) ? $commerce->getCurrentLang() : $availableLanguages[key($availableLanguages)];
        $lexicon = new Lexicon($modx, [
            'lang' => $lang,
            'langDir' => 'assets/plugins/commerceDeliveryGoshippo/lang/'
        ]);
        $lexicon->fromFile('core');
        return $lexicon;
    }
}