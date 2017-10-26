<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\RangeFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class RangeOperatorFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class RangeFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return RangeFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var OrmDataProvider $dataProvider
		 * @var RangeFilterDefinition $definition
		 */
		$field = $dataProvider->getFieldMapping($definition->getName());

		if($definition->getMin() !== null && $definition->getMax() !== null) {
			$this->handleMinMax($field, $dataProvider, $definition);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$param = ':' . $definition->getName();

		if($definition->getMin() !== null) {
			$qb
				->andWhere($qb->expr()->gte($field, $param))
				->setParameter($param, $definition->getMin());
			return;
		}

		if($definition->getMax() !== null) {
			$qb
				->andWhere($qb->expr()->lte($field, $param))
				->setParameter($param, $definition->getMax());
		}
	}

	/**
	 * @param string                $field
	 * @param OrmDataProvider       $dataProvider
	 * @param RangeFilterDefinition $definition
	 */
	protected function handleMinMax(string $field, OrmDataProvider $dataProvider, RangeFilterDefinition $definition)
	{
		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_min';
		$paramEnd = ':' . $definition->getName() . '_max';

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lte($field, $paramEnd)
				)
			)
			->setParameter($paramStart, $definition->getMin())
			->setParameter($paramEnd, $definition->getMax());
	}
}