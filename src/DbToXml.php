<?php
namespace Xao;

use mysqli;

/**
 * A class for converting database results to XML.
 *
 * This class provides a simple way to execute a SQL query and get the results
 * back as an XML document.
 */
class DbToXml extends DomDoc
{
    /**
     * The database connection.
     *
     * @var mysqli
     */
    public mysqli $objDb;

    /**
     * The SQL query to execute.
     *
     * @var string
     */
    public string $strSql;

    /**
     * The name of the root element for the XML document.
     *
     * @var string
     */
    public string $strRootEl;

    /**
     * The name of the element for each row in the result set.
     *
     * @var string
     */
    public string $strRowEl;

    /**
     * DbToXml constructor.
     *
     * @param string $strHost The database host.
     * @param string $strUser The database username.
     * @param string $strPass The database password.
     * @param string $strDb The database name.
     * @param string $strSql The SQL query to execute.
     * @param string $strRootEl The name of the root element.
     * @param string $strRowEl The name of the row element.
     */
    public function __construct(
        string $strHost,
        string $strUser,
        string $strPass,
        string $strDb,
        string $strSql,
        string $strRootEl = "data",
        string $strRowEl = "row"
    ) {
        parent::__construct("<{$strRootEl}/>");
        $this->objDb = new mysqli($strHost, $strUser, $strPass, $strDb);
        $this->strSql = $strSql;
        $this->strRootEl = $strRootEl;
        $this->strRowEl = $strRowEl;
    }

    /**
     * Fetches the data from the database and converts it to XML.
     */
    public function Fetch(): void
    {
        $result = $this->objDb->query($this->strSql);
        while ($row = $result->fetch_assoc()) {
            $ndRow = $this->ndAppendToRoot($this->strRowEl);
            foreach ($row as $key => $value) {
                $this->ndAppendToNode($ndRow, $key, $value);
            }
        }
    }
}
