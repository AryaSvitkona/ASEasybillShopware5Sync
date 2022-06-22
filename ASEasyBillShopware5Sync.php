<?php

namespace ASEasyBillShopware5Sync;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;

class ASEasyBillShopware5Sync extends Plugin
{

	public function install(InstallContext $context)
	{
		$this->createAttributes();
		parent::install($context);
	}

	public function update(UpdateContext $context)
	{
	    $this->createAttributes();
	    parent::update($context);
	}

	public function uninstall(UninstallContext $context)
	{
		if(!$context->keepUserData()) {
			$this->deleteAttributes();
		}
		parent::uninstall($context);
	}


	public static function getSubscribedEvents() : array
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Article' => 'onPostDispatchArticle'
        ];

    }

    public function onPostDispatchArticle(\Enlight_Controller_ActionEventArgs $args)
    { 
    if ( $args->getRequest()->getActionName() === 'save' )
        { 
            // hier den Code einfügen der nach dem // Speichern ausgeführt werden soll 
        }
    }



    private function createAttributes()
    {
        $service = Shopware()->Container()->get( 'shopware_attribute.crud_service' );

        $service->update('s_articles_attributes', 'easybillArticleId', 'integer', [
            'label' => 'EasybillArtikelID',
            'supportText' => 'Artikel ID für den Abgleich mit Easybill',
            'displayInBackend' => false,
            'position' => 100,
            'custom' => false,
        ]);

        $this->regenerateModels();
    }

    private function deleteAttributes()
    {
        $service = Shopware()->Container()->get( 'shopware_attribute.crud_service' );

        try {
            $service->delete('s_articles_attributes', 'easybillArticleId');
        } catch (Exception $e) {}

        $this->regenerateModels();
    }

    private function regenerateModels()
    {
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels( [
            's_articles_attributes',
        ] );
    }

}