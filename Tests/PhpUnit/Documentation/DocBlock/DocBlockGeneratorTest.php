<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\DocBlock;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\DocBlock\DocBlockGenerator;
use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Documentation\ApivalkRequestDocumentation;

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
        $this->assertFileExists($this->tempDir . '/Request/Shape/' . $uniqueRequestClassName . 'OrderingShape.php');
    }

    public function testRunInvalidDirectory(): void
    {
        $this->expectException(\RuntimeException::class);
        $generator = new DocBlockGenerator();
        $generator->run('/invalid/path', 'Namespace');
    }
}
