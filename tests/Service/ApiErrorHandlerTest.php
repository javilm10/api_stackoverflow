<?php

namespace App\Tests\Service;

use App\Service\ApiErrorHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiErrorHandlerTest extends TestCase
{
    private ApiErrorHandler $apiErrorHandler;

    protected function setUp(): void
    {
        $this->apiErrorHandler = new ApiErrorHandler();
    }

    /**
     * @dataProvider errorDataProvider
     */
    public function testHandle($exceptionMessage, $expectedMessage, $expectedStatus)
    {
        // Crear una excepción con el mensaje proporcionado
        $exception = new \Exception($exceptionMessage);
        
        // Llamar al método handle y obtener el resultado
        $result = $this->apiErrorHandler->handle($exception);
        
        // Verificar que el resultado coincida con las expectativas
        $this->assertSame($expectedMessage, $result['message']);
        $this->assertSame($expectedStatus, $result['status']);
    }

    public static function errorDataProvider(): array 
    {
        return [
            ['Error fetching data from Stack Overflow API.', 'Error fetching data from API.', Response::HTTP_BAD_GATEWAY],
            ['bad_parameter', 'Invalid parameters were passed to the API.', Response::HTTP_BAD_REQUEST],
            ['access_token_required', 'Access token is required.', Response::HTTP_UNAUTHORIZED],
            ['invalid_access_token', 'Invalid access token.', Response::HTTP_UNAUTHORIZED],
            ['access_denied', 'Access denied due to insufficient permissions.', Response::HTTP_FORBIDDEN],
            ['no_method', 'The requested method does not exist.', Response::HTTP_NOT_FOUND],
            ['key_required', 'An application key is required.', Response::HTTP_METHOD_NOT_ALLOWED],
            ['access_token_compromised', 'The access token is no longer secure.', Response::HTTP_FORBIDDEN],
            ['write_failed', 'Write operation was rejected.', Response::HTTP_CONFLICT],
            ['duplicate_request', 'Duplicate request.', Response::HTTP_CONFLICT],
            ['internal_error', 'An unexpected internal error occurred.', Response::HTTP_INTERNAL_SERVER_ERROR],
            ['throttle_violation', 'Rate limit exceeded.', Response::HTTP_BAD_GATEWAY],
            ['temporarily_unavailable', 'API is temporarily unavailable. Please try again later.', Response::HTTP_SERVICE_UNAVAILABLE],
            ['some_other_error', 'An unexpected error occurred.', Response::HTTP_INTERNAL_SERVER_ERROR],
        ];
    }
}
