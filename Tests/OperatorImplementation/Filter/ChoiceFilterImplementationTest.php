<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter\ChoiceFilterImplementation;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilter\ChoiceProvider\ArrayProvider;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class ChoiceFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class ChoiceFilterImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testNotMulti()
	{
		$paramName = 'test';
		$paramValue = 'asdf';
		$paramMapping = 'test';
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('eq')
			->with($sqlParam, $paramMapping);

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expressionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParam, $paramValue)
			->once();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new ChoiceFilterDefinition($paramName, new ArrayProvider([$paramValue]));
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new ChoiceFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMulti()
	{
		$paramName = 'test';
		$paramValue = [ 'asdf' ];
		$paramMapping = 'test';
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('in')
			->with($sqlParam, $paramMapping);

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expressionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParam, $paramValue)
			->once();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new ChoiceFilterDefinition($paramName, new ArrayProvider(array_merge($paramValue, [])), true);
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new ChoiceFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}
