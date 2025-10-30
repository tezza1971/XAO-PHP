<?php
namespace Xao;

/**
 * A text debugging utility.
 *
 * This class provides a way to display a portion of text with a specific line
 * highlighted, which is useful for showing the context of an error.
 */
class TextDebugger
{
    /**
     * The HTML output of the debugger.
     *
     * @var string
     */
    public string $strHtml = "";

    /**
     * TextDebugger constructor.
     *
     * @param string $strText The text to debug.
     * @param int $intErrLine The line number to highlight.
     * @param int $intPad The number of lines to show around the highlighted line.
     */
    public function __construct(string $strText, int $intErrLine, int $intPad = 4)
    {
        $arrLines = explode("\n", $strText);
        $intStart = max(0, $intErrLine - $intPad - 1);
        $intEnd = min(count($arrLines), $intErrLine + $intPad);

        $this->strHtml = "<pre style='border: 1px solid #ccc; padding: 10px; font-family: monospace;'>";
        for ($i = $intStart; $i < $intEnd; $i++) {
            $line = htmlspecialchars($arrLines[$i]);
            if ($i == $intErrLine - 1) {
                $this->strHtml .= "<div style='background-color: #fdd;'>" . ($i + 1) . ": " . $line . "</div>";
            } else {
                $this->strHtml .= "<div>" . ($i + 1) . ": " . $line . "</div>";
            }
        }
        $this->strHtml .= "</pre>";
    }

    /**
     * Returns the HTML output.
     *
     * @return string
     */
    public function strGetHtml(): string
    {
        return $this->strHtml;
    }
}
