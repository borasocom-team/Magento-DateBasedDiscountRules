<?php

class Boraso_DateBasedDiscountRules_Model_Observer
{
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper("boraso_datebaseddiscountrules");
    }


    public function updateCatalogRules()
    {
	    if( Mage::getStoreConfig('boraso_datebaseddiscountrules/settings/modenabled') ) {

		    $this->helper->updateCatalogRules();
        }
    }


    public function _prepareDatetimeValue($observer)
    {
        if( Mage::getStoreConfig('boraso_datebaseddiscountrules/settings/datetime_workaround') ) {

            $value      = $observer->getEvent()->getData("value");
            $object     = $observer->getEvent()->getData("object");
            $origin     = $observer->getEvent()->getData("origin");

            $this->helper->_prepareDatetimeValue($value, $object, $origin);
        }
    }

}
