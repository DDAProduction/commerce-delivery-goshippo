<?php

namespace CommerceDeliveryGoshippo;

use Helpers\Lexicon;

class Renderer
{

    private $modx;

    protected $block;
    protected $blocks = [];
    protected $extensionLevels = [];
    protected $templateLevels = [];

    /**
     * @var string
     */
    private $lang;
    /**
     * @var Lexicon
     */
    private $lexicon;

    public function __construct($modx, Lexicon $lexicon)
    {
        $this->modx = $modx;

        $this->lang = $lexicon->get('lang_code');
        $this->lexicon = $lexicon;
    }



    public function render($template, array $data = [])
    {
        $this->templateLevels[] = $template;
        $fullTemplate = MODX_BASE_PATH.'assets/plugins/commerceDeliveryGoshippo/templates/' . $template;

        if (!is_readable($fullTemplate)) {
            throw new \Exception('Template "' . $fullTemplate . '" is not readable!');
        }

        global $_style, $_lang, $lastInstallTime, $modx_manager_charset, $modx_lang_attribute;

        $modx  = $this->modx;
        $lang  = $this->lang;
        extract($data);
        setlocale(LC_NUMERIC, 'C');

        ob_start();
        include $fullTemplate;

        if (!empty($this->extensionLevels)) {
            $parent = end($this->extensionLevels);

            if (key($this->extensionLevels) == $template) {
                ob_end_clean();
                array_pop($this->extensionLevels);
                return $this->render($parent, compact(array_keys($data)));
            }
        }

        array_pop($this->templateLevels);
        return $this->lexicon->parse(ob_get_clean());
    }

    public function extend($parent)
    {
        $this->extensionLevels[end($this->templateLevels)] = $parent;
    }

    public function block($name, $default = null)
    {
        if (empty($this->extensionLevels)) {
            return isset($this->blocks[$name]) ? $this->blocks[$name] : $default;
        }

        if ($this->block) {
            throw new \Exception('Block "' . $this->block . '" not closed!');
        }

        $this->block = $name;
        ob_start();
    }

    public function endBlock()
    {
        if (is_null($this->block)) {
            throw new \Exception('Unexpected endBlock: no blocks are opened!');
        }

        $this->blocks[$this->block] = trim(ob_get_clean());
        $this->block = null;

    }
}
