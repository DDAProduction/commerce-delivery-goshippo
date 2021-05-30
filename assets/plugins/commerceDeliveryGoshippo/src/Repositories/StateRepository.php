<?php
namespace CommerceDeliveryGoshippo\Repositories;


class StateRepository
{
    private $langCode;
    /**
     * @var \DocumentParser
     */
    private $modx;
    /**
     * @var string
     */
    private $table;


    public function __construct(\DocumentParser $modx,$langCode)
    {
        $this->langCode = $langCode;
        $this->modx = $modx;
        $this->table = $modx->getFullTableName('commerce_delivery_goshippo_states');

    }

    public function getCountryStates($countryIso){


        $eCountryIso = $this->modx->db->escape($countryIso);
        $sql  = "select `iso`, `title_$this->langCode` as `title` from $this->table  where `country_iso` = '$eCountryIso' order by `title_$this->langCode` asc";

        return $this->modx->db->makeArray($this->modx->db->query($sql));
    }

    public function getState($stateIso,$countryIso){
        $eCountryIso = $this->modx->db->escape($countryIso);
        $eStateIso = $this->modx->db->escape($stateIso);

        $sql  = "select `iso`, `title_$this->langCode` as `title` from $this->table  where `country_iso` = '$eCountryIso' and `iso` = '$eStateIso' ";

        return $this->modx->db->getRow($this->modx->db->query($sql));
    }

}