<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminSearchBundle\ProxyQuery;

use Elastica\Search;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class ProxyQuery implements ProxyQueryInterface
{

    public $smartProxyQuery; //Assume the default one is based on elasticsearch
    public $originalProxyQuery; // For each admin, keep a reference to the original datagrid builder
    public $smart = true;
    
    public function __construct(ElasticaProxyQuery $smartProxyQuery,ProxyQueryInterface $originalProxyQuery)
    {
    	$this->smartProxyQuery    = $smartProxyQuery;
    	$this->originalProxyQuery = $originalProxyQuery;
    	
    }
    
    
    public function getQuery()
    {
    	if ($this->smart) {
    		return $this->smartProxyQuery;
    	}
    	return $this->originalProxyQuery;
    }
    
    
    public function getPaginator()
    {
    	if ($this->smart) {
    	 	return $this->smartProxyQuery->getPaginator();
    	}
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
    	if ($this->smart) {
    		$paginator = $this->smartProxyQuery->execute($params, $hydrationMode);
    		$results = $paginator->getResults(
				$this->smartProxyQuery->getFirstResult(), 
				$this->smartProxyQuery->getMaxResults()
    		);
    		
    		return $results;
    	}
    	return $this->originalProxyQuery->execute($params, $hydrationMode);
    }

    
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
		$this->getQuery()->setSortBy($parentAssociationMappings, $fieldMapping);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortBy()
    {
        return $this->getQuery()->getSortBy();
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        $this->getQuery()->setSortOrder($sortOrder);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
    	return $this->getQuery()->getSortOrder();
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->getQuery()->setFirstResult($firstResult);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->getQuery()->getFirstResult();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->getQuery()->setMaxResults($maxResults);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->getQuery()->getMaxResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->getQuery()->getResults();
    }

    public function getSingleScalarResult()
    {
        // TODO
    }

    public function getUniqueParameterId()
    {
        // TODO
    }

    public function entityJoin(array $associationMappings)
    {
        // TODO
    }

    public function addMust($args)
    {
    	$this->smartProxyQuery->addMust($args);
    }

    public function addMustNot($args)
    {
      $this->smartProxyQuery->addMustNot($args);
    }

    public function addShould($args)
    {
      $this->smartProxyQuery->addShould($args);
    }

    public function getQueryBuilder() 
    {
    	$this->smart = false;
    	return $this->originalProxyQuery->getQueryBuilder();
    }
    
    public function toArray()
    {
        $queryReflection = new \ReflectionClass($this->smartProxyQuery);
        $queryProperty   = $queryReflection->getProperty('query');
        
        $queryProperty->setAccessible(true);
        return $queryProperty->getValue($this->smartProxyQuery)->toArray();
        
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $args)
    {
    	$this->smart = false;
    }
}
