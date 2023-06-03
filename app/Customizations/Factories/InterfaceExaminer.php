<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

interface InterfaceExaminer
{
    /**
     * Status Code Getter
     *
     * Return the status code response from a performed a HEAD request
     *
     * @access  public
     * @return  int
     */
    public function getStatusCode(): int;

    /**
     * Last Modified Getter
     *
     * Last time the source examined modified
     *
     * @access  public
     * @return  int
     */
    public function getLastModified(): string;

    /**
     * Content Type Getter
     *
     * Retrieve content type from the requested file
     */
    public function getContentType(): string;

    /**
     * Is Valid
     *
     * Expected only 200 status code responses, and/or depending on the extend of validation content type match
     * or other type of information.
     * For this example will be limited to status code
     *
     * @access  public
     * @return  bool
     */
    public function isValid(): bool;

    /**
     * Build Information
     *
     * Creates from stream handler the information required.
     *
     * @access  public
     * @param   string $source Url to get information from
     * @return  void
     */
    public function build(string $source): void;
}