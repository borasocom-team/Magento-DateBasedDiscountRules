<?php

class Boraso_DateBasedDiscountRules_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $todayDateForDB;

    protected $sqlUpdateRuleCondition   = "
                                            UPDATE 
                                              catalogrule 
                                            SET
                                              conditions_serialized = :conditions_serialized 
                                            WHERE 
                                              rule_id               = :rule_id
                                        ";

    protected $stmtUpdateRuleCondition;
    protected $dbw;

    public function __construct()
    {
        $this->todayDateForDB           = date("Y-m-d");
        $this->dbw                      = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->stmtUpdateRuleCondition  = $this->dbw->prepare($this->sqlUpdateRuleCondition);
    }

    public function updateCatalogRules()
    {
        $this->log("##### SECTION: updateCatalogRules() #####", "=");

        $arrAllRules = Mage::getModel("catalogrule/rule")->getCollection();
        $modRulesModel = Mage::getModel("boraso_datebaseddiscountrules/catalogrules");

        $arrCompatibleRules = $modRulesModel->toArray();
        $arrSelectedRules   = $modRulesModel->getSelectedRules();
        $arrReport = array('deleted' => array(), 'kept' => array(), 'skipped' => array(), "err" => array());

        foreach ($arrAllRules as $rule) {

            $rule_id = $rule->getId();
            $rule_name = $rule->getName();

            $this->log("### Processing a catalogrule", "-");
            $this->log("#" . $rule_name . "# [" . $rule_id . "]");

            if ( !array_key_exists($rule_id, $arrCompatibleRules) ) {

                $this->log("This rule is missing a condition on the _to_date, thus is not compatible. Skipping.");
                $arrReport['skipped'][] = $rule_name;
                continue;
            }

            if ( !array_key_exists($rule_id, $arrSelectedRules) ) {

                $this->log("This rule is compatible, but it's not selected for processing in the module admin section. Skipping.");
                $arrReport['skipped'][] = $rule_name;
                continue;
            }


            $this->log("Rule is selected - Processing..");
            $esito = $this->updateCatalogRule($rule);

            if (empty($esito)) {

                $this->log("Success: rule updated");
                $arrReport['deleted'][] = $rule_name;

            } else {

                $this->log("Warning: rule NOT updated");
                $this->log($esito);
                $arrReport['err'][] = $rule_name;
            }
        }

        $this->log("### Report", "*");

        foreach ($arrReport as $key => $val) {

            $this->log(ucfirst($key) . ": " . count($val));
        }

        return $arrReport;
    }



    protected function updateCatalogRule(Mage_CatalogRule_Model_Rule $rule)
    {
        $txtConditionsSerialized    = $rule->getConditionsSerialized();
        $arrConditions              = unserialize($txtConditionsSerialized);

        if(!is_array($arrConditions) || empty($arrConditions["conditions"])) {

            $message = "!!! CRITICAL ERROR - Unable to unserialize conditions ###" . $txtConditionsSerialized . "###";
            return $message;
        }


        $updateNeeded = false;
        foreach($arrConditions["conditions"] as &$condition) {

            if(
                !empty($condition["attribute"]) &&
                preg_match("/[a-z]+_(from|to)_date$/i", $condition["attribute"]) &&
                $condition["value"] != $this->todayDateForDB
            ){
                $condition["value"] = $this->todayDateForDB;
                $condition["Boraso_DateBasedDiscountRules"] = "This rule was modified by Boraso/DateBasedDiscountRules (this key is FYI, not a technical need)";
                $updateNeeded       = true;
            }
        }


        if($updateNeeded === false) {

            return null;
        }

        $arrPram    = array(
                        "conditions_serialized" => serialize($arrConditions),
                        "rule_id"               => $rule->getId()
                    );

        try {

            $this->stmtUpdateRuleCondition->execute($arrPram);

        } catch (Exception $ex) {

            $message = "!!! CRITICAL ERROR - Exception, unable to update conditions: " . $ex->getMessage();
            return $message;
        }

        return null;
    }


    protected function log($message, $titleSeparator = false)
    {
        $filename = "boraso_datebaseddiscountrules.log";

        if($titleSeparator) {

            Mage::log("", null, $filename);
        }


        Mage::log($message, null, $filename);

        if($titleSeparator) {

            Mage::log( str_repeat($titleSeparator, mb_strlen($message)), null, $filename );
        }
    }



    public function _prepareDatetimeValue($value, $object, $origin)
    {
        $attribute = $object->getResource()->getAttribute($origin->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $origin->setValue(strtotime($origin->getValue()));
            if (is_scalar($value)) {
                $value = strtotime($value);
            }
        }
        return $value;
    }
}
