# API StackOverflow

Este proyecto es una API que interactúa con StackOverflow para obtener preguntas basadas en etiquetas. Utiliza Symfony como framework y Doctrine como ORM para gestionar la base de datos.

## Requisitos

- PHP 8.1 o superior
- Composer
- Symfony CLI
- MySQL o MariaDB

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/javilm10/api_stackoverflow.git
   cd api_stackoverflow
   ```

2. Instala las dependencias:
   ```bash
   composer install
   ```

3. Configura tu archivo `.env` para la conexión a la base de datos:
   ```dotenv
   DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/nombre_de_la_base_de_datos"
   API_URL="https://api.stackexchange.com/2.3/questions"
   ```
Cambia `usuario`, `contraseña`, y `nombre_base_datos` por los valores correctos de tu entorno.


4. Crea y migra la base de datos:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

## Uso

Puedes realizar solicitudes a la API usando herramientas como Postman o directamente desde el navegador o vía curl. La ruta para obtener preguntas es:

```
GET /api/questions?tagged=<etiqueta>&fromdate=<fecha>&todate=<fecha>&forceRefresh=<true|false>
```

### Parámetros

- `tagged`: (obligatorio) La etiqueta que se utilizará para filtrar las preguntas.
- `fromdate`: (opcional) Fecha de inicio en formato `Y-m-d`.
- `todate`: (opcional) Fecha de fin en formato `Y-m-d`.
- `forceRefresh`: (opcional) Si se establece en `true`, forzará una actualización de la base de datos, haciendo una llamada a la API incluso si ya hay preguntas almacenadas en la base de datos.

## Estructura del Proyecto

- `src/Controller`: Controladores que gestionan las solicitudes.
- `src/Service`: Servicios que contienen la lógica de negocio.
- `src/Entity`: Entidades que representan las tablas de la base de datos.
- `src/Repository`: Clases para trabajar con la base de datos.

## Pruebas

Para ejecutar las pruebas unitarias, asegúrate de que estás en el directorio raíz del proyecto y usa el siguiente comando:

```bash
vendor/bin/phpunit
```

### Servicios Probados

- **ApiErrorHandler**: Maneja errores y excepciones específicas de la API. Se han creado pruebas unitarias para asegurarse de que devuelve los mensajes y códigos de estado esperados según el tipo de error.
  
- **QuestionService**: Maneja la interacción con la base de datos para las preguntas. Se han implementado pruebas para verificar el correcto funcionamiento de los métodos `findQuestionsByTag` y `saveQuestions`.

- **StackOverflowService**: Interactúa con la API de Stack Overflow para recuperar preguntas. Las pruebas aseguran que maneja correctamente las solicitudes y errores de la API.

### Controlador Probado

- **StackOverflowController**: Se han implementado pruebas unitarias para validar el comportamiento de las rutas de la API, especialmente el método `getQuestions`. Estas pruebas verifican el manejo de errores y la correcta respuesta de la API según diferentes escenarios. 

  Además, se ha añadido el parámetro `forceRefresh`, que permite forzar la actualización de la base de datos. Si ya hay preguntas en la base de datos y `forceRefresh` no está establecido o es `false`, la API devolverá los datos desde la base de datos. Si `forceRefresh` está establecido en `true`, se realizará una llamada a la API para obtener datos actualizados.

### Ejemplo de Pruebas

Aquí hay ejemplos de pruebas unitarias para cada uno de los componentes:

#### Pruebas para ApiErrorHandler
```php
public function testHandle()
{
    $exception = new \Exception('Error fetching data from Stack Overflow API.');
    $result = $this->apiErrorHandler->handle($exception);
    $this->assertSame('Error fetching data from API.', $result['message']);
    $this->assertSame(Response::HTTP_BAD_GATEWAY, $result['status']);
}
```

#### Pruebas para QuestionService
```php
public function testFindQuestionsByTag()
{
    // Configurar el comportamiento del repositorio
    $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);
    $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
    $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
    // Otros métodos del query builder...

    // Llamar al método
    $questions = $this->questionService->findQuestionsByTag('php');

    // Afirmaciones
    $this->assertIsArray($questions);
    // Otras afirmaciones según lo que esperas
}
```

#### Pruebas para StackOverflowController
```php
public function testGetQuestionsFromDatabase()
{
    $request = new Request(['tagged' => 'php', 'forceRefresh' => false]);
    $this->questionService->method('findQuestionsByTag')->willReturn([$this->mockQuestion]);

    $response = $this->controller->getQuestions($request);

    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame('db', json_decode($response->getContent(), true)['source']);
}
```

### Ejecutando Pruebas Individuales

Si deseas ejecutar una prueba específica, puedes hacerlo usando:

```bash
vendor/bin/phpunit tests/Service/ApiErrorHandlerTest.php
```

## Contribuciones

Las contribuciones son bienvenidas. Si deseas contribuir a este proyecto, por favor crea un "fork" del repositorio y envía un "pull request" con tus cambios.

## Licencia

Este proyecto está licenciado bajo la MIT License. Consulta el archivo LICENSE para más información.

