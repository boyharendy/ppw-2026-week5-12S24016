<?php

namespace Tests\Feature\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class CoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_rfc_7807_problem_details_for_unknown_api_endpoints(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Not Found',
            'status' => 404,
            'detail' => 'Alamat URI endpoint atau resource yang diminta tidak ditemukan.',
            'instance' => '/api/v1/non-existent-endpoint',
        ]);
        $this->assertArrayHasKey('type', $response->json());
    }

    public function test_api_response_trait_formats_success_and_created_responses(): void
    {
        $dummyClass = new class {
            use ApiResponse;

            public function getSuccess(): JsonResponse
            {
                return $this->successResponse(['item' => 'test'], 'OK', 200, ['total' => 1]);
            }

            public function getCreated(): JsonResponse
            {
                return $this->createdResponse(['id' => 10], 'http://localhost/api/v1/items/10', 'Created');
            }

            public function getNoContent(): JsonResponse
            {
                return $this->noContentResponse();
            }

            public function getProblem(): JsonResponse
            {
                return $this->problemResponse('Custom Error', 'Detail message', 400, 'bad-req', ['field' => ['error']]);
            }
        };

        $success = $dummyClass->getSuccess();
        $this->assertEquals(200, $success->status());
        $this->assertTrue($success->getData(true)['success']);
        $this->assertEquals(['total' => 1], $success->getData(true)['meta']);

        $created = $dummyClass->getCreated();
        $this->assertEquals(201, $created->status());
        $this->assertEquals('http://localhost/api/v1/items/10', $created->headers->get('Location'));

        $noContent = $dummyClass->getNoContent();
        $this->assertEquals(204, $noContent->status());

        $problem = $dummyClass->getProblem();
        $this->assertEquals(400, $problem->status());
        $this->assertEquals('application/problem+json', $problem->headers->get('Content-Type'));
        $this->assertEquals('Custom Error', $problem->getData(true)['title']);
    }
}
