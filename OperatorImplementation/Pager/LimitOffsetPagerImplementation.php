<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Pager;

use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\OperatorDefinition\Pager\LimitOffsetPagerDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class LimitOffsetPagerImplementation
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class LimitOffsetPagerImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return LimitOffsetPagerDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/** @var OrmDataProvider $dataProvider */
		/** @var LimitOffsetPagerDefinition $definition */

		$qb = $dataProvider->getQueryBuilder();
		$qb
			->setFirstResult($definition->getOffset())
			->setMaxResults($definition->getLimit());
	}

}