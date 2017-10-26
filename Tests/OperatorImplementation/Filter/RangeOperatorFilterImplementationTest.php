<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter\RangeOperatorFilterImplementation;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\RangeFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class RangeOperatorFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class RangeOperatorFilterImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testMinMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$min = 0;
		$max = 1;
		$sqlParamMin = ':' . $paramName . '_min';
		$sqlParamMax = ':' . $paramName . '_max';

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParamMin)
			->once();

		$expressionBuilder
			->shouldReceive('lte')
			->with($paramMapping, $sqlParamMax)
			->once();

		$expressionBuilder
			->shouldReceive('andX')
			->once();

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
			->with($sqlParamMin, $min)
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParamMax, $max)
			->andReturnSelf()
			->once();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'min' => $min,
				'max' => $max
			]
		]);

		$filterImplementation = new RangeOperatorFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMin()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$min = 0;
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParam)
			->once();

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
			->with($sqlParam, $min)
			->andReturnSelf()
			->once();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'min' => $min
			]
		]);

		$filterImplementation = new RangeOperatorFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$max = 0;
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('lte')
			->with($paramMapping, $sqlParam)
			->once();

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
			->with($sqlParam, $max)
			->andReturnSelf()
			->once();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'max' => $max
			]
		]);

		$filterImplementation = new RangeOperatorFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}
