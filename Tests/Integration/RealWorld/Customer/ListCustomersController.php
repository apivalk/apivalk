<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer;

use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\Pagination\PagePaginationResponse;
use apivalk\apivalk\Router\RateLimit\IpRateLimit;
use apivalk\apivalk\Router\Route\Filter\EnumFilter;
use apivalk\apivalk\Router\Route\Filter\StringFilter;
use apivalk\apivalk\Router\Route\Pagination\Pagination;
use apivalk\apivalk\Router\Route\Route;
use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Security\RouteAuthorization;
use Tests\Integration\RealWorld\Customer\Request\CustomerListRequest;

class ListCustomersController extends AbstractApivalkController
{
    public static function getRoute(): Route
    {
        return Route::get('/v1/api/customers')
            ->routeAuthorization(new RouteAuthorization('bearer', ['api:customers'], ['api:customers:read']))
            ->filtering([
                StringFilter::like(new StringProperty('first_name', 'First name')),
                StringFilter::like(new StringProperty('last_name', 'Last name')),
                StringFilter::equals(new StringProperty('email', 'Email address')),
                EnumFilter::equals(new EnumProperty('status', 'Status', ['active', 'inactive', 'pending'])),
            ])
            ->sorting([
                Sort::asc('last_name'),
                Sort::desc('created_at'),
                Sort::asc('first_name'),
                Sort::asc('email'),
            ])
            ->pagination(Pagination::page()->setMaxLimit(100))
            ->rateLimit(new IpRateLimit('list-customers', 60, 60));
    }

    public static function getRequestClass(): string
    {
        return CustomerListRequest::class;
    }

    public static function getResponseClasses(): array
    {
        return [CustomerListResponse::class];
    }

    public function __invoke(ApivalkRequestInterface $request): AbstractApivalkResponse
    {
        $customers = self::fixtures();
        $customers = $this->applyFilters($customers, $request);
        $customers = $this->applySorts($customers, $request);

        $response = new CustomerListResponse($customers);
        $response->setPaginationResponse(new PagePaginationResponse(count($customers), 25, false, 1));

        return $response;
    }

    /**
     * @param array<int, array<string, mixed>> $customers
     * @return array<int, array<string, mixed>>
     */
    private function applyFilters(array $customers, ApivalkRequestInterface $request): array
    {
        $filterBag = $request->filtering();

        $firstNameFilter = $filterBag->get('first_name');
        if ($firstNameFilter !== null && $firstNameFilter->getValue() !== null) {
            $needle = strtolower((string) $firstNameFilter->getValue());
            $customers = array_values(array_filter($customers, function (array $c) use ($needle): bool {
                return strpos(strtolower($c['first_name']), $needle) !== false;
            }));
        }

        $lastNameFilter = $filterBag->get('last_name');
        if ($lastNameFilter !== null && $lastNameFilter->getValue() !== null) {
            $needle = strtolower((string) $lastNameFilter->getValue());
            $customers = array_values(array_filter($customers, function (array $c) use ($needle): bool {
                return strpos(strtolower($c['last_name']), $needle) !== false;
            }));
        }

        $emailFilter = $filterBag->get('email');
        if ($emailFilter !== null && $emailFilter->getValue() !== null) {
            $needle = strtolower((string) $emailFilter->getValue());
            $customers = array_values(array_filter($customers, function (array $c) use ($needle): bool {
                return strtolower($c['email']) === $needle;
            }));
        }

        $statusFilter = $filterBag->get('status');
        if ($statusFilter !== null && $statusFilter->getValue() !== null) {
            $needle = (string) $statusFilter->getValue();
            $customers = array_values(array_filter($customers, function (array $c) use ($needle): bool {
                return $c['status'] === $needle;
            }));
        }

        return $customers;
    }

    /**
     * @param array<int, array<string, mixed>> $customers
     * @return array<int, array<string, mixed>>
     */
    private function applySorts(array $customers, ApivalkRequestInterface $request): array
    {
        $sorts = $request->sorting()->getRequested();
        if (empty($sorts)) {
            return $customers;
        }

        usort($customers, function (array $a, array $b) use ($sorts): int {
            foreach ($sorts as $sort) {
                $field = $sort->getField();
                $cmp = strcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) {
                    return $sort->isAsc() ? $cmp : -$cmp;
                }
            }
            return 0;
        });

        return $customers;
    }

    /** @return array<int, array<string, mixed>> */
    private static function fixtures(): array
    {
        return [
            ['customer_id' => 1, 'first_name' => 'Alice', 'last_name' => 'Anderson', 'email' => 'alice@example.com', 'status' => 'active',   'created_at' => '2024-01-01T00:00:00Z'],
            ['customer_id' => 2, 'first_name' => 'Bob',   'last_name' => 'Brown',    'email' => 'bob@example.com',   'status' => 'inactive', 'created_at' => '2024-02-01T00:00:00Z'],
            ['customer_id' => 3, 'first_name' => 'Carol', 'last_name' => 'Clark',    'email' => 'carol@example.com', 'status' => 'active',   'created_at' => '2024-03-01T00:00:00Z'],
            ['customer_id' => 4, 'first_name' => 'Dave',  'last_name' => 'Davis',    'email' => 'dave@example.com',  'status' => 'pending',  'created_at' => '2024-01-15T00:00:00Z'],
            ['customer_id' => 5, 'first_name' => 'Eve',   'last_name' => 'Evans',    'email' => 'eve@example.com',   'status' => 'active',   'created_at' => '2023-12-01T00:00:00Z'],
        ];
    }
}
