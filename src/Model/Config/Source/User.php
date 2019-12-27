<?php

namespace Hampe\DemoLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class User implements OptionSourceInterface
{

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(\Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection->getItems() as $item) {
            /** @var $item \Magento\User\Model\User */
            $options[] = [
                'value' => $item->getId(),
                'label' => sprintf('%s [%s]', $item->getName(), $item->getUserName())
            ];
        }

        return $options;
    }
}
