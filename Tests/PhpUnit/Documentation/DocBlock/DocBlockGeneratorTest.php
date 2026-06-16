<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use apivalk\apivalk\Documentation\DocBlock\DocBlockGenerator;
use PHPUnit\Framework\TestCase;

class DocBlockGeneratorTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/apivalk_docblock_test_' . uniqid('', true);
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/Request');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $path): void
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $this->removeDirectory($path . '/' . $file);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    public function testRun(): void
    {
        $uniqueRequestClassName = 'TestRequest_' . str_replace('.', '_', uniqid('', true));
        $requestFile = $this->tempDir . '/Request/' . $uniqueRequestClassName . '.php';
        $requestContent = <<<PHP
<?php

namespace TestNamespace\Request;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;

class {$uniqueRequestClassName} extends AbstractApivalkRequest
{
    public static function getDocumentation(): ApivalkRequestDocumentation
    {
        return new ApivalkRequestDocumentation();
    }
}
PHP;
        file_put_contents($requestFile, $requestContent);
        require_once $requestFile;

        $uniqueControllerClassName = 'TestController_' . str_replace('.', '_', uniqid('', true));
        $controllerFile = $this->tempDir . '/' . $uniqueControllerClassName . '.php';
        $controllerContent = <<<PHP
<?php

namespace TestNamespace;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Method\GetMethod;use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Router\Route\Route;
use TestNamespace\Request\\{$uniqueRequestClassName};

class {$uniqueControllerClassName} extends AbstractApivalkController
{
    public static function getRoute(): Route { return new Route('', new GetMethod(), ''); }
    public static function getRequestClass(): string { return {$uniqueRequestClassName}::class; }
    public static function getResponseClasses(): array { return []; }
    public function __invoke(ApivalkRequestInterface \$request): AbstractApivalkResponse { throw new \Exception(); }
}
PHP;
        file_put_contents($controllerFile, $controllerContent);
        require_once $controllerFile;

        $generator = new DocBlockGenerator();
        
        // We need to use output buffering because the generator echoes stuff
        ob_start();
        $generator->run($this->tempDir, 'TestNamespace');
        $output = ob_get_clean();

        $this->assertStringContainsString('✔ DocBlocks & Shapes generated', $output);
        $this->assertStringContainsString($uniqueRequestClassName, $output);
        $this->assertStringContainsString($uniqueControllerClassName, $output);
        
        // Verify request file was updated
        $updatedContent = file_get_contents($requestFile);
        $this->assertStringContainsString('/**', $updatedContent);
        $this->assertStringContainsString('@method', $updatedContent);

        // Verify shape files were created
        $this->assertFileExists($this->tempDir . '/Request/Shape/' . $uniqueRequestClassName . 'PathShape.php');
        $this->assertFileExists($this->tempDir . '/Request/Shape/' . $uniqueRequestClassName . 'QueryShape.php');
        $this->assertFileExists($this->tempDir . '/Request/Shape/' . $uniqueRequestClassName . 'BodyShape.php');
        $this->assertFileExists($this->tempDir . '/Request/Shape/' . $uniqueRequestClassName . 'SortingShape.php');
    }

    public function testRunInvalidDirectory(): void
    {
        $this->expectException(\RuntimeException::class);
        $generator = new DocBlockGenerator();
        $generator->run('/invalid/path', 'Namespace');
    }

    public function testRunGeneratesResourcePropertyDocBlocks(): void
    {
        $resourceDir = $this->tempDir . '/Resource';
        mkdir($resourceDir);

        $resourceClassName = 'TestResource_' . str_replace('.', '_', uniqid('', true));
        $resourceFile = $resourceDir . '/' . $resourceClassName . '.php';
        $resourceContent = <<<PHP
<?php

namespace TestNamespace\\Resource;

use apivalk\\apivalk\\Documentation\\Property\\FloatProperty;
use apivalk\\apivalk\\Documentation\\Property\\StringProperty;
use apivalk\\apivalk\\Resource\\AbstractResource;

class {$resourceClassName} extends AbstractResource
{
    public function getName(): string { return 'thing'; }
    public function excludeFromMode(string \$mode): array { return []; }

    protected function init(): void
    {
        \$this->addProperty(new StringProperty('thing_uuid', 'Identifier of the thing'));
        \$this->addProperty(new StringProperty('name', 'Name of the thing'));
        \$this->addProperty((new FloatProperty('weight'))->setIsRequired(false));
    }
}
PHP;
        file_put_contents($resourceFile, $resourceContent);
        require_once $resourceFile;

        $controllerClassName = 'TestResourceController_' . str_replace('.', '_', uniqid('', true));
        $controllerFile = $this->tempDir . '/' . $controllerClassName . '.php';
        $controllerContent = <<<PHP
<?php

namespace TestNamespace;

use apivalk\\apivalk\\Http\\Controller\\Resource\\AbstractListResourceController;
use apivalk\\apivalk\\Http\\Request\\ApivalkRequestInterface;
use apivalk\\apivalk\\Http\\Response\\AbstractApivalkResponse;
use apivalk\\apivalk\\Http\\Response\\Resource\\ResourceListResponse;
use apivalk\\apivalk\\Http\\Response\\Pagination\\PagePaginationResponse;
use apivalk\\apivalk\\Router\\Route\\Route;
use TestNamespace\\Resource\\{$resourceClassName};

class {$controllerClassName} extends AbstractListResourceController
{
    protected static function buildRoute(): Route
    {
        return Route::get('/api/v1/things');
    }

    public static function getResourceClass(): string
    {
        return {$resourceClassName}::class;
    }

    public function __invoke(ApivalkRequestInterface \$request): AbstractApivalkResponse
    {
        return new ResourceListResponse([], new PagePaginationResponse(1, 25, false, 0));
    }
}
PHP;
        file_put_contents($controllerFile, $controllerContent);
        require_once $controllerFile;

        $generator = new DocBlockGenerator();

        ob_start();
        $generator->run($this->tempDir, 'TestNamespace');
        $output = ob_get_clean();

        $this->assertStringContainsString('Resource @property docblocks generated', $output);
        $this->assertStringContainsString($resourceClassName, $output);

        $updatedResource = file_get_contents($resourceFile);
        $this->assertStringContainsString('@property string $thing_uuid', $updatedResource);
        $this->assertStringContainsString('@property string $name', $updatedResource);
        $this->assertStringContainsString('@property float|null $weight', $updatedResource);
    }

    public function testRunGeneratesSimpleArrayPropertyDocBlocks(): void
    {
        $resourceDir = $this->tempDir . '/Resource';
        mkdir($resourceDir);

        $resourceClassName = 'TestArrayResource_' . str_replace('.', '_', uniqid('', true));
        $resourceFile = $resourceDir . '/' . $resourceClassName . '.php';
        $resourceContent = <<<PHP
<?php

namespace TestNamespace\\Resource;

use apivalk\\apivalk\\Documentation\\Property\\SimpleArrayProperty;
use apivalk\\apivalk\\Documentation\\Property\\StringProperty;
use apivalk\\apivalk\\Resource\\AbstractResource;

class {$resourceClassName} extends AbstractResource
{
    public function getName(): string { return 'thing'; }
    public function excludeFromMode(string \$mode): array { return []; }

    protected function init(): void
    {
        \$this->addProperty(new StringProperty('thing_uuid', 'Identifier of the thing'));
        \$this->addProperty(new SimpleArrayProperty('tags', 'Free-form tags', SimpleArrayProperty::TYPE_STRING));
        \$this->addProperty(
            (new SimpleArrayProperty('attachment_ids', 'Attached document IDs', SimpleArrayProperty::TYPE_INT))
                ->setIsRequired(false)
        );
        \$this->addProperty(new SimpleArrayProperty('scores', 'Scores', SimpleArrayProperty::TYPE_NUMBER));
        \$this->addProperty(new SimpleArrayProperty('flags', 'Flags', SimpleArrayProperty::TYPE_BOOL));
    }
}
PHP;
        file_put_contents($resourceFile, $resourceContent);
        require_once $resourceFile;

        $controllerClassName = 'TestArrayResourceController_' . str_replace('.', '_', uniqid('', true));
        $controllerFile = $this->tempDir . '/' . $controllerClassName . '.php';
        $controllerContent = <<<PHP
<?php

namespace TestNamespace;

use apivalk\\apivalk\\Http\\Controller\\Resource\\AbstractListResourceController;
use apivalk\\apivalk\\Http\\Request\\ApivalkRequestInterface;
use apivalk\\apivalk\\Http\\Response\\AbstractApivalkResponse;
use apivalk\\apivalk\\Http\\Response\\Resource\\ResourceListResponse;
use apivalk\\apivalk\\Http\\Response\\Pagination\\PagePaginationResponse;
use apivalk\\apivalk\\Router\\Route\\Route;
use TestNamespace\\Resource\\{$resourceClassName};

class {$controllerClassName} extends AbstractListResourceController
{
    protected static function buildRoute(): Route
    {
        return Route::get('/api/v1/things');
    }

    public static function getResourceClass(): string
    {
        return {$resourceClassName}::class;
    }

    public function __invoke(ApivalkRequestInterface \$request): AbstractApivalkResponse
    {
        return new ResourceListResponse([], new PagePaginationResponse(1, 25, false, 0));
    }
}
PHP;
        file_put_contents($controllerFile, $controllerContent);
        require_once $controllerFile;

        $generator = new DocBlockGenerator();

        ob_start();
        $generator->run($this->tempDir, 'TestNamespace');
        ob_get_clean();

        $updatedResource = file_get_contents($resourceFile);
        $this->assertStringContainsString('@property string[] $tags Free-form tags', $updatedResource);
        $this->assertStringContainsString('@property int[]|null $attachment_ids Attached document IDs', $updatedResource);
        $this->assertStringContainsString('@property float[] $scores Scores', $updatedResource);
        $this->assertStringContainsString('@property bool[] $flags Flags', $updatedResource);
    }

    public function testRunOnlyProcessesEachResourceClassOnce(): void
    {
        $resourceDir = $this->tempDir . '/Resource';
        mkdir($resourceDir);

        $resourceClassName = 'SharedResource_' . str_replace('.', '_', uniqid('', true));
        $resourceFile = $resourceDir . '/' . $resourceClassName . '.php';
        $resourceContent = <<<PHP
<?php

namespace TestNamespace\\Resource;

use apivalk\\apivalk\\Documentation\\Property\\StringProperty;
use apivalk\\apivalk\\Resource\\AbstractResource;

class {$resourceClassName} extends AbstractResource
{
    public function getName(): string { return 'shared'; }
    public function excludeFromMode(string \$mode): array { return []; }

    protected function init(): void
    {
        \$this->addProperty(new StringProperty('shared_uuid'));
        \$this->addProperty(new StringProperty('name'));
    }
}
PHP;
        file_put_contents($resourceFile, $resourceContent);
        require_once $resourceFile;

        $listClassName = 'SharedListController_' . str_replace('.', '_', uniqid('', true));
        $viewClassName = 'SharedViewController_' . str_replace('.', '_', uniqid('', true));

        $listFile = $this->tempDir . '/' . $listClassName . '.php';
        $viewFile = $this->tempDir . '/' . $viewClassName . '.php';

        $listContent = <<<PHP
<?php

namespace TestNamespace;

use apivalk\\apivalk\\Http\\Controller\\Resource\\AbstractListResourceController;
use apivalk\\apivalk\\Http\\Request\\ApivalkRequestInterface;
use apivalk\\apivalk\\Http\\Response\\AbstractApivalkResponse;
use apivalk\\apivalk\\Http\\Response\\Resource\\ResourceListResponse;
use apivalk\\apivalk\\Http\\Response\\Pagination\\PagePaginationResponse;
use apivalk\\apivalk\\Router\\Route\\Route;
use TestNamespace\\Resource\\{$resourceClassName};

class {$listClassName} extends AbstractListResourceController
{
    protected static function buildRoute(): Route { return Route::get('/api/v1/shareds'); }
    public static function getResourceClass(): string { return {$resourceClassName}::class; }
    public function __invoke(ApivalkRequestInterface \$request): AbstractApivalkResponse
    {
        return new ResourceListResponse([], new PagePaginationResponse(1, 25, false, 0));
    }
}
PHP;

        $viewContent = <<<PHP
<?php

namespace TestNamespace;

use apivalk\\apivalk\\Http\\Controller\\Resource\\AbstractViewResourceController;
use apivalk\\apivalk\\Http\\Request\\ApivalkRequestInterface;
use apivalk\\apivalk\\Http\\Response\\AbstractApivalkResponse;
use apivalk\\apivalk\\Http\\Response\\Resource\\ResourceViewResponse;
use apivalk\\apivalk\\Router\\Route\\Route;
use apivalk\\apivalk\\Documentation\\Property\\StringProperty;
use TestNamespace\\Resource\\{$resourceClassName};

class {$viewClassName} extends AbstractViewResourceController
{
    protected static function buildRoute(): Route {
        return Route::get('/api/v1/shareds/{shared_uuid}')
            ->pathProperty(new StringProperty('shared_uuid', 'Shared UUID'));
    }
    public static function getResourceClass(): string { return {$resourceClassName}::class; }
    public function __invoke(ApivalkRequestInterface \$request): AbstractApivalkResponse
    {
        return new ResourceViewResponse(new {$resourceClassName}());
    }
}
PHP;

        file_put_contents($listFile, $listContent);
        file_put_contents($viewFile, $viewContent);
        require_once $listFile;
        require_once $viewFile;

        $generator = new DocBlockGenerator();

        ob_start();
        $generator->run($this->tempDir, 'TestNamespace');
        $output = ob_get_clean();

        // Match line-by-line so "generated for X (from Y)" is counted once per resource class
        $resourceLines = preg_grep('/Resource @property docblocks generated for /', explode("\n", $output));

        $this->assertCount(
            1,
            $resourceLines,
            'Resource shared by multiple controllers must only be processed once.'
        );
    }
}
