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
     * Setup .env file
     */
    public function setupEnv(Request $request)
    {
        if (!$this->verifyToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $envPath = base_path('.env');
            $envExamplePath = base_path('.env.example');
            
            // Create .env from .env.example if it doesn't exist
            if (!file_exists($envPath)) {
                if (file_exists($envExamplePath)) {
                    copy($envExamplePath, $envPath);
                    Log::info('.env file created from .env.example');
                } else {
                    touch($envPath);
                    Log::info('.env file created (empty)');
                }
            }
            
            // Generate APP_KEY if not exists
            $envContent = file_get_contents($envPath);
            if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false) {
                Artisan::call('key:generate', ['--force' => true]);
                Log::info('APP_KEY generated');
            }
            
            // Update database credentials
            $dbDatabase = env('DB_DATABASE', $request->input('db_database'));
            $dbUsername = env('DB_USERNAME', $request->input('db_username'));
            $dbPassword = env('DB_PASSWORD', $request->input('db_password'));
            
            if ($dbDatabase || $dbUsername || $dbPassword) {
                $envContent = file_get_contents($envPath);
                
                if ($dbDatabase) {
                    $envContent = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$dbDatabase}", $envContent);
                    if (strpos($envContent, 'DB_DATABASE=') === false) {
                        $envContent .= "\nDB_DATABASE={$dbDatabase}";
                    }
                }
                
                if ($dbUsername) {
                    $envContent = preg_replace('/^DB_USERNAME=.*/m', "DB_USERNAME={$dbUsername}", $envContent);
                    if (strpos($envContent, 'DB_USERNAME=') === false) {
                        $envContent .= "\nDB_USERNAME={$dbUsername}";
                    }
                }
                
                if ($dbPassword) {
                    $envContent = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$dbPassword}", $envContent);
                    if (strpos($envContent, 'DB_PASSWORD=') === false) {
                        $envContent .= "\nDB_PASSWORD={$dbPassword}";
                    }
                }
                
                file_put_contents($envPath, $envContent);
                Log::info('Database credentials updated in .env');
            }
            
            $response = [
                'success' => true,
                'message' => '.env file setup completed'
            ];
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('.env setup failed', ['error' => $e->getMessage()]);
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

