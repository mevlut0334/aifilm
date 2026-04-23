<?php

namespace App\Console\Commands;

use App\Models\Template;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('test:template-api')]
#[Description('Test Template API endpoints')]
class TestTemplateApi extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Template API Endpoints...');
        $this->newLine();

        // 1. Create or get test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
            ]
        );

        // 2. Create token
        $token = $user->createToken('test-token')->plainTextToken;
        $this->info('✓ Test user and token created');

        // 3. Check if templates exist
        $templateCount = Template::active()->count();
        $this->info("✓ Found {$templateCount} active template(s)");

        if ($templateCount === 0) {
            $this->warn('⚠ No active templates found. Creating a test template...');
            Template::create([
                'title' => ['en' => 'Test Template', 'tr' => 'Test Şablonu'],
                'description' => ['en' => 'Test description', 'tr' => 'Test açıklama'],
                'token_cost' => 10,
                'is_active' => true,
                'order' => 1,
            ]);
            $this->info('✓ Test template created');
        }

        $this->newLine();

        // 4. Test GET /api/v1/templates
        $this->info('Testing GET /api/v1/templates...');
        $response = Http::withToken($token)
            ->withHeaders(['Accept-Language' => 'en'])
            ->get('http://localhost/api/v1/templates');

        if ($response->successful()) {
            $data = $response->json();
            $this->info('✓ Status: '.$response->status());
            $this->info('✓ Success: '.($data['success'] ? 'true' : 'false'));
            $this->info('✓ Locale: '.$data['locale']);
            $this->info('✓ Templates count: '.count($data['data'] ?? []));
        } else {
            $this->error('✗ Failed with status: '.$response->status());
            $this->error('Response: '.$response->body());
        }

        $this->newLine();

        // 5. Test GET /api/v1/templates?orientation=landscape
        $this->info('Testing GET /api/v1/templates?orientation=landscape...');
        $response = Http::withToken($token)
            ->withHeaders(['Accept-Language' => 'en'])
            ->get('http://localhost/api/v1/templates?orientation=landscape');

        if ($response->successful()) {
            $data = $response->json();
            $this->info('✓ Status: '.$response->status());
            $this->info('✓ Filtered templates count: '.count($data['data'] ?? []));
        } else {
            $this->error('✗ Failed with status: '.$response->status());
        }

        $this->newLine();

        // 6. Test GET /api/v1/templates/{uuid}
        $firstTemplate = Template::active()->first();
        if ($firstTemplate) {
            $this->info("Testing GET /api/v1/templates/{$firstTemplate->uuid}...");
            $response = Http::withToken($token)
                ->withHeaders(['Accept-Language' => 'en'])
                ->get("http://localhost/api/v1/templates/{$firstTemplate->uuid}");

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✓ Status: '.$response->status());
                $this->info('✓ Template UUID: '.$data['data']['uuid']);
                $this->info('✓ Template Title (en): '.$data['data']['title']['en']);
            } else {
                $this->error('✗ Failed with status: '.$response->status());
            }
        }

        $this->newLine();

        // 7. Test without auth (with Accept: application/json header to get JSON response)
        $this->info('Testing without authentication...');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
        ])->get('http://localhost/api/v1/templates');

        if ($response->status() === 401) {
            $this->info('✓ Correctly returns 401 Unauthorized');
        } else {
            $this->warn('⚠ Expected 401, got: '.$response->status());
        }

        // 8. Test with invalid token
        $this->info('Testing with invalid token...');
        $response = Http::withToken('invalid-token-here')
            ->withHeaders([
                'Accept' => 'application/json',
                'Accept-Language' => 'en',
            ])
            ->get('http://localhost/api/v1/templates');

        if ($response->status() === 401) {
            $this->info('✓ Correctly returns 401 Unauthorized for invalid token');
        } else {
            $this->warn('⚠ Expected 401, got: '.$response->status());
        }

        $this->newLine();

        // 9. Test Turkish locale
        $this->info('Testing with Turkish locale...');
        $response = Http::withToken($token)
            ->withHeaders(['Accept-Language' => 'tr'])
            ->get('http://localhost/api/v1/templates');

        if ($response->successful()) {
            $data = $response->json();
            $this->info('✓ Status: '.$response->status());
            $this->info('✓ Locale: '.$data['locale']);
            if (! empty($data['data'])) {
                $firstTemplate = $data['data'][0];
                $this->info('✓ Turkish title exists: '.(! empty($firstTemplate['title']['tr']) ? 'yes' : 'no'));
            }
        }

        $this->newLine();

        // 10. Verify response structure
        $this->info('Verifying response structure...');
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/json', 'Accept-Language' => 'en'])
            ->get('http://localhost/api/v1/templates');

        if ($response->successful()) {
            $data = $response->json();
            $hasRequiredFields = isset($data['success']) &&
                               isset($data['locale']) &&
                               isset($data['data']);

            if ($hasRequiredFields) {
                $this->info('✓ Response has required fields: success, locale, data');

                if (! empty($data['data'])) {
                    $template = $data['data'][0];
                    $templateFields = ['uuid', 'title', 'description', 'token_cost',
                        'landscape_video_url', 'portrait_video_url',
                        'square_video_url', 'created_at'];

                    $missingFields = array_diff($templateFields, array_keys($template));

                    if (empty($missingFields)) {
                        $this->info('✓ Template object has all required fields');
                    } else {
                        $this->error('✗ Missing fields: '.implode(', ', $missingFields));
                    }
                }
            } else {
                $this->error('✗ Response missing required fields');
            }
        }

        $this->newLine();
        $this->info('✅ All tests completed!');

        return Command::SUCCESS;
    }
}
