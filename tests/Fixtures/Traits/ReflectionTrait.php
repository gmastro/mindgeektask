<?php

/**
 * Trait ReflectionTrait | File ./tests/Fixtures/Traits/ReflectionTrait.php
 *
 * Various reflection methods to expose content
 * 
 * @package     Tests
 * @subpackage  Fixtures
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Components/AtomFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Traits;

/**
 * Error Code Trait
 *
 * Will store various error codes in case that something went wrong
 * 
 * @category    Fixtures
 * @package     Tests
 * @version     0.0.1
 */
trait ReflectionTrait
{
    /**
     * Accessibility
     *
     * Change the accessibility of the method to public
     *
     * @final
     * @access  private
     * @param   \ReflectionProperty|\ReflectionMethod $reflection
     * @return  \ReflectionProperty|\ReflectionMethod
     */
    final private function setAccessibility(
        \ReflectionProperty|\ReflectionMethod $reflection
    ): \ReflectionProperty|\ReflectionMethod {
        if (false === $reflection->isPublic()) {
            $reflection->setAccessible(true);
        }

        return $reflection;
    }

    /**
     * Expose Property
     *
     * It will expose and set given value to the property
     *
     * @final
     * @access  public
     * @param   object $sut System Under Test, to expose content
     * @param   string $property Property to expose and set new content
     * @param   mixed $value Value to set to property
     * @return  void
     * @throws  \ReflectionException
     */
    final public function propertySet(object &$sut, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($sut, $property);
        $reflection = $this->setAccessibility($reflection);
        $reflection->setValue($sut, $value);
    }

    /**
     * Expose Property
     *
     * Changes accessibility and returns content of property
     *
     * @final
     * @access  public
     * @param   object $sut System Under Test, to expose content
     * @param   string $property Property to expose and set new content
     * @return  mixed
     * @throws  \ReflectionException
     */
    public function propertyGet(object &$sut, string $property): mixed
    {
        $reflection = new \ReflectionProperty($sut, $property);
        $reflection = $this->setAccessibility($reflection);
        return $reflection->getValue($sut);
    }

    /**
     * Expose Method
     *
     * It will expose and will return the content from the given property
     *
     * @final
     * @access  public
     * @param   object $sut System Under Test, to expose content
     * @param   string $method Method to expose
     * @param   array<int, mixed> $args **Default `[]`**, set of arguments required by the method
     * @return  mixed
     * @throws  \ReflectionException
     */
    final public function methodSet(object &$sut, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionMethod($sut, $method);
        $reflection = $this->setAccessibility($reflection);
        return $reflection->invokeArgs($reflection->isStatic() ? null : $sut, $args);
    }
}
