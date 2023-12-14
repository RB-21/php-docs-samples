<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\HelloLogging\Test;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\TestUtils\DeploymentTrait;
use Google\Cloud\TestUtils\TestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class DeployTest.
 *
 * This test is not run by the CI system.
 *
 * To skip deployment of a new function, run with "GOOGLE_SKIP_DEPLOYMENT=true".
 * To skip deletion of the tested function, run with "GOOGLE_KEEP_DEPLOYMENT=true".
 * @group deploy
 */
class DeployTest extends TestCase
{
    use DeploymentTrait;
    use TestCasesTrait;
    use TestTrait;

    private static $entryPoint = 'helloLogging';

    public function testFunction(): void
    {
        foreach (self::cases() as $test) {
            $targetAudience = self::getBaseUri();
            // create middleware
            $middleware = ApplicationDefaultCredentials::getIdTokenMiddleware($targetAudience);
            $stack = HandlerStack::create();
            $stack->push($middleware);

            // create the HTTP client
            $client = new Client([
                'handler' => $stack,
                'auth' => 'google_auth',
                'base_uri' => $targetAudience,
            ]);

            // Send a request to the function..
            $resp = $this->client->get('');

            // Assert status code.
            $this->assertEquals('200', $resp->getStatusCode());

            // Assert function output.
            $output = trim((string) $resp->getBody());

            if (isset($test['not_contains'])) {
                $this->assertStringNotContainsString($test['not_contains'], $output);
            }
        }
    }
}
