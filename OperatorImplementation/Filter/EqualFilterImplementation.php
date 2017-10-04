<?php

namespace Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter;

use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\OperatorDefinition\Filter\EqualFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementation\AbstractValueFilterImplementation;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class EqualFilterImplementation
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class EqualFilterImplementation extends AbstractValueFilterImplementation
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return EqualFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	protected function _apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/** @var OrmDataProvider $dataProvider */
		/** @var EqualFilterDefinition $definition */
		$qb = $dataProvider->getQueryBuilder();

		$field = $dataProvider->getFieldMapping($definition->getName());
		$param = ':' . $definition->getName();

		$qb
			->andWhere($qb->expr()->eq($field, $param))
			->setParameter($param, $definition->getValue());
	}

}