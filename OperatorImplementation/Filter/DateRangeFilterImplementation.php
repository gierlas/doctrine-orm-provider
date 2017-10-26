<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

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

		if ($definition->getDateStart()) {
			$qb
				->andWhere($qb->expr()->gte($field, $param))
				->setParameter($param, $dateStart);
			return;
		}

		if ($definition->getDateEnd()) {
			$qb
				->andWhere($qb->expr()->lt($field, $param))
				->setParameter($param, $dateEnd);
		}
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
		}

		if(!$isStart) {
			$retDate->modify('+1 day');
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