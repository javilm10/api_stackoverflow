<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class ApiErrorHandler
{
    public function handle(\Exception $e): array
    {
        // Manejo de errores basado en el mensaje de la excepción
        switch ($e->getMessage()) {
            case 'Error fetching data from Stack Overflow API.':
                return ['message' => 'Error fetching data from API.', 'status' => Response::HTTP_BAD_GATEWAY];
            case 'bad_parameter':
                return ['message' => 'Invalid parameters were passed to the API.', 'status' => Response::HTTP_BAD_REQUEST];
            case 'access_token_required':
                return ['message' => 'Access token is required.', 'status' => Response::HTTP_UNAUTHORIZED];
            case 'invalid_access_token':
                return ['message' => 'Invalid access token.', 'status' => Response::HTTP_UNAUTHORIZED];
            case 'access_denied':
                return ['message' => 'Access denied due to insufficient permissions.', 'status' => Response::HTTP_FORBIDDEN];
            case 'no_method':
                return ['message' => 'The requested method does not exist.', 'status' => Response::HTTP_NOT_FOUND];
            case 'key_required':
                return ['message' => 'An application key is required.', 'status' => Response::HTTP_METHOD_NOT_ALLOWED];
            case 'access_token_compromised':
                return ['message' => 'The access token is no longer secure.', 'status' => Response::HTTP_FORBIDDEN];
            case 'write_failed':
                return ['message' => 'Write operation was rejected.', 'status' => Response::HTTP_CONFLICT];
            case 'duplicate_request':
                return ['message' => 'Duplicate request.', 'status' => Response::HTTP_CONFLICT];
            case 'internal_error':
                return ['message' => 'An unexpected internal error occurred.', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
            case 'throttle_violation':
                return ['message' => 'Rate limit exceeded.', 'status' => Response::HTTP_BAD_GATEWAY];
            case 'temporarily_unavailable':
                return ['message' => 'API is temporarily unavailable. Please try again later.', 'status' => Response::HTTP_SERVICE_UNAVAILABLE];
            default:
                return ['message' => 'An unexpected error occurred.', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }
}
