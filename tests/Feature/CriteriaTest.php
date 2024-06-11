<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile; 
use Illuminate\Support\Facades\Storage;

class CriteriaTest extends TestCase
{
    public function testItReturnsAllCriterias()
    {
        $response = $this->get('/api/criteria/index');

        $response->assertStatus(200);

        $responseData = $response->json();
        dump($responseData);
    }

            
    public function testItCreatesNewCriteria()
    {
    
        $data = [
            'name' => 'communication , sociabilité',
        ];
    
        $response = $this->post('/api/criteria/store', $data);
    
        $response->assertStatus(201);
    
        $responseData = $response->json();
        $this->assertEquals('Critère créé avec succès.', $responseData['data']);
        $this->assertArrayHasKey('critère', $responseData);
    
        $this->assertDatabaseHas('criterias', [
            'name' => 'communication , sociabilité',
        ]);
    }
}
