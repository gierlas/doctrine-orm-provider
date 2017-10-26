<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\DateFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class DateFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return DateFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var OrmDataProvider $dataProvider
		 * @var DateFilterDefinition $definition
		 */

		if($definition->getDate() === null) return;

		$field = $dataProvider->getFieldMapping($definition->getName());

		if($definition->hasDatePart() && !$definition->hasTimePart()) {
			$this->handleDate($field, $dataProvider, $definition);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$param = ':' . $definition->getName();

		$qb
			->andWhere($qb->expr()->eq($field, $param))
			->setParameter($param, $definition->getDate());
	}

	/**
	 * @param                      $field
	 * @param OrmDataProvider      $dataProvider
	 * @param DateFilterDefinition $definition
	 */
	protected function handleDate($field, OrmDataProvider $dataProvider, DateFilterDefinition $definition)
	{

		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_start';
		$paramEnd = ':' . $definition->getName() . '_end';

		$startTime = $definition->getDate()->setTime(0, 0);
		$endTime = clone $definition->getDate();
		$endTime->modify('+1 day');

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lt($field, $paramEnd)
				)
			)
			->setParameter($paramEnd, $endTime)
			->setParameter($paramStart, $startTime);
	}

}