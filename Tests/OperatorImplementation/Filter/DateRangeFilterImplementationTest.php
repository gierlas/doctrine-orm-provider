<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter\DateRangeFilterImplementation;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\DateRangeDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class DateRangeFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateRangeFilterImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testMin()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');

		$expectedDateMin = clone $date;
		$expectedDateMin->setTime(0, 0);


		$sqlParamMin = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParamMin)
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
			->withArgs(function($paramName, $value) use($sqlParamMin, $expectedDateMin) {
				return ($paramName === $sqlParamMin && $value == $expectedDateMin);
			})
			->andReturnSelf();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'start' => $date
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');

		$expectedDateMax = clone $date;
		$expectedDateMax->setTime(0, 0)->modify('+1 day');


		$sqlParamMax = ':' . $paramName;

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('lt')
			->with($paramMapping, $sqlParamMax)
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
			->withArgs(function($paramName, $value) use($sqlParamMax, $expectedDateMax) {
				return ($paramName === $sqlParamMax && $value == $expectedDateMax);
			})
			->andReturnSelf();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'end' => $date
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMinMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$dateMin = new \DateTime('2017-01-01 12:22:21');
		$dateMax = new \DateTime('2017-01-02 12:22:21');

		$expectedDateMin = clone $dateMin;
		$expectedDateMin->setTime(0, 0);
		$expectedDateMax = clone $dateMax;
		$expectedDateMax->setTime(0, 0)->modify('+1 day');


		$sqlParamMin = ':' . $paramName . '_start';
		$sqlParamMax = ':' . $paramName . '_end';

		$expressionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParamMin)
			->once();

		$expressionBuilder
			->shouldReceive('lt')
			->with($paramMapping, $sqlParamMax)
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
			->withArgs(function($paramName, $value) use($sqlParamMin, $expectedDateMin, $sqlParamMax, $expectedDateMax) {
				return ($paramName === $sqlParamMin && $value == $expectedDateMin)
					|| ($paramName === $sqlParamMax && $value == $expectedDateMax);
			})
			->andReturnSelf();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'start' => $dateMin,
				'end' => $dateMax
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}
