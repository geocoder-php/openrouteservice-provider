<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenRouteService\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\OpenRouteService\OpenRouteService;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class OpenRouteServiceTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new OpenRouteService($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('openrouteservice', $provider->getName());
    }

    public function testGeocode(): void
    {
        $provider = new OpenRouteService($this->getMockedHttpClient('{}'), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress(): void
    {
        if (!isset($_SERVER['OPEN_ROUTE_SERVICE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPEN_ROUTE_SERVICE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenRouteService($this->getHttpClient($_SERVER['OPEN_ROUTE_SERVICE_API_KEY']), $_SERVER['OPEN_ROUTE_SERVICE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('242 Acklam Road, London, United Kingdom'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(51.521124, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.20360200000000001, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Acklam Road', $result->getStreetName());
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(4, $result->getAdminLevels());
        $this->assertEquals('London', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GBR', $result->getCountry()->getCode());
    }

    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['OPEN_ROUTE_SERVICE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPEN_ROUTE_SERVICE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenRouteService($this->getHttpClient($_SERVER['OPEN_ROUTE_SERVICE_API_KEY']), $_SERVER['OPEN_ROUTE_SERVICE_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(54.0484068, -2.7990345));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(54.048411999999999, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-2.7989549999999999, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals(11, $result->getStreetNumber());
        $this->assertEquals('Ffrances Passage', $result->getStreetName());
        $this->assertEquals('LA1 1UG', $result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(4, $result->getAdminLevels());
        $this->assertEquals('Lancashire', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GBR', $result->getCountry()->getCode());
    }

    public function testReverseWithVillage(): void
    {
        if (!isset($_SERVER['OPEN_ROUTE_SERVICE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPEN_ROUTE_SERVICE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenRouteService($this->getHttpClient($_SERVER['OPEN_ROUTE_SERVICE_API_KEY']), $_SERVER['OPEN_ROUTE_SERVICE_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(49.1390924, 1.6572462));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEquals('Bray-et-Lû', $result->getLocality());
    }

    public function testGeocodeWithCity(): void
    {
        if (!isset($_SERVER['OPEN_ROUTE_SERVICE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPEN_ROUTE_SERVICE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenRouteService($this->getHttpClient($_SERVER['OPEN_ROUTE_SERVICE_API_KEY']), $_SERVER['OPEN_ROUTE_SERVICE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hanover'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(52.379952, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(9.787455, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(4, $result->getAdminLevels());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Germany', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(18.393428, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-78.107687, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNull($result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Jamaica', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(39.192889999999998, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-76.724140000000006, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
    }

    public function testGeocodeWithCityDistrict(): void
    {
        if (!isset($_SERVER['OPEN_ROUTE_SERVICE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPEN_ROUTE_SERVICE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenRouteService($this->getHttpClient($_SERVER['OPEN_ROUTE_SERVICE_API_KEY']), $_SERVER['OPEN_ROUTE_SERVICE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(2, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(50.189017, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(8.6367809999999992, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('10a', $result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(5, $result->getAdminLevels());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(4)->getName());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('HE', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());
    }

    public function testGeocodeQuotaExceeded(): void
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);
        $this->expectExceptionMessage('Valid request but quota exceeded.');

        $provider = new OpenRouteService(
            $this->getMockedHttpClient(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 429
                    },
                    "results": {
                        "error": {
                            "type": "QpsExceededError",
                            "message": "Queries per second exceeded: Queries exceeded (6 allowed)."
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    public function testGeocodeInvalidApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid or missing api key.');

        $provider = new OpenRouteService(
            $this->getMockedHttpClient(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 403
                    },
                    "results": {
                        "error": {
                            "type": "KeyError",
                            "message": "No api_key specified."
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The openrouteservice provider does not support IP addresses, only street addresses.');

        $provider = new OpenRouteService($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The openrouteservice provider does not support IP addresses, only street addresses.');

        $provider = new OpenRouteService($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The openrouteservice provider does not support IP addresses, only street addresses.');

        $provider = new OpenRouteService($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The openrouteservice provider does not support IP addresses, only street addresses.');

        $provider = new OpenRouteService($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
