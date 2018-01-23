<?php

class Boraso_DateBasedDiscountRules_Model_Mage_CatalogRule_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    protected function _prepareDatetimeValue($value, $object)
    {
        if( Mage::getStoreConfig('boraso_datebaseddiscountrules/settings/datetime_workaround') ) {

            $attribute = $object->getResource()->getAttribute($this->getAttribute());

            if ($attribute && $attribute->getBackendType() == 'datetime') {
                $this->setValue(strtotime($this->getValue()));
                if (is_scalar($value)) {
                    $value = strtotime($value);
                }
            }

            return $value;

        } else {

            return parent::_prepareDatetimeValue($value, $object);
        }
    }
}
