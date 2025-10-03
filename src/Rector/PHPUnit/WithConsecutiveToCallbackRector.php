<?php

declare(strict_types=1);

namespace Simtel\RectorRules\Rector\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replace deprecated PHPUnit withConsecutive() method with willReturnCallback()
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/5063
 */
final class WithConsecutiveToCallbackRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated PHPUnit withConsecutive() method with willReturnCallback()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$mock = $this->createMock(SomeClass::class);
$mock->expects($this->exactly(2))
    ->method('someMethod')
    ->withConsecutive(
        ['first'],
        ['second']
    );
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$mock = $this->createMock(SomeClass::class);
$mock->expects($this->exactly(2))
    ->method('someMethod')
    ->willReturnCallback(function ($parameters) {
        static $callCount = 0;
        $callCount++;
        
        if ($callCount === 1) {
            $this->assertSame(['first'], $parameters);
        }
        
        if ($callCount === 2) {
            $this->assertSame(['second'], $parameters);
        }
    });
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // Check if this is a withConsecutive method call
        if (!$this->isName($node->name, 'withConsecutive')) {
            return null;
        }

        // Check if this is a method call on a method() call which is on an expects() call
        if (!$node->var instanceof MethodCall) {
            return null;
        }

        // Check if the parent call is method()
        $methodCall = $node->var;
        if (!$this->isName($methodCall->name, 'method')) {
            return null;
        }

        // Check if the grandparent call is expects()
        if (!$methodCall->var instanceof MethodCall) {
            return null;
        }

        $expectsCall = $methodCall->var;
        if (!$this->isName($expectsCall->name, 'expects')) {
            return null;
        }

        // Create the callback function
        $callback = $this->createCallback($node->args, []);

        // Create a new method call with willReturnCallback instead of withConsecutive
        // We need to preserve the method() call and just replace the withConsecutive() part
        $newWithConsecutiveCall = new MethodCall(
            $methodCall->var, // This is the method() call's var (expects() call)
            new Identifier('method'),
            $methodCall->args  // Preserve the method() call arguments
        );

        // Add the willReturnCallback call to the chain
        $newMethodCall = new MethodCall(
            $newWithConsecutiveCall,
            new Identifier('willReturnCallback'),
            [$this->nodeFactory->createArg($callback)]
        );

        return $newMethodCall;
    }

    /**
     * Create the callback function with assertions for each consecutive call
     *
     * @param array<Arg|VariadicPlaceholder> $consecutiveArgs
     * @param array<null|Expr> $returnValues
     * @return Closure
     */
    private function createCallback(array $consecutiveArgs, array $returnValues): Node\Expr\Closure
    {
        // Create static variable declaration
        $staticVar = new Node\Stmt\Static_([
            new Node\Stmt\StaticVar(
                new Variable('callCount'),
                new LNumber(0)
            ),
        ]);

        // Create increment statement
        $increment = new Node\Expr\PostInc(
            new Variable('callCount')
        );

        $statements = [
            $staticVar,
            new Expression($increment),
        ];

        foreach ($consecutiveArgs as $index => $arg) {
            // Create if statement for each call
            $condition = new Node\Expr\BinaryOp\Identical(
                new Variable('callCount'),
                new LNumber($index + 1)
            );

            // Create assertion
            $assertion = new Node\Expr\MethodCall(
                new Variable('this'),
                new Identifier('assertSame'),
                [
                    $this->nodeFactory->createArg($arg->value ?? null),
                    $this->nodeFactory->createArg(new Variable('parameters')),
                ]
            );

            $innerStatements = [new Expression($assertion)];

            // Add return statement if we have return values
            if (isset($returnValues[$index])) {
                $innerStatements[] = new Node\Stmt\Return_($returnValues[$index]);
            }

            $ifStatement = new Node\Stmt\If_(
                $condition,
                [
                    'stmts' => $innerStatements,
                ]
            );

            $statements[] = $ifStatement;
        }

        // Create the closure
        return new Node\Expr\Closure([
            'params' => [
                new Node\Param(
                    new Variable('parameters'),  // var
                    null,                        // default
                    null,                        // type
                    false,                       // byRef
                    false,                       // variadic
                    [],                          // attributes
                    0,                           // flags
                    [],                          // attrGroups
                    []                           // hooks
                ),
            ],
            'stmts' => $statements,
        ]);
    }
}
