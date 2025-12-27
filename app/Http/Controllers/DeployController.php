<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    /**
     * Verify deployment token
     */
    private function verifyToken(Request $request): bool
    {
        $token = $request->query('token');
        $expectedToken = config('app.deploy_token', env('DEPLOY_TOKEN'));
        
        if (empty($expectedToken)) {
            Log::warning('Deploy token not configured');
            return false;
        }
        
        return hash_equals($expectedToken, $token);
    }

    /**
     * Run composer install
     */
    public function composerInstall(Request $request)
    {
        if (!$this->verifyToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $output = [];
            $exitCode = 0;
            
            // Change to project directory
            $basePath = base_path();
            chdir($basePath);
            
            // Run composer install
            exec('composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $output, $exitCode);
            
            $response = [
                'success' => $exitCode === 0,
                'output' => implode("\n", $output),
                'exit_code' => $exitCode
            ];
            
            Log::info('Composer install executed', $response);
            
            return response()->json($response, $exitCode === 0 ? 200 : 500);
            
        } catch (\Exception $e) {
            Log::error('Composer install failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run database migrations
     */
    public function migrate(Request $request)
    {
        if (!$this->verifyToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            $response = [
                'success' => true,
                'output' => $output
            ];
            
            Log::info('Migrations executed', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Migrations failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        if (!$this->verifyToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Artisan::call('optimize:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            $response = [
                'success' => true,
                'message' => 'Cache cleared successfully'
            ];
            
            Log::info('Cache cleared');
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Cache clear failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

