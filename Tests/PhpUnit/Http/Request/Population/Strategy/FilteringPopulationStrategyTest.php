<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Http\Request\Population\Strategy;

use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\Population\RequestPopulationContext;
use apivalk\apivalk\Http\Request\Population\Strategy\FilteringPopulationStrategy;
use apivalk\apivalk\Router\Route\Filter\BooleanFilter;
use apivalk\apivalk\Router\Route\Filter\DateFilter;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\FilterBag;
use apivalk\apivalk\Router\Route\Filter\FilterInterface;
use apivalk\apivalk\Router\Route\Filter\IntegerFilter;
use apivalk\apivalk\Router\Route\Filter\Operator;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class FilteringPopulationStrategyTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    /**
     * @param FilterInterface[] $filters
     */
    private function populate(array $filters): FilterBag
    {
        $route = Route::get('/api/v1/animals');
        $route->filtering($filters);

        $request = $this->makeRequest();
        (new FilteringPopulationStrategy())->populate(
            $request,
            new RequestPopulationContext($route, new ApivalkRequestDocumentation())
        );

        return $request->filtering();
    }

    /**
     * A list has no string keys, so it cannot be an operator map. It means the same as the
     * comma-separated form, which is what lets a QUERY body send a real JSON array.
     */
    public function testFlatListResolvesToTheDefaultInOperator(): void
    {
        $_GET['status'] = ['draft', 'active'];

        $filters = $this->populate([
            new EnumFilter(new EnumProperty('status', 'Status', ['draft', 'active']), Operator::IN),
        ]);

        self::assertSame(['draft', 'active'], $filters->get('status')->in);
        self::assertSame([], $filters->getViolations());
    }

    public function testFlatListIsStillAViolationWhenTheDefaultOperatorIsNotIn(): void
    {
        $_GET['status'] = ['draft', 'active'];

        $filters = $this->populate([
            new EnumFilter(new EnumProperty('status', 'Status', ['draft', 'active']), Operator::EQ, Operator::IN),
        ]);

        self::assertSame([], $filters->get('status')->conditions());
        self::assertCount(2, $filters->getViolations());
    }

    public function testOperatorMapIsStillReadAsAnOperatorMap(): void
    {
        $_GET['status'] = ['in' => 'draft,active'];

        $filters = $this->populate([
            new EnumFilter(new EnumProperty('status', 'Status', ['draft', 'active']), Operator::IN),
        ]);

        self::assertSame(['draft', 'active'], $filters->get('status')->in);
        self::assertSame([], $filters->getViolations());
    }

    public function testBooleanFilterReadsTheWireValueRatherThanCastingEveryStringToTrue(): void
    {
        $_GET['is_primary'] = 'false';

        $filters = $this->populate([
            new BooleanFilter(new BooleanProperty('is_primary', 'Is primary', false), Operator::EQ),
        ]);

        self::assertFalse($filters->get('is_primary')->equal);
    }

    public function testBooleanFilterNullsAValueThatIsNotABoolean(): void
    {
        $_GET['is_primary'] = 'schwurbel';

        $filters = $this->populate([
            new BooleanFilter(new BooleanProperty('is_primary', 'Is primary', false), Operator::EQ),
        ]);

        // The validation middleware turns the null into a 422, a `true` would have passed.
        self::assertNull($filters->get('is_primary')->equal);
    }

    public function testDeclaredFilterWithoutInputIsPresentButEmpty(): void
    {
        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::EQ)]);

        self::assertTrue($filters->has('status'));
        self::assertSame([], $filters->get('status')->conditions());
        self::assertFalse($filters->get('status')->has(Operator::EQ));
    }

    public function testFlatNotationUsesTheFirstDeclaredOperator(): void
    {
        $_GET['status'] = 'active';

        $filters = $this->populate([
            new StringFilter(new StringProperty('status'), Operator::EQ, Operator::LIKE),
        ]);

        self::assertTrue($filters->get('status')->has(Operator::EQ));
        self::assertSame('active', $filters->status->equal);
        self::assertSame('active', $filters->get('status')->raw(Operator::EQ));
        self::assertFalse($filters->get('status')->has(Operator::LIKE));
    }

    public function testBracketNotationCarriesSeveralOperatorsOnOneField(): void
    {
        $_GET['weight'] = ['gte' => '5', 'lte' => '20'];

        $filters = $this->populate([
            new IntegerFilter(new IntegerProperty('weight'), Operator::GTE, Operator::LTE),
        ]);

        self::assertSame(5, $filters->weight->greaterThanOrEqual);
        self::assertSame(20, $filters->weight->lessThanOrEqual);
        self::assertSame('5', $filters->get('weight')->raw(Operator::GTE));
    }

    public function testValuesAreCastToThePropertyType(): void
    {
        $_GET['born'] = ['gt' => '2020-01-15'];

        $filters = $this->populate([new DateFilter(new DateProperty('born'), Operator::GT)]);

        self::assertInstanceOf(\DateTime::class, $filters->born->greaterThan);
    }

    public function testInIsSplitOnCommasAndCastPerItem(): void
    {
        $_GET['weight'] = ['in' => '5, 20,7'];

        $filters = $this->populate([new IntegerFilter(new IntegerProperty('weight'), Operator::IN)]);

        self::assertSame([5, 20, 7], $filters->weight->in);
    }

    public function testNullOperatorTakesABoolean(): void
    {
        $_GET['status'] = ['null' => 'true'];

        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::NULL)]);

        self::assertTrue($filters->status->isNull);
    }

    public function testAnOperatorTheFieldDoesNotAllowBecomesAViolation(): void
    {
        $_GET['status'] = ['like' => 'act'];

        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::EQ)]);

        self::assertSame([['field' => 'status', 'operator' => 'like']], $filters->getViolations());
        self::assertSame([], $filters->get('status')->conditions());
    }

    public function testAnUnknownOperatorBecomesAViolation(): void
    {
        $_GET['status'] = ['0' => 'active'];

        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::EQ)]);

        self::assertSame([['field' => 'status', 'operator' => '0']], $filters->getViolations());
    }

    public function testANonScalarOperatorValueBecomesAViolation(): void
    {
        $_GET['status'] = ['eq' => ['active', 'draft']];

        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::EQ)]);

        self::assertSame([['field' => 'status', 'operator' => 'eq']], $filters->getViolations());
    }

    public function testTheLegacyFilterWrapperNoLongerResolves(): void
    {
        $_GET['filter'] = ['status' => 'active'];

        $filters = $this->populate([new StringFilter(new StringProperty('status'), Operator::EQ)]);

        self::assertSame([], $filters->get('status')->conditions());
        self::assertSame([], $filters->getViolations());
    }

    public function testPopulationDoesNotMutateTheRouteFilter(): void
    {
        $_GET['status'] = 'active';

        $declared = new StringFilter(new StringProperty('status'), Operator::EQ);
        $route = Route::get('/api/v1/animals');
        $route->filtering([$declared]);

        $request = $this->makeRequest();
        (new FilteringPopulationStrategy())->populate(
            $request,
            new RequestPopulationContext($route, new ApivalkRequestDocumentation())
        );

        self::assertSame('active', $request->filtering()->status->equal);
        self::assertSame([], $declared->conditions());
    }

    private function makeRequest(): AbstractApivalkRequest
    {
        return new class() extends AbstractApivalkRequest {
            private ParameterBag $queryBag;
            private FilterBag $filterBag;

            public function __construct()
            {
                $this->queryBag = new ParameterBag();
                $this->filterBag = new FilterBag();
            }

            public static function getDocumentation(): ApivalkRequestDocumentation
            {
                return new ApivalkRequestDocumentation();
            }

            public function query(): ParameterBag
            {
                return $this->queryBag;
            }

            public function setFilterBag(FilterBag $bag): void
            {
                $this->filterBag = $bag;
            }

            public function filtering(): FilterBag
            {
                return $this->filterBag;
            }
        };
    }
}
