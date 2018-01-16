<?php

class Boraso_DateBasedDiscountRules_Model_Catalogrules
{
    protected $arrRules         = null;
    protected $dbr              = null;


    public function __construct()
    {
        //db init
        $dbres                  = Mage::getSingleton('core/resource');
        $this->dbr              = $dbres->getConnection('core_read');

        $this->loadRules();
    }


    public function loadRules()
    {
        $sql                    = "
                                    SELECT 
                                        rule_id AS id, name, is_active
                                    FROM
                                        catalogrule
                                    WHERE
                                        conditions_serialized LIKE '%\_to\_date%'
                                    ORDER BY
                                        is_active ASC, name ASC
                                ";

        $this->arrRules         = $this->dbr->fetchAll($sql, \PDO::FETCH_ASSOC);
    }


    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arrOptionArray = array();

        foreach($this->arrRules as $item) {

            $inactiveWarn       = empty($item["is_active"]) ? '[Inactive]' : '';
            $arrOptionArray[]   = array(
                                    "value"     => $item["id"],
                                    "label"     => $item["name"] . " " . $inactiveWarn
                                );
        }

        return $arrOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arrToOptionArray       = $this->toOptionArray();
        $arrToArray             = array();

        foreach($arrToOptionArray as $item) {

            $value              = $item["value"];
            $arrToArray[$value] = $item["label"];
        }

        return $arrToArray;
    }


    public function getSelectedRuleIds()
    {
        $string         = Mage::getStoreConfig('boraso_datebaseddiscountrules/settings/enabled_for_catalogrules_list');

        if(empty($string)) {

            return array();
        }

        $arrSections    = explode(",", $string);
        return $arrSections;
    }


    public function getSelectedRules()
    {
        $arrSelectedRuleIds     = $this->getSelectedRuleIds();

        if( empty($arrSelectedRuleIds) ) {

            return $arrSelectedRuleIds;
        }


        $arrToArray             = $this->toArray();
        $arrSelected            = array();

        foreach($arrToArray as $rule_id => $rule_name) {

            if( in_array($rule_id, $arrSelectedRuleIds) ) {

                $arrSelected[$rule_id]  = $rule_name;
            }
        }

        return $arrSelected;
    }
}
