<?php
namespace Xao;

/**
 * The base class for the XAO framework.
 *
 * This class provides the foundational properties and methods that all other
 * classes in the framework will use. It establishes a common ground for error
 * handling, namespace conventions, and other core functionalities.
 */
class XaoRoot
{
    /**
     * The XML namespace for XAO.
     *
     * This is used to distinguish XAO-generated XML data from user data.
     */
    public string $idXaoNamespace = "http://github.com/tezza1971/XAO-PHP/schema/xao_1-0.xsd";

    /**
     * The prefix for the XAO XML namespace.
     *
     * This can be customized by child classes if needed, but it should be set
     * before any XML documents are created.
     */
    public string $strXaoNamespacePrefix = "xao";

    /**
     * Stores debugging information.
     *
     * This property is available for child classes to log and manage debugging
     * data.
     */
    public ?string $strDebugData = null;

    /**
     * Caching parameters.
     *
     * This array holds the configuration for caching, such as the cache key,
     * TTL (Time To Live), and expiration timestamp. It's designed to be used by
     * a dedicated caching class.
     */
    public array $arrCacheParams = [];

    /**
     * A generic error handler.
     *
     * This method provides a consistent way to handle errors throughout the
     * framework. It can be extended by child classes to provide more specific
     * error handling, such as logging or custom error pages.
     *
     * @param string $strErrMsg The main error message.
     * @param array|null $arrAttribs Additional context for the error.
     * @throws \Exception Always throws an exception with the error message.
     */
    public function Throw(string $strErrMsg, ?array $arrAttribs = null): void
    {
        $errorMessage = $strErrMsg;

        if ($arrAttribs && isset($arrAttribs["class"], $arrAttribs["function"], $arrAttribs["line"])) {
            $errorMessage = sprintf(
                "In method %s::%s() on line %d\n\n%s",
                $arrAttribs["class"],
                $arrAttribs["function"],
                $arrAttribs["line"],
                $strErrMsg
            );
        }

        throw new \Exception($errorMessage);
    }

    /**
     * Gathers context for error reporting.
     *
     * This is a helper function to collect file, function, and line number
     * information, which can be passed to the Throw method for more detailed
     * error messages.
     *
     * @param string $fcnCurrent The name of the function where the error occurred.
     * @param int $intLine The line number where the error occurred.
     * @return array An array with the class, function, and line number.
     */
    public function arrSetErrFnc(string $fcnCurrent, int $intLine): array
    {
        return [
            "class" => get_class($this),
            "function" => $fcnCurrent,
            "line" => $intLine,
        ];
    }

    /**
     * Validates a string to ensure it's a safe name.
     *
     * A safe name in this context is a string that doesn't contain newlines,
     * doesn't start with a digit, and only contains word characters
     * (letters, numbers, and underscores).
     *
     * @param string $strSubject The string to validate.
     * @return bool True if the string is a safe name, false otherwise.
     */
    public function blnTestSafeName(string $strSubject): bool
    {
        // A multi-line string is not safe.
        if (str_contains($strSubject, "\n")) {
            return false;
        }

        // A string that starts with a digit is not safe.
        if (preg_match("/^\d/", $strSubject)) {
            return false;
        }

        // A string with non-word characters (excluding underscores) is not safe.
        if (preg_match("/[^\w]/", $strSubject)) {
            return false;
        }

        return true;
    }
}
