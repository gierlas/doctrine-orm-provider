<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter\DateFilterImplementation;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\DateFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class DateFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateFilterImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testOnlyDate()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');

		$expectedDateMin = clone $date;
		$expectedDateMin->setTime(0, 0);
		$expectedDateMax = clone $expectedDateMin;
		$expectedDateMax->modify('+1 day');


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
			->withArgs(function($paramName, $value) use($sqlParamMin, $expectedDateMin, $sqlParamMax, $expectedDateMax) {
				return ($paramName === $sqlParamMin && $value == $expectedDateMin)
					|| ($paramName === $sqlParamMax && $value == $expectedDateMax);
			})
			->andReturnSelf();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => $date
		]);

		$filterImplementation = new DateFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testDateTime()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');
		$param = ':' . $paramName;

		$expresionBuilder = m::mock(Expr::class)
			->shouldDeferMissing();

		$expresionBuilder
			->shouldReceive('eq')
			->with($paramMapping, $param)
			->once();

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expresionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->withArgs([$param, $date])
			->andReturnSelf();

		$dataProvider = new OrmDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateFilterDefinition($paramName);
		$filterDefinition->setHasTimePart(true);
		$filterDefinition->initData([
			$paramName => $date
		]);

		$filterImplementation = new DateFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}
