<?php
namespace Xao;

use mysqli;

/**
 * A class for fetching database results and converting them to XML.
 *
 * This class extends DbToXml to provide a more convenient way to fetch data.
 */
class DbToXmlResultFetcher extends DbToXml
{
    /**
     * DbToXmlResultFetcher constructor.
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
        parent::__construct($strHost, $strUser, $strPass, $strDb, $strSql, $strRootEl, $strRowEl);
        $this->Fetch();
    }
}
