<?php

declare(strict_types=1);

namespace Simtel\RectorRules\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RenameFindAndGetMethodCallRector extends AbstractRector
{
    /**
     * @throws PoorDocumentationException
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename find* methods to get* when they return only entity (not nullable)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class UserRepository
{
    public function findUserById(int $id): User
    {
        // ...
    }
    
    public function findUserByEmail(string $email): ?User
    {
        // ...
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class UserRepository
{
    public function getUserById(int $id): User
    {
        // ...
    }
    
    public function findUserByEmail(string $email): ?User
    {
        // ...
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassMethod) {
            return null;
        }


        $methodName = $this->getName($node);
        if (!$methodName || !str_starts_with($methodName, 'find')) {
            return null;
        }

        $returnType = $node->getReturnType();
        if (!$returnType) {
            return null;
        }

        if ($returnType instanceof NullableType) {
            return null;
        }
        if ($returnType instanceof UnionType) {
            return null;
        }

        if (!$returnType instanceof Name) {
            return null;
        }

        $returnTypeName = $returnType->toString();

        $primitiveTypes = ['int', 'string', 'bool', 'float', 'array', 'object', 'mixed', 'void'];
        if (in_array(strtolower($returnTypeName), $primitiveTypes, true)) {
            return null;
        }

        $newMethodName = 'get' . substr($methodName, 4); // Заменяем "find" на "get"

        $node->name->name = $newMethodName;

        return $node;
    }
}