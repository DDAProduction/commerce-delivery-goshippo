<?php
namespace CommerceDeliveryGoshippo\Repositories;


class CountryRepository
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
    /**
     * @var array
     */
    private $showOnlyCountries;

    public function __construct(\DocumentParser $modx,$langCode,$showOnlyCountries = [])
    {
        $this->langCode = $langCode;
        $this->modx = $modx;
        $this->table = $modx->getFullTableName('commerce_delivery_goshippo_countries');

        $this->showOnlyCountries = $showOnlyCountries;
    }


    public function search($phrase){
        $ePhrase = $this->modx->db->escape($phrase);


        $sql  = "select `iso`,`require_state`, `title_$this->langCode` as `title` from $this->table where `title_$this->langCode` like '%$ePhrase%' ";
        if(!empty($this->showOnlyCountries)){
            $sql .= " and `iso` in ('".implode("','",$this->showOnlyCountries)."')";
        }
        $sql .= " order by `title_$this->langCode` asc";

        return $this->modx->db->makeArray($this->modx->db->query($sql));
    }

    public function all(){

        $sql  = "select `iso`,`require_state`, `title_$this->langCode` as `title`  from $this->table";
        if(!empty($this->showOnlyCountries)){
            $sql .= " where `iso` in ('".implode("','",$this->showOnlyCountries)."')";
        }
        $sql .= " order by `title_$this->langCode` asc";

        return $this->modx->db->makeArray($this->modx->db->query($sql),'iso');
    }



    public function getSelectedCountryFromRequest($request= []){

        $countries = $this->all();

        $selectedCountry = null;
        if(isset($request['delivery_goshippo_country']) && array_key_exists($request['delivery_goshippo_country'],$countries)){
            $selectedCountry = $countries[$request['delivery_goshippo_country']];
        }
        else if(count($countries) == 1){
            $selectedCountry = $countries[key($countries)];
        }

        return $selectedCountry;
    }

    public function     requireState($countryIso){
        $eCountryIso = $this->modx->db->escape($countryIso);

        $sql  = "select `require_state`  from $this->table where `iso` = '$eCountryIso'";
        return $this->modx->db->getValue($this->modx->db->query($sql));
    }


    public function get($countryIso){
        $eCountryIso = $this->modx->db->escape($countryIso);

        $sql  = "select `iso`,`require_state`, `title_$this->langCode` as `title`  from $this->table where `iso` = '$eCountryIso'";
        return $this->modx->db->getRow($this->modx->db->query($sql));
    }
}