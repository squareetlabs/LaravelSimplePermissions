<?php

namespace Squareetlabs\LaravelSimplePermissions\Exceptions;

use Exception;
use Illuminate\Support\Facades\Schema;

class AuditTableMissingException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        $message = "La auditoría está habilitada pero la tabla 'audit_logs' no existe.\n\n";
        $message .= "Para resolver esto, ejecuta:\n";
        $message .= "1. php artisan vendor:publish --tag=simple-permissions-migrations\n";
        $message .= "2. php artisan migrate\n\n";
        $message .= "O desactiva la auditoría en config/simple-permissions.php:\n";
        $message .= "'audit' => ['enabled' => false]";

        parent::__construct($message);
    }
}

