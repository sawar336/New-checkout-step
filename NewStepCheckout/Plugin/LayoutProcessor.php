<?php
namespace Custom\NewStepCheckout\Plugin;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Checkout\Block\Checkout\LayoutProcessor as BaseLayoutProcessor;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable;
use Magento\Swatches\ViewModel\Product\Renderer\Configurable as ConfigurableViewModel;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var ConfigurableViewModel
     */
    protected $configurableViewModel;

    /**
     * @var Configurable
     */
    protected $configurableBlock;

    /**
     * @var LayoutFactory
     */
    protected $layout;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * LayoutProcessorPlugin constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param Cart $cartHelper
     * @param FormKey $formKey
     * @param LayoutFactory $layout
     * @param ConfigurableViewModel $configurableViewModel
     * @param Configurable $configurableBlock
     * @param Json $json
     * @param Image $imageHelper
     *
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        Cart $cartHelper,
        FormKey $formKey,
        LayoutFactory $layout,
        ConfigurableViewModel $configurableViewModel,
        Configurable $configurableBlock,
        Json $json,
        Image $imageHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->cartHelper = $cartHelper;
        $this->formKey = $formKey;
        $this->layout = $layout;
        $this->configurableViewModel = $configurableViewModel;
        $this->configurableBlock = $configurableBlock;
        $this->json = $json;
        $this->imageHelper = $imageHelper;
    }

    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * @return Render
     */
    protected function getPriceRender()
    {
        return $this->layout->create()->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }

    /**
     * Get categories products
     *
     * @param $ids
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCategoryProductsList($ids)
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => $ids])
            ->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status',Status::STATUS_ENABLED);

        $products = [];
        foreach ($collection as $product) {
            $productData = [
                "name" => $product->getName(),
                "id" => $product->getId(),
                "sku" => $product->getSku(),
                "image" => $this->imageHelper->init($product, 'category_page_grid')->getUrl(),
                "url" => $product->getProductUrl(),
                "formattedPrice" => $this->getProductPrice($product),
                "addToUrl" => $this->cartHelper->getAddUrl($product),
                "formKey" => $this->formKey->getFormKey()
            ];

            if ($product->getTypeId() == 'configurable') {
                $this->configurableBlock->setProduct($product);
                $productData["swatches"] = [
                    "numberToShow" => $this->configurableBlock->getNumberSwatchesPerProduct(),
                    "jsonConfig" => $this->json->unserialize($this->configurableBlock->getJsonConfig()),
                    "jsonSwatchConfig" => $this->json->unserialize($this->configurableBlock->getJsonSwatchConfig()),
                    "mediaCallback" => $this->configurableBlock->getMediaCallback(),
                    "jsonSwatchImageSizeConfig" => $this->json->unserialize($this->configurableBlock->getJsonSwatchSizeConfig()),
                    "showTooltip" => $this->configurableViewModel->getShowSwatchTooltip()
                ];

                $productData["prices"] = [
                    'priceFormat' => $this->json->unserialize($this->configurableBlock->getPriceFormatJson()),
                    'prices' => $this->json->unserialize($this->configurableBlock->getPricesJson())
                ];
            }

            $products[] = $productData;
        }

        return $products;
    }


    /**
     * @param BaseLayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(BaseLayoutProcessor $subject, array $jsLayout)
    {
        $jsLayout['components']["checkout"]["children"]["steps"]["children"]["products-step"]["products"] = $this->getCategoryProductsList([41]); // 41 is hardcoded id for Checkout Products category

        return $jsLayout;
    }
}
