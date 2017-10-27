<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\DateRangeDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class DateRangeFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateRangeFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return DateRangeDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var OrmDataProvider     $dataProvider
		 * @var DateRangeDefinition $definition
		 */
		$field = $dataProvider->getFieldMapping($definition->getName());
		$dateStart = $this->prepareDate($definition->getDateStart(), $definition, true);
		$dateEnd = $this->prepareDate($definition->getDateEnd(), $definition, false);

		if ($dateStart !== null && $dateEnd !== null) {
			$this->handleBoth($field, $dataProvider, $definition, $dateStart, $dateEnd);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$param = ':' . $definition->getName();


		$this->applyDate($qb, $field, $param, $this->getComparisonType(true, $definition->hasTimePart()), $dateStart);
		$this->applyDate($qb, $field, $param, $this->getComparisonType(false, $definition->hasTimePart()), $dateEnd);
	}

	protected function applyDate(QueryBuilder $qb, string $field, string $paramName, string $type, $date)
	{
		if($date === null) {
			return;
		}

		$qb
			->andWhere($qb->expr()->{$type}($field, $paramName))
			->setParameter($paramName, $date);
	}

	protected function getComparisonType(bool $isStart, bool $hasTime)
	{
		return $isStart ? 'gte' : ($hasTime ? 'lte' : 'lt');
	}

	/**
	 * @param \DateTime|null      $date
	 * @param DateRangeDefinition $definition
	 * @param bool                $isStart
	 * @return null|\DateTime
	 */
	protected function prepareDate($date, DateRangeDefinition $definition, bool $isStart)
	{
		if ($date === null) {
			return null;
		}

		$retDate = clone $date;

		if (!$definition->hasTimePart()) {
			$retDate->setTime(0, 0);

			if(!$isStart) {
				$retDate->modify('+1 day');
			}
		}

		return $retDate;
	}

	/**
	 * @param string              $field
	 * @param OrmDataProvider     $dataProvider
	 * @param DateRangeDefinition $definition
	 * @param \DateTime           $dateStart
	 * @param \DateTime           $dateEnd
	 */
	protected function handleBoth(
		string $field, OrmDataProvider $dataProvider, DateRangeDefinition $definition,
		\DateTime $dateStart, \DateTime $dateEnd
	)
	{
		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_start';
		$paramEnd = ':' . $definition->getName() . '_end';

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lt($field, $paramEnd)
				)
			)
			->setParameter($paramStart, $dateStart)
			->setParameter($paramEnd, $dateEnd);
	}

}