<?php

/**
* Author:Stefano Rutishauser
* @link https://github.com/AryaSvitkona
*/

namespace ASEasyBillShopware5Sync\Commmands;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncShopwareArticlesToEasybill extends ShopwareCommand
{

    protected function configure()
    {
        $this
            ->setName('as:sync:articles')
            ->setDescription('sync articles between shopware and easybill');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Start sync articles',
            '========================',
        ]);
        $result = $this->createEasybillArticle();
        $output->write($result['syncedArticles'].' Articles synced');
        $output->writeln([
            '',
            '====',
        ]);
        $output->write($result['errors'].' Errors found');
        $output->writeln([
            '',
            '========================',
        ]);

        return 0;
    }

    public function getSupplierShowAttribute($supplier)
    {

        $connection = $this->container->get('dbal_connection');
        $sql = "SELECT s_articles_supplier_attributes.addSupplierNameToTitle FROM s_articles_supplier_attributes JOIN s_articles_supplier ON s_articles_supplier.id=s_articles_supplier_attributes.supplierID WHERE s_articles_supplier.name='".$supplier."'";
        $data = $connection->fetchAll($sql);

        return $data[0];
    }

    public function authentication()
    {
        
    }

    public function initializeEasybillIds()
    {
        
    }

    public function retrieveEasybillId($article)
    {
        
    }

    public function createEasybillArticle($article)
    {
        $data = '{
          "type": "PRODUCT",
          "number": "123413",
          "description": "Jelena Test",
          "document_note": "It Works",
          "note": null,
          "unit": null,
          "export_identifier": "8400",
          "export_identifier_extended": {
            "NULL": "8400",
            "nStb": "8338",
            "nStbUstID": "8339",
            "nStbNoneUstID": "8950",
            "nStbIm": "8950",
            "revc": "8337",
            "IG": "8125",
            "AL": "8120",
            "sStfr": "8100",
            "smallBusiness": "8195"
          },
          "price_type": "BRUTTO",
          "vat_percent": 7.7,
          "sale_price": 1250,
          "sale_price2": null,
          "sale_price3": null,
          "sale_price4": null,
          "sale_price5": null,
          "sale_price6": null,
          "sale_price7": null,
          "sale_price8": null,
          "sale_price9": null,
          "sale_price10": null,
          "cost_price": 830,
          "export_cost1": null,
          "export_cost2": null,
          "group_id": null,
          "stock": "NO",
          "stock_limit_notify": false,
          "stock_limit_notify_frequency": "ALWAYS",
          "stock_limit": 50,
          "quantity": 10
        }';

        $this->apiCall(POST,$data);
        
    }

    public function updateEasybillArticle($easybillID)
    {
        
    }

    public function deleteEasybillArticle($easybillID)
    {
        
    }

    public function apiCall(string $method, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->bearerToken ));
        curl_setopt($curl, CURLOPT_URL, $this->apiEndPoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
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