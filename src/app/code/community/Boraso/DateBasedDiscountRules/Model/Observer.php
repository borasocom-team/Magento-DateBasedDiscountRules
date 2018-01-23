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

}
