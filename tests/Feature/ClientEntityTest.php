<?php

namespace OfflineAgency\LaravelFattureInCloudV2\Tests\Feature;

use Illuminate\Support\Facades\Http;
use OfflineAgency\LaravelFattureInCloudV2\Api\Client;
use OfflineAgency\LaravelFattureInCloudV2\Entities\Client\ClientList;
use OfflineAgency\LaravelFattureInCloudV2\Entities\Client\Client as ClientEntity;
use OfflineAgency\LaravelFattureInCloudV2\Entities\Client\ClientPagination;
use OfflineAgency\LaravelFattureInCloudV2\Entities\Error;
use OfflineAgency\LaravelFattureInCloudV2\Tests\Fake\ClientFakeResponse;
use OfflineAgency\LaravelFattureInCloudV2\Tests\TestCase;

class ClientEntityTest extends TestCase
{
    // list

    public function test_list_clients()
    {
        Http::fake([
            'entities/clients' => Http::response(
                (new ClientFakeResponse())->getClientsFakeList()
            ),
        ]);

        $clients = new Client();
        $response = $clients->list();

        $this->assertInstanceOf(ClientList::class, $response);
        $this->assertInstanceOf(ClientPagination::class, $response->getPagination());
        $this->assertIsArray($response->getItems());
        $this->assertCount(2, $response->getItems());
        $this->assertInstanceOf(ClientEntity::class, $response->getItems()[0]);
    }

    public function test_error_on_list_clients()
    {
        Http::fake([
            'entities/clients' => Http::response(
                (new ClientFakeResponse())->getClientFakeError(),
                401
            ),
        ]);

        $clients = new Client();
        $response = $clients->list();

        $this->assertInstanceOf(Error::class, $response);
    }

    // pagination

    public function test_go_to_client_next_page()
    {
        $product_list = new ClientList(json_decode(
            (new ClientFakeResponse())->getClientsFakeList([
                'next_page_url' => 'https://fake_url/entities/clients?per_page=10&page=2',
            ])
        ));

        Http::fake([
            'entities/clients?per_page=10&page=2' => Http::response(
                (new ClientFakeResponse())->getClientsFakeList()
            ),
        ]);

        $next_page_response = $product_list->getPagination()->goToNextPage();

        $this->assertInstanceOf(ClientList::class, $next_page_response);
    }

    public function test_go_to_client_prev_page()
    {
        $product_list = new ClientList(json_decode(
            (new ClientFakeResponse())->getClientsFakeList([
                'prev_page_url' => 'https://fake_url/entities/clients?per_page=10&page=1',
            ])
        ));

        Http::fake([
            'entities/clients?per_page=10&page=1' => Http::response(
                (new ClientFakeResponse())->getClientsFakeList()
            ),
        ]);

        $prev_page_response = $product_list->getPagination()->goToPrevPage();

        $this->assertInstanceOf(ClientList::class, $prev_page_response);
    }

    public function test_go_to_client_first_page()
    {
        $product_list = new ClientList(json_decode(
            (new ClientFakeResponse())->getClientsFakeList([
                'first_page_url' => 'https://fake_url/entities/clients?per_page=10&page=1',
            ])
        ));

        Http::fake([
            'entities/clients?per_page=10&page=1' => Http::response(
                (new ClientFakeResponse())->getClientsFakeList()
            ),
        ]);

        $first_page_response = $product_list->getPagination()->goToFirstPage();

        $this->assertInstanceOf(ClientList::class, $first_page_response);
    }

    public function test_go_to_client_last_page()
    {
        $product_list = new ClientList(json_decode(
            (new ClientFakeResponse())->getClientsFakeList([
                'last_page_url' => 'https://fake_url/entities/clients?per_page=10&page=2',
            ])
        ));

        Http::fake([
            'entities/clients?per_page=10&page=2' => Http::response(
                (new ClientFakeResponse())->getClientsFakeList()
            ),
        ]);

        $last_page_response = $product_list->getPagination()->goToLastPage();

        $this->assertInstanceOf(ClientList::class, $last_page_response);
    }

    // single

    public function test_detail_client()
    {
        $client_id = 1;

        Http::fake([
            'entities/clients/'.$client_id => Http::response(
                (new ClientFakeResponse())->getClientFakeDetail()
            ),
        ]);

        $client = new Client();
        $response = $client->detail($client_id);

        $this->assertNotNull($response);
        $this->assertInstanceOf(ClientEntity::class, $response);
    }
}
