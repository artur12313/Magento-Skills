<?php
    namespace Delivery\Price\Observer;
    use Magento\Framework\Event\Observer as EventObserver;
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\App\ObjectManager;
    use Magento\Framework\Serialize\Serializer\Json;
    
    class CheckoutCartProductAddAfterObserver implements ObserverInterface
    {
    /**
    * @var \Magento\Framework\View\LayoutInterface
    */
    protected $_layout;
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;
    protected $_request;
    
    /**
    * @var Json
    */
    private $serializer;
    
    /**
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\View\LayoutInterface $layout
    */
    public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\View\LayoutInterface $layout,
    \Magento\Framework\App\RequestInterface $request,
    Json $serializer = null
    )
    {
    $this->_layout = $layout;
    $this->_storeManager = $storeManager;
    $this->_request = $request;
    $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }
    /**
    * Add order information into GA block to render on checkout success pages
    *
    * @param EventObserver $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
    /* @var \Magento\Quote\Model\Quote\Item $item */
    $item = $observer->getQuoteItem();
    $additionalOptions = array();
    if ($additionalOption = $item->getOptionByCode('additional_options')){
    $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
    }
    $post = $this->_request->getParam('cloudways');
    if(is_array($post))
    {
    foreach($post as $key => $value)
    {
    if($key == '' || $value == '')
    {
    continue;
    }
    $additionalOptions[] = [
    'label' => $key,
    'value' => $value
    ];
    }
    }
    if(count($additionalOptions) > 0)
    {
    $item->addOption(array(
    'product_id' => $item->getProductId(),
    'code' => 'additional_options',
    'value' => $this->serializer->serialize($additionalOptions)
    ));
    }
    }
    }