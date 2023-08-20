<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Interfaces\BuilderDtoServiceInterface;
use App\Models\Balance;
use Tests\DataProviders\BuilderDtoServiceDataProvider;
use Tests\TestCase;

class BuilderDtoServiceTest extends TestCase
{
    use BuilderDtoServiceDataProvider;

    private int $senderId = 1;
    private int $recipientId = 2;

    /**
     * @dataProvider providerCases
     */
    public function testCreate(array $case): void
    {
        Balance::factory(['user_id' => $this->senderId])->create();
        Balance::factory(['user_id' => $this->recipientId])->create();

        $service = $this->app->make(BuilderDtoServiceInterface::class);
        $dto = $service->create($case);

        $this->assertNotNull($dto);
        $this->assertEmpty($service->getErrors());
    }

    /**
     * @dataProvider providerCasesFailed
     */
    public function testCreateFailed(array $case): void
    {
        Balance::factory(['user_id' => $this->senderId])->create();
        Balance::factory(['user_id' => $this->recipientId])->create();

        $service = $this->app->make(BuilderDtoServiceInterface::class);
        $dto = $service->create($case);

        $this->assertNull($dto);
        $this->assertNotEmpty($service->getErrors());
    }
}
