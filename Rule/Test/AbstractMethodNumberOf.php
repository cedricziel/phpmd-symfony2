<?php

namespace MS\PHPMD\Rule\Test;

use PHPMD\AbstractRule;
use PHPMD\Node\ClassNode;
use PHPMD\Node\MethodNode;
use PHPMD\Rule\ClassAware;
use PHPMD\AbstractNode;

/**
 * Class AbstractMethodNumberOf
 *
 * @package MS\PHPMD\Rule\CleanCode
 */
abstract class AbstractMethodNumberOf extends AbstractRule implements ClassAware
{
    /**
     * @var array
     */
    private $names;

    /**
     * @var bool
     */
    private $match;

    /**
     * @param AbstractNode $node
     */
    public function apply(AbstractNode $node)
    {
        if (!$node instanceof ClassNode) {
            return;
        }

        if (false === $this->isTest($node)) {
            return;
        }

        $this->names = explode($this->getStringProperty('delimiter'), $this->getStringProperty('names'));
        $this->match = $this->getBooleanProperty('match');
        $number = $this->getIntProperty('number');

        foreach ($node->getMethods() as $method) {
            $count = $this->count($method);

            if ($number < $count) {
                $this->addViolation($method, [$count, $number]);
            }
        }
    }

    /**
     * @param ClassNode $node
     *
     * @return bool
     */
    private function isTest(ClassNode $node)
    {
        if ('Test' === substr($node->getImage(), -4, 4)) {
            return true;
        }

        return false;
    }

    /**
     * @param MethodNode $node
     *
     * @return int
     */
    private function count(MethodNode $node)
    {
        $count = 0;
        $methodPostfixes = $node->findChildrenOfType('MethodPostfix');

        foreach ($methodPostfixes as $methodPostfix) {
            if (true === $this->isMethodPostfixInNames($methodPostfix)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param AbstractNode $methodPostfix
     *
     * @return bool
     */
    private function isMethodPostfixInNames(AbstractNode $methodPostfix)
    {
        if (true === $this->match) {
            return in_array($methodPostfix->getImage(), $this->names);
        }

        return $this->isMethodPostfixContainsNames($methodPostfix);
    }

    /**
     * @param AbstractNode $methodPostfix
     *
     * @return bool
     */
    private function isMethodPostfixContainsNames(AbstractNode $methodPostfix)
    {
        foreach ($this->names as $name) {
            if (false !== strpos($methodPostfix->getImage(), $name)) {
                return true;
            }
        }

        return false;
    }
}
