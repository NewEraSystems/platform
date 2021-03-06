<?php

namespace Oro\Bundle\ApiBundle\Tests\Unit\Batch\Processor\UpdateItem;

use Oro\Bundle\ApiBundle\Batch\Processor\UpdateItem\NormalizeEntityClass;
use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\ApiBundle\Provider\ResourcesProvider;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;

class NormalizeEntityClassTest extends BatchUpdateItemProcessorTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ValueNormalizer */
    private $valueNormalizer;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ResourcesProvider */
    private $resourcesProvider;

    /** @var NormalizeEntityClass */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->valueNormalizer = $this->createMock(ValueNormalizer::class);
        $this->resourcesProvider = $this->createMock(ResourcesProvider::class);

        $this->processor = new NormalizeEntityClass(
            $this->valueNormalizer,
            $this->resourcesProvider
        );
    }

    public function testProcessWhenClassIsNotSet()
    {
        $this->processor->process($this->context);

        self::assertNull($this->context->getClassName());
    }

    public function testProcessWhenClassAlreadyNormalized()
    {
        $this->valueNormalizer->expects(self::never())
            ->method('normalizeValue');

        $this->context->setClassName('Test\Entity');
        $this->processor->process($this->context);

        self::assertEquals('Test\Entity', $this->context->getClassName());
    }

    public function testProcessWhenEntityClassIsNotNormalized()
    {
        $this->valueNormalizer->expects(self::once())
            ->method('normalizeValue')
            ->with('entity', DataType::ENTITY_CLASS, $this->context->getRequestType())
            ->willReturn('Test\Entity');
        $this->resourcesProvider->expects(self::once())
            ->method('isResourceAccessible')
            ->with('Test\Entity', $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn(true);

        $this->context->setClassName('entity');
        $this->processor->process($this->context);

        self::assertEquals('Test\Entity', $this->context->getClassName());
    }

    /**
     * @expectedException \Oro\Bundle\ApiBundle\Exception\ResourceNotAccessibleException
     */
    public function testProcessForNotAccessibleEntityType()
    {
        $this->valueNormalizer->expects(self::once())
            ->method('normalizeValue')
            ->with('entity', DataType::ENTITY_CLASS, $this->context->getRequestType())
            ->willReturn('Test\Entity');
        $this->resourcesProvider->expects(self::once())
            ->method('isResourceAccessible')
            ->with('Test\Entity', $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn(false);

        $this->context->setClassName('entity');
        $this->processor->process($this->context);
    }

    public function testProcessForInvalidEntityType()
    {
        $this->valueNormalizer->expects(self::once())
            ->method('normalizeValue')
            ->with('entity', DataType::ENTITY_CLASS, $this->context->getRequestType())
            ->willThrowException(new \Exception('some error'));

        $this->context->setClassName('entity');
        $this->processor->process($this->context);

        self::assertNull($this->context->getClassName());
        self::assertEquals(
            [
                Error::createValidationError('entity type constraint', 'Unknown entity type: entity.')
            ],
            $this->context->getErrors()
        );
    }
}
