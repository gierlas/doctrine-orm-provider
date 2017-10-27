<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kora\DataProvider\DataProviderOperatorsSetup;
use Kora\DataProvider\Doctrine\Orm\OperatorImplementation\Filter\EqualFilterImplementation;
use Kora\DataProvider\Doctrine\Orm\OrmDataProvider;
use Kora\DataProvider\Doctrine\Orm\OrmImplementationList;
use Kora\DataProvider\Doctrine\Orm\Tests\AbstractDoctrineTest;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\EqualFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class EqualFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class EqualFilterImplementationTest extends AbstractDoctrineTest
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @dataProvider resultProvider
	 * @param $term
	 * @param $expectedCount
	 */
	public function testResult($term, $expectedCount)
	{
		$em = $this->getPropagatedEM();
		$qb = $em->createQueryBuilder()
			->select('f')
			->from('Test:Foo', 'f');

		$setup = new DataProviderOperatorsSetup();
		$setup
			->addFilter(new EqualFilterDefinition('title'));

		$setup->setData([
			'title' => $term
		]);

		$ormDataProvider = new OrmDataProvider(new OrmImplementationList(), clone $qb, new Mapper());
		$data = $ormDataProvider->fetchData($setup);


		$this->assertEquals($expectedCount, $data->getNbAll());
		$this->assertCount($expectedCount, $data->getResults());
	}

	public function resultProvider()
	{
		return [
			['Title2', 1],
			['Title1', 2]
		];
	}

	public function testIsWhereExecuted()
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

		$filterDefinition = new EqualFilterDefinition($paramName, true);
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new EqualFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}
