<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Log an audit action
     *
     * @param string $action Action name (e.g., 'create', 'update', 'delete', 'login')
     * @param string|null $description Optional description
     * @param mixed $model Optional model instance that was affected
     * @param array|null $oldValues Old values (for update actions)
     * @param array|null $newValues New values (for update actions)
     * @param Request|null $request Optional request object
     * @return Audit
     */
    public static function logAudit(
        string $action,
        ?string $description = null,
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null
    ): Audit {
        $request = $request ?? request();
        $authUser = Auth::user();
        $authAdmin = Auth::guard('admin')->user();

        $auditData = [
            'action' => $action,
            'description' => $description,
            'ip_address' => getRealIP(),
            'user_agent' => $request->userAgent(),
            'route' => $request->path(),
            'method' => $request->method(),
            'request_data' => $request->except(['password', 'password_confirmation', '_token']),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];

        // Determine user type and set appropriate IDs
        if ($authAdmin) {
            $auditData['user_type'] = 'admin';
            $auditData['admin_id'] = $authAdmin->id;
        } elseif ($authUser) {
            $auditData['user_type'] = 'user';
            $auditData['user_id'] = $authUser->id;
        } else {
            $auditData['user_type'] = 'user'; // Default to user if no auth
        }

        // Set model information if provided
        if ($model) {
            $auditData['model_type'] = get_class($model);
            $auditData['model_id'] = $model->id ?? null;
        }

        return Audit::create($auditData);
    }

    /**
     * Log audit from a controller method
     * This is a convenience method that can be called directly
     *
     * @param string $action
     * @param string|null $description
     * @param mixed $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return Audit
     */
    protected function audit(
        string $action,
        ?string $description = null,
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): Audit {
        return self::logAudit($action, $description, $model, $oldValues, $newValues, request());
    }
}

