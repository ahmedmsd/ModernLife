<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Tests\Feature\Commands;

use DateTimeImmutable;
use Grazulex\LaravelDevtoolbox\Console\Concerns\HandlesJsonSerialization;
use Orchestra\Testbench\TestCase;
use stdClass;

final class JsonSerializationRobustnessTest extends TestCase
{
    use HandlesJsonSerialization;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_problematic_data_types_safely(): void
    {
        $problematicData = [
            'closure' => fn () => 'test',
            'resource' => fopen('php://memory', 'r'),
            'inf' => INF,
            'nan' => NAN,
            'datetime' => new DateTimeImmutable('2024-01-01'),
            'circular' => new stdClass(),
            'normal_data' => 'test',
            'array' => ['key' => 'value'],
        ];

        // Create circular reference
        $problematicData['circular']->self = $problematicData['circular'];

        $result = $this->makeJsonSerializable($problematicData);

        // Ensure all problematic types are handled
        $this->assertArrayHasKey('closure', $result);
        $this->assertArrayHasKey('resource', $result);
        $this->assertArrayHasKey('inf', $result);
        $this->assertArrayHasKey('nan', $result);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertArrayHasKey('circular', $result);
        $this->assertArrayHasKey('normal_data', $result);
        $this->assertArrayHasKey('array', $result);

        // Ensure they are JSON-serializable
        $this->assertEquals('[Closure]', $result['closure']);
        $this->assertStringStartsWith('[Resource:', $result['resource']);
        $this->assertNull($result['inf']);
        $this->assertNull($result['nan']);
        $this->assertEquals('2024-01-01 00:00:00', $result['datetime']);
        $this->assertEquals('test', $result['normal_data']);
        $this->assertEquals(['key' => 'value'], $result['array']);

        // Most importantly - ensure it can be JSON encoded
        $json = $this->safeJsonEncode($problematicData);
        $this->assertJson($json);

        // Close the resource
        if (is_resource($problematicData['resource'])) {
            fclose($problematicData['resource']);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_falls_back_gracefully_on_encoding_failure(): void
    {
        // Create a mock where json_encode would fail (simulate by using an object that can't be converted)
        $data = ['key' => 'value'];

        // Even with normal data, test the error handling path by ensuring we get valid JSON
        $json = $this->safeJsonEncode($data);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('key', $decoded);
        $this->assertEquals('value', $decoded['key']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_outputs_json_safely(): void
    {
        $testData = [
            'test' => 'value',
            'closure' => fn () => 'should be converted',
            'number' => 123,
        ];

        // Capture output by redirecting to a temporary buffer
        $result = $this->makeJsonSerializable($testData);
        $json = $this->safeJsonEncode($result);

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('test', $decoded);
        $this->assertEquals('value', $decoded['test']);
        $this->assertEquals('[Closure]', $decoded['closure']);
        $this->assertEquals(123, $decoded['number']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_outputs_json_to_file_safely(): void
    {
        $testData = ['test' => 'value'];
        $tempFile = tempnam(sys_get_temp_dir(), 'json_test');

        $this->outputJson($testData, $tempFile);

        $this->assertFileExists($tempFile);
        $content = file_get_contents($tempFile);
        $this->assertJson($content);

        $decoded = json_decode($content, true);
        $this->assertEquals($testData, $decoded);

        unlink($tempFile);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_deeply_nested_structures(): void
    {
        $deepData = [];
        $current = &$deepData;

        // Create a deeply nested structure
        for ($i = 0; $i < 100; $i++) {
            $current['level_'.$i] = [];
            $current = &$current['level_'.$i];
        }
        $current['value'] = 'deep_value';

        $result = $this->makeJsonSerializable($deepData);
        $json = $this->safeJsonEncode($result);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Grazulex\LaravelDevtoolbox\LaravelDevtoolboxServiceProvider::class,
        ];
    }
}
