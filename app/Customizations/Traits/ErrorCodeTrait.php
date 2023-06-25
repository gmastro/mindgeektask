<?php

/**
 * Trait ErrorCodeTrait | File ./src/Customizations/Traits/ErrorCodeTrait.php
 *
 * Captures error code values, usable during exception throwing or when the response
 * from various instances is a vague false
 */

declare(strict_types=1);

namespace App\Customizations\Traits;

use App\Customizations\Components\interfaces\InterfaceErrorCodes;

/**
 * Error Code Trait
 *
 * Will store various error codes in case that something went wrong
 */
trait ErrorCodeTrait
{
    /**
     * Error Code
     *
     * Holds various error codes to determine cases which either information retrieval or content retrieval failed
     *
     * @access  protected
     * @var     int $errorCode
     */
    protected $errorCode = InterfaceErrorCodes::NONE;

    /**
     * Error Code Batch
     *
     * Holds all those error codes before terminating the instance
     */
    protected $errorBatch = [];

    /**
     * Error Code Setter
     *
     * Stores latest error code value.
     * In addition, it will store, if satisfied by the condition unique error codes.
     * All available error codes are defined at {@see App\Customizations\Components\interfaces\InterfaceErrorCodes}
     *
     * @access  public
     * @param   int $errorCode Code identifier to add
     * @param   bool $condition **Default `true`**, adds the code only if a condition is true
     * @return  bool
     */
    protected function setErrorCode(int $errorCode, bool $condition = true): bool
    {
        if($condition === true) {
            $this->errorCode = $errorCode;
            if($errorCode !== 0) {
                $this->errorBatch[$errorCode] = null;
            }
        }

        return $condition;
    }

    /**
     * Error Code Getter
     *
     * Will return an error code to determine cases that something might have gone wrong
     *
     * @access  public
     * @return  int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Error Code Batch Getter
     *
     * Will return an error code to determine cases that something might have gone wrong
     *
     * @access  public
     * @return  int
     */
    public function getErrorBatch(): array
    {
        return $this->errorBatch;
    }

    /**
     * Error Code Checker
     *
     * Returns true or false when an error has occured
     *
     * @access  public
     * @return  bool
     */
    public function hasErrors(): bool
    {
        return $this->errorBatch !== [];
    }
}
