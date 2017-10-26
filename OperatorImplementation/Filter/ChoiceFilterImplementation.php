<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class ChoiceFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class ChoiceFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return ChoiceFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var OrmDataProvider $dataProvider
		 * @var ChoiceFilterDefinition $definition
		 */
		if(empty($definition->getValue())) return;

		$qb = $dataProvider->getQueryBuilder();
		$field = $dataProvider->getFieldMapping($definition->getName());
		$param = ':' . $definition->getName();

		if($definition->isMulti() && is_array($definition->getValue())) {
			$qb->andWhere($qb->expr()->in($field, $param));

		} else {
			$qb->andWhere($qb->expr()->eq($field, $param));
		}

		$qb->setParameter($param, $definition->getValue());
	}
}