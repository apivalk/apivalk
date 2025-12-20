<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Util;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Util\ClassLocator;

class ClassLocatorTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'apivalk_locator_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDir($this->tempDir);
        }
    }

    private function removeDir(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            (is_dir($path)) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testFindClasses(): void
    {
        $subDir = $this->tempDir . DIRECTORY_SEPARATOR . 'Sub';
        mkdir($subDir);
        
        $file1 = $this->tempDir . DIRECTORY_SEPARATOR . 'Class1.php';
        $file2 = $subDir . DIRECTORY_SEPARATOR . 'Class2.php';
        
        file_put_contents($file1, '<?php namespace ApivalkTest; class Class1 {}');
        file_put_contents($file2, '<?php namespace ApivalkTest\Sub; class Class2 {}');
        
        // We need to declare the classes so ClassLocator can find them via get_declared_classes()
        // since it doesn't parse files but scans them and checks get_declared_classes().
        // Actually, it uses class_exists($className) which triggers autoloading.
        // We can mock the autoloading by just defining them.
        
        eval('namespace ApivalkTest; class Class1 {}');
        eval('namespace ApivalkTest\Sub; class Class2 {}');

        $locator = new ClassLocator($this->tempDir, 'ApivalkTest');
        $classes = $locator->findClasses();
        
        $this->assertCount(2, $classes);
        
        $classNames = array_column($classes, 'className');
        $this->assertContains('ApivalkTest\\Class1', $classNames);
        $this->assertContains('ApivalkTest\\Sub\\Class2', $classNames);
    }
}
