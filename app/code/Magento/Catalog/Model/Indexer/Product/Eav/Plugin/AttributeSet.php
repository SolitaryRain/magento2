<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;

class AttributeSet
{
	/**
	 * @var bool
	 */
	private $requiresReindex;

	/**
     * @var Processor
     */
    protected $_indexerEavProcessor;

    /**
     * @var AttributeSet\IndexableAttributeFilter
     */
    protected $_attributeFilter;

    /**
     * @param Processor $indexerEavProcessor
     * @param AttributeSet\IndexableAttributeFilter $filter
     */
    public function __construct(Processor $indexerEavProcessor, AttributeSet\IndexableAttributeFilter $filter)
    {
        $this->_indexerEavProcessor = $indexerEavProcessor;
        $this->_attributeFilter = $filter;
    }

    /**
     * Invalidate EAV indexer if attribute set has indexable attributes changes
     *
     * @param Set $subject
     * @param Set $result
     * @return Set
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Set $subject, Set $result)
    {
        if ($this->requiresReindex) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }
        return $result;
    }

	/**
	 * @param Set $subject
	 *
	 * @return bool
	 */
	public function beforeSave(Set $subject) {
		$this->requiresReindex = false;
		if ( $subject->getId() ) {
			$originalSet = clone $subject;
			$originalSet->initFromSkeleton($subject->getId());
			$originalAttributeCodes = array_flip( $this->_attributeFilter->filter( $originalSet ) );
			$subjectAttributeCodes  = array_flip( $this->_attributeFilter->filter( $subject ) );
			$this->requiresReindex  = (bool) count( array_merge(
				array_diff_key( $subjectAttributeCodes, $originalAttributeCodes ),
				array_diff_key( $originalAttributeCodes, $subjectAttributeCodes )
			) );
		}
	}
}
